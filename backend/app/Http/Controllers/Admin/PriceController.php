<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PriceType;
use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\Market;
use App\Models\MarketPrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Market price management. Two ways to enter data:
 *  1. Daily entry grid – pick market + date, type min/max for every commodity
 *     (pre-filled with the last known price so only changes need typing).
 *  2. CSV import – for bulk data, e.g. digitised DAM daily reports.
 */
class PriceController extends Controller
{
    public function index(Request $request): View
    {
        $prices = MarketPrice::query()
            ->with(['commodity', 'market', 'enteredBy'])
            ->when($request->filled('market_id'), fn ($q) => $q->where('market_id', $request->integer('market_id')))
            ->when($request->filled('commodity_id'), fn ($q) => $q->where('commodity_id', $request->integer('commodity_id')))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('price_date', $request->date('date')))
            ->orderByDesc('price_date')
            ->orderBy('market_id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.prices.index', [
            'prices' => $prices,
            'markets' => Market::orderBy('name_en')->get(),
            'commodities' => Commodity::orderBy('sort_order')->get(),
        ]);
    }

    public function entry(Request $request): View
    {
        $markets = Market::where('is_active', true)->with('district')->orderBy('name_en')->get();
        $marketId = $request->integer('market_id') ?: $markets->first()?->id;
        $date = $request->date('date') ?? today();
        $type = $request->input('type', PriceType::Wholesale->value);

        $commodities = Commodity::where('is_active', true)->orderBy('sort_order')->get();

        $existing = MarketPrice::where('market_id', $marketId)
            ->whereDate('price_date', $date)
            ->where('price_type', $type)
            ->get()
            ->keyBy('commodity_id');

        // Last known price per commodity before this date – shown as placeholder hints.
        $previous = MarketPrice::where('market_id', $marketId)
            ->where('price_type', $type)
            ->whereDate('price_date', '<', $date)
            ->whereDate('price_date', '>=', $date->copy()->subDays(30))
            ->orderByDesc('price_date')
            ->get()
            ->unique('commodity_id')
            ->keyBy('commodity_id');

        return view('admin.prices.entry', compact('markets', 'marketId', 'date', 'type', 'commodities', 'existing', 'previous'));
    }

    public function storeEntry(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'market_id' => ['required', 'integer', 'exists:markets,id'],
            'price_date' => ['required', 'date', 'before_or_equal:tomorrow'],
            'price_type' => ['required', Rule::enum(PriceType::class)],
            'source' => ['nullable', 'string', 'max:120'],
            'prices' => ['required', 'array'],
            'prices.*.min' => ['nullable', 'numeric', 'between:0,1000000'],
            'prices.*.max' => ['nullable', 'numeric', 'between:0,1000000'],
        ]);

        $saved = 0;
        $errors = [];
        $validIds = Commodity::pluck('id')->all();

        DB::transaction(function () use ($data, $request, $validIds, &$saved, &$errors) {
            foreach ($data['prices'] as $commodityId => $row) {
                $min = $row['min'] ?? null;
                $max = $row['max'] ?? null;
                if (($min === null || $min === '') && ($max === null || $max === '')) {
                    continue; // left blank → not traded / not collected today
                }
                if (! in_array((int) $commodityId, $validIds, true)) {
                    continue;
                }
                // One value is enough: use it for both min and max.
                $min = (float) ($min ?? $max);
                $max = (float) ($max ?? $min);
                if ($max < $min) {
                    $errors[] = "Commodity #{$commodityId}: max price is lower than min price.";

                    continue;
                }

                MarketPrice::updateOrCreate(
                    [
                        'commodity_id' => (int) $commodityId,
                        'market_id' => $data['market_id'],
                        'price_date' => Carbon::parse($data['price_date'])->toDateString(),
                        'price_type' => $data['price_type'],
                    ],
                    [
                        'min_price' => $min,
                        'max_price' => $max,
                        'source' => $data['source'] ?? null,
                        'entered_by' => $request->user()->id,
                    ],
                );
                $saved++;
            }
        });

        $redirect = redirect()->route('admin.prices.entry', [
            'market_id' => $data['market_id'],
            'date' => Carbon::parse($data['price_date'])->toDateString(),
            'type' => $data['price_type'],
        ])->with('status', "{$saved} prices saved.");

        return $errors ? $redirect->withErrors($errors) : $redirect;
    }

    public function importForm(): View
    {
        return view('admin.prices.import', ['markets' => Market::orderBy('name_en')->get()]);
    }

    /**
     * CSV columns: date, market, commodity, type, min_price, max_price, source
     * "market" and "commodity" may be the numeric ID or the exact English name.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $markets = Market::all();
        $commodities = Commodity::all();
        $findMarket = fn (string $v) => is_numeric($v)
            ? $markets->firstWhere('id', (int) $v)
            : $markets->first(fn ($m) => strcasecmp($m->name_en, $v) === 0);
        $findCommodity = fn (string $v) => is_numeric($v)
            ? $commodities->firstWhere('id', (int) $v)
            : $commodities->first(fn ($c) => strcasecmp($c->name_en, $v) === 0);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = array_map(fn ($h) => strtolower(trim((string) $h, " \t\n\r\0\x0B\xEF\xBB\xBF")), fgetcsv($handle) ?: []);
        $required = ['date', 'market', 'commodity', 'min_price', 'max_price'];
        if (array_diff($required, $header)) {
            fclose($handle);

            return back()->withErrors(['file' => 'CSV header must contain: '.implode(', ', $required).' (optional: type, source).']);
        }

        $saved = 0;
        $errors = [];
        $line = 1;

        DB::beginTransaction();
        while (($cells = fgetcsv($handle)) !== false) {
            $line++;
            if (count(array_filter($cells, fn ($c) => trim((string) $c) !== '')) === 0) {
                continue;
            }
            $row = array_combine($header, array_pad(array_slice($cells, 0, count($header)), count($header), ''));

            $market = $findMarket(trim($row['market']));
            $commodity = $findCommodity(trim($row['commodity']));
            $type = strtolower(trim($row['type'] ?? '')) ?: PriceType::Wholesale->value;
            $min = is_numeric($row['min_price']) ? (float) $row['min_price'] : null;
            $max = is_numeric($row['max_price']) ? (float) $row['max_price'] : $min;

            try {
                $date = Carbon::parse(trim($row['date']))->toDateString();
            } catch (\Throwable) {
                $date = null;
            }

            $problem = match (true) {
                ! $date => 'invalid date',
                ! $market => 'unknown market "'.$row['market'].'"',
                ! $commodity => 'unknown commodity "'.$row['commodity'].'"',
                PriceType::tryFrom($type) === null => 'type must be wholesale or retail',
                $min === null || $min < 0 => 'invalid min_price',
                $max < $min => 'max_price lower than min_price',
                default => null,
            };

            if ($problem) {
                $errors[] = "Line {$line}: {$problem}";

                continue;
            }

            MarketPrice::updateOrCreate(
                ['commodity_id' => $commodity->id, 'market_id' => $market->id, 'price_date' => $date, 'price_type' => $type],
                ['min_price' => $min, 'max_price' => $max, 'source' => trim($row['source'] ?? '') ?: 'CSV import', 'entered_by' => $request->user()->id],
            );
            $saved++;
        }
        fclose($handle);
        DB::commit();

        $redirect = redirect()->route('admin.prices.import')->with('status', "{$saved} rows imported.");

        return $errors ? $redirect->withErrors(array_slice($errors, 0, 50)) : $redirect;
    }

    /** A ready-to-fill CSV with one row per commodity for the chosen market. */
    public function template(Request $request): StreamedResponse
    {
        $market = Market::find($request->integer('market_id')) ?? Market::orderBy('name_en')->first();
        $commodities = Commodity::where('is_active', true)->orderBy('sort_order')->get();
        $date = today()->toDateString();

        return response()->streamDownload(function () use ($market, $commodities, $date) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['date', 'market', 'commodity', 'type', 'min_price', 'max_price', 'source']);
            foreach ($commodities as $c) {
                fputcsv($out, [$date, $market?->name_en, $c->name_en, 'wholesale', '', '', '']);
            }
            fclose($out);
        }, 'price-template-'.$date.'.csv', ['Content-Type' => 'text/csv']);
    }

    public function destroy(MarketPrice $price): RedirectResponse
    {
        $price->delete();

        return back()->with('status', 'Price entry deleted.');
    }
}

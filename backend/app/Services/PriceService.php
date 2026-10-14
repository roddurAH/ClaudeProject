<?php

namespace App\Services;

use App\Models\District;
use App\Models\Market;
use App\Models\MarketPrice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Read-side logic for market prices: latest board per market with day-to-day
 * trend, price history for charts, and nearest-market lookup.
 */
class PriceService
{
    /** How far back to look for a "previous" price when computing the trend. */
    private const TREND_LOOKBACK_DAYS = 14;

    /** Prices within ±2% are shown as "stable". */
    private const STABLE_THRESHOLD_PCT = 2.0;

    /**
     * Market closest to the district that actually has price data.
     */
    public function nearestMarket(?District $district): ?Market
    {
        $markets = Market::query()
            ->where('is_active', true)
            ->whereHas('prices')
            ->with('district')
            ->get();

        if ($markets->isEmpty()) {
            return null;
        }

        if (! $district) {
            return $markets->first();
        }

        return $markets->sortBy(fn (Market $m) => $district->distanceTo($m->district))->first();
    }

    /**
     * Latest price for each commodity in a market, with trend vs the previous entry.
     *
     * @return array{date:?string, items:Collection}
     */
    public function board(Market $market, string $type): array
    {
        $latestDate = MarketPrice::query()
            ->where('market_id', $market->id)
            ->where('price_type', $type)
            ->max('price_date');

        if (! $latestDate) {
            return ['date' => null, 'items' => collect()];
        }

        $latestDate = Carbon::parse($latestDate);

        $rows = MarketPrice::query()
            ->with('commodity')
            ->where('market_id', $market->id)
            ->where('price_type', $type)
            ->whereBetween('price_date', [$latestDate->copy()->subDays(self::TREND_LOOKBACK_DAYS), $latestDate])
            ->whereHas('commodity', fn ($q) => $q->where('is_active', true))
            ->orderByDesc('price_date')
            ->get()
            ->groupBy('commodity_id');

        $items = $rows->map(function (Collection $history) {
            /** @var MarketPrice $latest */
            $latest = $history->first();
            /** @var MarketPrice|null $previous */
            $previous = $history->skip(1)->first();

            $avg = $latest->average();
            $prevAvg = $previous?->average();
            $changePct = ($prevAvg && $prevAvg > 0) ? round(($avg - $prevAvg) / $prevAvg * 100, 1) : null;

            return [
                'commodity' => $latest->commodity,
                'date' => $latest->price_date->toDateString(),
                'min' => $latest->min_price,
                'max' => $latest->max_price,
                'avg' => $avg,
                'previous_avg' => $prevAvg,
                'change_pct' => $changePct,
                'trend' => $this->trend($changePct),
                'source' => $latest->source,
            ];
        })
            ->sortBy(fn ($item) => [$item['commodity']->sort_order, $item['commodity']->id])
            ->values();

        return ['date' => $latestDate->toDateString(), 'items' => $items];
    }

    /**
     * @return Collection<int, array{date:string,min:float,max:float,avg:float}>
     */
    public function history(int $commodityId, int $marketId, string $type, int $days): Collection
    {
        return MarketPrice::query()
            ->where('commodity_id', $commodityId)
            ->where('market_id', $marketId)
            ->where('price_type', $type)
            ->where('price_date', '>=', now()->subDays($days)->toDateString())
            ->orderBy('price_date')
            ->get()
            ->map(fn (MarketPrice $p) => [
                'date' => $p->price_date->toDateString(),
                'min' => $p->min_price,
                'max' => $p->max_price,
                'avg' => $p->average(),
            ]);
    }

    private function trend(?float $changePct): string
    {
        if ($changePct === null || abs($changePct) < self::STABLE_THRESHOLD_PCT) {
            return 'stable';
        }

        return $changePct > 0 ? 'up' : 'down';
    }
}

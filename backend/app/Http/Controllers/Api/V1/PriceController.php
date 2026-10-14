<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PriceType;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommodityResource;
use App\Http\Resources\FertilizerResource;
use App\Http\Resources\MarketResource;
use App\Models\District;
use App\Models\Fertilizer;
use App\Models\Market;
use App\Services\PriceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PriceController extends Controller
{
    public function __construct(private readonly PriceService $prices) {}

    /**
     * Price board for one market. Pass market_id, or district_id to get the
     * nearest market that has data.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'market_id' => ['nullable', 'integer', 'exists:markets,id'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'type' => ['nullable', Rule::enum(PriceType::class)],
        ]);

        $type = $data['type'] ?? PriceType::Wholesale->value;

        $market = isset($data['market_id'])
            ? Market::with('district')->find($data['market_id'])
            : $this->prices->nearestMarket(isset($data['district_id']) ? District::find($data['district_id']) : null);

        if (! $market) {
            return response()->json(['market' => null, 'date' => null, 'type' => $type, 'items' => []]);
        }

        $market->loadMissing('district');
        $board = $this->prices->board($market, $type);

        return response()->json([
            'market' => (new MarketResource($market))->resolve($request),
            'date' => $board['date'],
            'type' => $type,
            'items' => $board['items']->map(fn (array $item) => [
                ...$item,
                'commodity' => (new CommodityResource($item['commodity']))->resolve($request),
            ]),
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $data = $request->validate([
            'commodity_id' => ['required', 'integer', 'exists:commodities,id'],
            'market_id' => ['required', 'integer', 'exists:markets,id'],
            'type' => ['nullable', Rule::enum(PriceType::class)],
            'days' => ['nullable', 'integer', 'min:7', 'max:365'],
        ]);

        $points = $this->prices->history(
            (int) $data['commodity_id'],
            (int) $data['market_id'],
            $data['type'] ?? PriceType::Wholesale->value,
            (int) ($data['days'] ?? 30),
        );

        return response()->json(['data' => $points]);
    }

    /** Government farmer-level fertilizer prices – helps farmers spot overcharging. */
    public function fertilizers(Request $request): JsonResponse
    {
        return response()->json([
            'data' => FertilizerResource::collection(Fertilizer::orderBy('sort_order')->get())->resolve($request),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommodityResource;
use App\Http\Resources\MarketResource;
use App\Models\Commodity;
use App\Models\Market;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MarketController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return MarketResource::collection(
            Market::with('district')->where('is_active', true)->orderBy('name_en')->get()
        );
    }

    public function commodities(): AnonymousResourceCollection
    {
        return CommodityResource::collection(
            Commodity::where('is_active', true)->orderBy('sort_order')->get()
        );
    }
}

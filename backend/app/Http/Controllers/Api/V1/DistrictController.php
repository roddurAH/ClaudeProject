<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DistrictResource;
use App\Models\District;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DistrictController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $districts = District::with('division')->orderBy('name_en')->get();

        return DistrictResource::collection($districts);
    }
}

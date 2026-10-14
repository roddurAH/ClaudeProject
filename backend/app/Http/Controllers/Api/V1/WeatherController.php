<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\WeatherUnavailableException;
use App\Http\Controllers\Controller;
use App\Models\District;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function __invoke(Request $request, WeatherService $weather): JsonResponse
    {
        $data = $request->validate([
            'district_id' => ['required', 'integer', 'exists:districts,id'],
        ]);

        try {
            return response()->json($weather->forecastFor(District::findOrFail($data['district_id'])));
        } catch (WeatherUnavailableException) {
            return response()->json([
                'message' => __('Weather service is temporarily unavailable. Please try again later.'),
            ], 503);
        }
    }
}

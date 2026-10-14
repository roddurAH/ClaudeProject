<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertResource;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlertController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate([
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
        ]);

        $alerts = Alert::active()
            ->forDistrict(isset($data['district_id']) ? (int) $data['district_id'] : null)
            ->orderByRaw("CASE severity WHEN 'danger' THEN 0 WHEN 'warning' THEN 1 ELSE 2 END")
            ->latest('starts_at')
            ->limit(20)
            ->get();

        return AlertResource::collection($alerts);
    }
}

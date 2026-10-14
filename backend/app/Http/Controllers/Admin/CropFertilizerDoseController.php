<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\CropFertilizerDose;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Recommended fertilizer per hectare for a crop – feeds the app's fertilizer calculator. */
class CropFertilizerDoseController extends Controller
{
    public function store(Request $request, Crop $crop): RedirectResponse
    {
        $data = $request->validate([
            'fertilizer_id' => ['required', 'integer', 'exists:fertilizers,id'],
            'kg_per_hectare' => ['required', 'numeric', 'between:0,5000'],
            'application_bn' => ['nullable', 'string', 'max:2000'],
            'application_en' => ['nullable', 'string', 'max:2000'],
        ]);

        $crop->fertilizerDoses()->updateOrCreate(
            ['fertilizer_id' => $data['fertilizer_id']],
            $data,
        );

        return redirect(route('admin.crops.edit', $crop).'#doses')->with('status', 'Fertilizer dose saved.');
    }

    public function destroy(CropFertilizerDose $dose): RedirectResponse
    {
        $cropId = $dose->crop_id;
        $dose->delete();

        return redirect(route('admin.crops.edit', $cropId).'#doses')->with('status', 'Fertilizer dose removed.');
    }
}

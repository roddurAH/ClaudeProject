<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Options;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CropStageRequest;
use App\Models\Crop;
use App\Models\CropStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Crop calendar steps, managed from the crop edit page. */
class CropStageController extends Controller
{
    public function store(CropStageRequest $request, Crop $crop): RedirectResponse
    {
        $crop->stages()->create($request->validated());

        return redirect(route('admin.crops.edit', $crop).'#stages')->with('status', 'Stage added.');
    }

    public function edit(CropStage $stage): View
    {
        return view('admin.crops.stage-form', ['stage' => $stage, 'stageTypes' => Options::STAGE_TYPES]);
    }

    public function update(CropStageRequest $request, CropStage $stage): RedirectResponse
    {
        $stage->update($request->validated());

        return redirect(route('admin.crops.edit', $stage->crop_id).'#stages')->with('status', 'Stage updated.');
    }

    public function destroy(CropStage $stage): RedirectResponse
    {
        $cropId = $stage->crop_id;
        $stage->delete();

        return redirect(route('admin.crops.edit', $cropId).'#stages')->with('status', 'Stage removed.');
    }
}

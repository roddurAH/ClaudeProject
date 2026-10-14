<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CropRequest;
use App\Models\Crop;
use App\Models\Fertilizer;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CropController extends Controller
{
    public function __construct(private readonly ImageService $images) {}

    public function index(Request $request): View
    {
        $crops = Crop::query()
            ->withCount(['problems', 'stages', 'fertilizerDoses'])
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name_en', 'like', '%'.$request->string('q').'%')
                ->orWhere('name_bn', 'like', '%'.$request->string('q').'%')))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.crops.index', compact('crops'));
    }

    public function create(): View
    {
        return view('admin.crops.form', ['crop' => new Crop(['is_published' => true])]);
    }

    public function store(CropRequest $request): RedirectResponse
    {
        $crop = new Crop($request->safe()->except('image'));
        if ($request->hasFile('image')) {
            $crop->image_path = $this->images->store($request->file('image'), 'crops');
        }
        $crop->save();

        return redirect()->route('admin.crops.edit', $crop)
            ->with('status', 'Crop created. Now add its calendar stages and fertilizer doses below.');
    }

    public function edit(Crop $crop): View
    {
        $crop->load(['stages', 'fertilizerDoses.fertilizer', 'problems']);
        $fertilizers = Fertilizer::orderBy('sort_order')->get();

        return view('admin.crops.form', compact('crop', 'fertilizers'));
    }

    public function update(CropRequest $request, Crop $crop): RedirectResponse
    {
        $crop->fill($request->safe()->except('image'));
        if (! $request->has('sowing_months')) {
            $crop->sowing_months = [];
        }
        if (! $request->has('harvest_months')) {
            $crop->harvest_months = [];
        }
        if ($request->hasFile('image')) {
            $this->images->delete($crop->image_path);
            $crop->image_path = $this->images->store($request->file('image'), 'crops');
        }
        $crop->save();

        return back()->with('status', 'Crop saved.');
    }

    public function destroy(Crop $crop): RedirectResponse
    {
        $this->images->delete($crop->image_path);
        $crop->problems->each(fn ($p) => $this->images->delete($p->image_path));
        $crop->delete();

        return redirect()->route('admin.crops.index')->with('status', 'Crop deleted.');
    }
}

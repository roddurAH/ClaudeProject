<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProblemRequest;
use App\Models\Crop;
use App\Models\Problem;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProblemController extends Controller
{
    public function __construct(private readonly ImageService $images) {}

    public function index(Request $request): View
    {
        $problems = Problem::query()
            ->with('crop')
            ->when($request->filled('crop_id'), fn ($q) => $q->where('crop_id', $request->integer('crop_id')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name_en', 'like', '%'.$request->string('q').'%')
                ->orWhere('name_bn', 'like', '%'.$request->string('q').'%')))
            ->orderBy('crop_id')
            ->orderBy('affected_part')
            ->paginate(30)
            ->withQueryString();

        $crops = Crop::orderBy('sort_order')->get(['id', 'name_en', 'name_bn']);

        return view('admin.problems.index', compact('problems', 'crops'));
    }

    public function create(Request $request): View
    {
        return view('admin.problems.form', [
            'problem' => new Problem([
                'crop_id' => $request->integer('crop_id') ?: null,
                'severity' => 'medium',
                'is_published' => true,
            ]),
            'crops' => Crop::orderBy('sort_order')->get(['id', 'name_en', 'name_bn']),
        ]);
    }

    public function store(ProblemRequest $request): RedirectResponse
    {
        $problem = new Problem($request->safe()->except('image'));
        if ($request->hasFile('image')) {
            $problem->image_path = $this->images->store($request->file('image'), 'problems');
        }
        $problem->save();

        return redirect()->route('admin.problems.index', ['crop_id' => $problem->crop_id])->with('status', 'Problem added.');
    }

    public function edit(Problem $problem): View
    {
        return view('admin.problems.form', [
            'problem' => $problem,
            'crops' => Crop::orderBy('sort_order')->get(['id', 'name_en', 'name_bn']),
        ]);
    }

    public function update(ProblemRequest $request, Problem $problem): RedirectResponse
    {
        $problem->fill($request->safe()->except('image'));
        if ($request->hasFile('image')) {
            $this->images->delete($problem->image_path);
            $problem->image_path = $this->images->store($request->file('image'), 'problems');
        }
        $problem->save();

        return redirect()->route('admin.problems.index', ['crop_id' => $problem->crop_id])->with('status', 'Problem saved.');
    }

    public function destroy(Problem $problem): RedirectResponse
    {
        $this->images->delete($problem->image_path);
        $problem->delete();

        return back()->with('status', 'Problem deleted.');
    }
}

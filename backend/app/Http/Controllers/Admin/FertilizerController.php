<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FertilizerRequest;
use App\Models\Fertilizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Government farmer-level fertilizer prices (shown in the app & calculator). */
class FertilizerController extends Controller
{
    public function index(): View
    {
        return view('admin.fertilizers.index', ['fertilizers' => Fertilizer::orderBy('sort_order')->get()]);
    }

    public function edit(Fertilizer $fertilizer): View
    {
        return view('admin.fertilizers.form', compact('fertilizer'));
    }

    public function update(FertilizerRequest $request, Fertilizer $fertilizer): RedirectResponse
    {
        $fertilizer->update($request->validated());

        return redirect()->route('admin.fertilizers.index')->with('status', 'Fertilizer saved.');
    }
}

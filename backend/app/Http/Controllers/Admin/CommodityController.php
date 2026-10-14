<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CommodityRequest;
use App\Models\Commodity;
use App\Models\Crop;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CommodityController extends Controller
{
    public function index(): View
    {
        return view('admin.commodities.index', [
            'commodities' => Commodity::with('crop')->orderBy('sort_order')->paginate(50),
        ]);
    }

    public function create(): View
    {
        return view('admin.commodities.form', [
            'commodity' => new Commodity(['unit' => 'kg', 'is_active' => true]),
            'crops' => Crop::orderBy('sort_order')->get(['id', 'name_en']),
        ]);
    }

    public function store(CommodityRequest $request): RedirectResponse
    {
        Commodity::create($request->validated());

        return redirect()->route('admin.commodities.index')->with('status', 'Commodity created.');
    }

    public function edit(Commodity $commodity): View
    {
        return view('admin.commodities.form', [
            'commodity' => $commodity,
            'crops' => Crop::orderBy('sort_order')->get(['id', 'name_en']),
        ]);
    }

    public function update(CommodityRequest $request, Commodity $commodity): RedirectResponse
    {
        $commodity->update($request->validated());

        return redirect()->route('admin.commodities.index')->with('status', 'Commodity saved.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MarketRequest;
use App\Models\District;
use App\Models\Market;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MarketController extends Controller
{
    public function index(): View
    {
        return view('admin.markets.index', [
            'markets' => Market::with('district')->withCount('prices')->orderBy('name_en')->paginate(50),
        ]);
    }

    public function create(): View
    {
        return view('admin.markets.form', [
            'market' => new Market(['type' => 'wholesale', 'is_active' => true]),
            'districts' => District::orderBy('name_en')->get(),
        ]);
    }

    public function store(MarketRequest $request): RedirectResponse
    {
        Market::create($request->validated());

        return redirect()->route('admin.markets.index')->with('status', 'Market created.');
    }

    public function edit(Market $market): View
    {
        return view('admin.markets.form', [
            'market' => $market,
            'districts' => District::orderBy('name_en')->get(),
        ]);
    }

    public function update(MarketRequest $request, Market $market): RedirectResponse
    {
        $market->update($request->validated());

        return redirect()->route('admin.markets.index')->with('status', 'Market saved.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AlertRequest;
use App\Models\Alert;
use App\Models\Division;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Broadcast warnings (flood, cyclone, pest outbreak …) to all or selected districts. */
class AlertController extends Controller
{
    public function index(): View
    {
        return view('admin.alerts.index', [
            'alerts' => Alert::with('districts')->latest('starts_at')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.alerts.form', [
            'alert' => new Alert(['severity' => 'warning', 'is_national' => true, 'starts_at' => now()]),
            'divisions' => Division::with(['districts' => fn ($q) => $q->orderBy('name_en')])->orderBy('name_en')->get(),
            'selected' => [],
        ]);
    }

    public function store(AlertRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $alert = new Alert(collect($data)->except('district_ids')->all());
        $alert->created_by = $request->user()->id;
        $alert->save();
        $alert->districts()->sync($alert->is_national ? [] : ($data['district_ids'] ?? []));

        return redirect()->route('admin.alerts.index')->with('status', 'Alert published.');
    }

    public function edit(Alert $alert): View
    {
        return view('admin.alerts.form', [
            'alert' => $alert,
            'divisions' => Division::with(['districts' => fn ($q) => $q->orderBy('name_en')])->orderBy('name_en')->get(),
            'selected' => $alert->districts()->pluck('districts.id')->all(),
        ]);
    }

    public function update(AlertRequest $request, Alert $alert): RedirectResponse
    {
        $data = $request->validated();
        $alert->update(collect($data)->except('district_ids')->all());
        $alert->districts()->sync($alert->is_national ? [] : ($data['district_ids'] ?? []));

        return redirect()->route('admin.alerts.index')->with('status', 'Alert saved.');
    }

    public function destroy(Alert $alert): RedirectResponse
    {
        $alert->delete();

        return back()->with('status', 'Alert deleted.');
    }
}

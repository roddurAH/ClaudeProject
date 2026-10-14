@extends('admin.layouts.app')
@section('title', 'Import prices from CSV')

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.prices.import.store') }}" enctype="multipart/form-data">
                    @csrf
                    <x-form.input name="file" type="file" label="CSV file" accept=".csv,text/csv" required />
                    <button class="btn btn-brand"><i class="bi bi-upload"></i> Import</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h6>File format</h6>
                <pre class="bg-light p-2 small mb-2">date,market,commodity,type,min_price,max_price,source
2026-10-14,Kawran Bazar,Potato,wholesale,20,24,DAM daily report</pre>
                <ul class="small">
                    <li><code>market</code> and <code>commodity</code>: numeric ID or exact English name.</li>
                    <li><code>type</code>: <code>wholesale</code> (default) or <code>retail</code>.</li>
                    <li>An existing entry for the same date, market, commodity and type is updated.</li>
                    <li>Rows with errors are skipped and listed after import.</li>
                </ul>
                <form method="GET" action="{{ route('admin.prices.template') }}" class="d-flex gap-2">
                    <select name="market_id" class="form-select form-select-sm">
                        @foreach($markets as $m)<option value="{{ $m->id }}">{{ $m->name_en }}</option>@endforeach
                    </select>
                    <button class="btn btn-sm btn-outline-secondary text-nowrap"><i class="bi bi-download"></i> Download template</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

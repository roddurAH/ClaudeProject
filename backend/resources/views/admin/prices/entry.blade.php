@extends('admin.layouts.app')
@section('title', 'Enter daily prices')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Market</label>
                <select name="market_id" class="form-select">
                    @foreach($markets as $m)
                        <option value="{{ $m->id }}" @selected($m->id == $marketId)>{{ $m->name_en }} ({{ $m->district->name_en }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="{{ $date->toDateString() }}" max="{{ now()->addDay()->toDateString() }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Price type</label>
                <select name="type" class="form-select">
                    <option value="wholesale" @selected($type === 'wholesale')>Wholesale (পাইকারি)</option>
                    <option value="retail" @selected($type === 'retail')>Retail (খুচরা)</option>
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i> Load</button></div>
        </form>
    </div>
</div>

@if($markets->isEmpty())
    <div class="alert alert-warning">Create a market first.</div>
@else
<form method="POST" action="{{ route('admin.prices.entry.store') }}">
    @csrf
    <input type="hidden" name="market_id" value="{{ $marketId }}">
    <input type="hidden" name="price_date" value="{{ $date->toDateString() }}">
    <input type="hidden" name="price_type" value="{{ $type }}">

    <div class="card">
        <div class="card-header bg-white small text-muted">
            Prices in Taka per unit. Leave a row empty if the item was not traded. Grey numbers are the last known price.
            <button type="button" class="btn btn-sm btn-link" id="copyPrev">Copy all last known prices</button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light"><tr><th>Commodity</th><th>Unit</th><th style="width: 160px">Min (৳)</th><th style="width: 160px">Max (৳)</th><th>Last known</th></tr></thead>
                <tbody>
                @foreach($commodities as $c)
                    @php($cur = $existing->get($c->id))
                    @php($prev = $previous->get($c->id))
                    <tr>
                        <td>{{ $c->icon }} <span lang="bn">{{ $c->name_bn }}</span> <small class="text-muted">{{ $c->name_en }}</small></td>
                        <td class="small">{{ $c->unit }}</td>
                        <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="prices[{{ $c->id }}][min]"
                                   value="{{ old("prices.{$c->id}.min", $cur?->min_price) }}" placeholder="{{ $prev?->min_price }}" data-prev="{{ $prev?->min_price }}"></td>
                        <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="prices[{{ $c->id }}][max]"
                                   value="{{ old("prices.{$c->id}.max", $cur?->max_price) }}" placeholder="{{ $prev?->max_price }}" data-prev="{{ $prev?->max_price }}"></td>
                        <td class="small text-muted">
                            @if($prev) {{ $prev->min_price }}–{{ $prev->max_price }} <span class="text-nowrap">({{ $prev->price_date->format('d M') }})</span> @else — @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex gap-2 align-items-end flex-wrap">
            <div class="flex-grow-1">
                <label class="form-label small mb-1">Source</label>
                <input name="source" class="form-control form-control-sm" value="{{ old('source', 'Field survey') }}" placeholder="e.g. DAM daily report, field survey">
            </div>
            <button class="btn btn-brand"><i class="bi bi-save"></i> Save prices</button>
        </div>
    </div>
</form>
@endif
@endsection

@push('scripts')
<script>
document.getElementById('copyPrev')?.addEventListener('click', () => {
    document.querySelectorAll('input[data-prev]').forEach(el => {
        if (!el.value && el.dataset.prev) el.value = el.dataset.prev;
    });
});
</script>
@endpush

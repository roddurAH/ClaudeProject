@extends('admin.layouts.app')
@section('title', 'Price records')

@section('content')
<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
    <form class="d-flex gap-2 flex-wrap" method="GET">
        <select name="market_id" class="form-select">
            <option value="">All markets</option>
            @foreach($markets as $m)
                <option value="{{ $m->id }}" @selected(request('market_id') == $m->id)>{{ $m->name_en }}</option>
            @endforeach
        </select>
        <select name="commodity_id" class="form-select">
            <option value="">All commodities</option>
            @foreach($commodities as $c)
                <option value="{{ $c->id }}" @selected(request('commodity_id') == $c->id)>{{ $c->name_en }}</option>
            @endforeach
        </select>
        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
        <button class="btn btn-outline-secondary"><i class="bi bi-funnel"></i></button>
    </form>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.prices.import') }}" class="btn btn-outline-success"><i class="bi bi-upload"></i> Import CSV</a>
        <a href="{{ route('admin.prices.entry') }}" class="btn btn-brand"><i class="bi bi-pencil-square"></i> Enter prices</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light"><tr><th>Date</th><th>Market</th><th>Commodity</th><th>Type</th><th class="text-end">Min ৳</th><th class="text-end">Max ৳</th><th>Source</th><th>By</th><th></th></tr></thead>
            <tbody>
            @forelse($prices as $p)
                <tr>
                    <td class="text-nowrap">{{ $p->price_date->format('d M Y') }}</td>
                    <td>{{ $p->market->name_en }}</td>
                    <td>{{ $p->commodity->icon }} {{ $p->commodity->name_en }} <small class="text-muted">/{{ $p->commodity->unit }}</small></td>
                    <td>{{ ucfirst($p->price_type) }}</td>
                    <td class="text-end">{{ number_format($p->min_price, 2) }}</td>
                    <td class="text-end">{{ number_format($p->max_price, 2) }}</td>
                    <td class="small">{{ $p->source }}</td>
                    <td class="small">{{ $p->enteredBy?->name ?? '—' }}</td>
                    <td class="text-end"><x-delete-button :action="route('admin.prices.destroy', $p)" label="" /></td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No price records.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $prices->links() }}</div>
@endsection

@extends('admin.layouts.app')
@section('title', 'Markets')

@section('content')
<div class="text-end mb-3"><a href="{{ route('admin.markets.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New market</a></div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Market</th><th>District</th><th>Type</th><th class="text-center">Price records</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach($markets as $m)
                <tr>
                    <td><strong lang="bn">{{ $m->name_bn }}</strong> <span class="text-muted">{{ $m->name_en }}</span></td>
                    <td>{{ $m->district->name_en }}</td>
                    <td>{{ \App\Enums\Options::MARKET_TYPES[$m->type] ?? $m->type }}</td>
                    <td class="text-center">{{ $m->prices_count }}</td>
                    <td>{!! $m->is_active ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Inactive</span>' !!}</td>
                    <td class="text-end"><a href="{{ route('admin.markets.edit', $m) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $markets->links() }}</div>
@endsection

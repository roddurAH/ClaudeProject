@extends('admin.layouts.app')
@section('title', 'Commodities')

@section('content')
<div class="text-end mb-3"><a href="{{ route('admin.commodities.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New commodity</a></div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>#</th><th>Commodity</th><th>Category</th><th>Unit</th><th>Linked crop</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach($commodities as $c)
                <tr>
                    <td class="text-muted">{{ $c->id }}</td>
                    <td>{{ $c->icon }} <strong lang="bn">{{ $c->name_bn }}</strong> <span class="text-muted">{{ $c->name_en }}</span></td>
                    <td>{{ \App\Enums\Options::CROP_CATEGORIES[$c->category] ?? $c->category }}</td>
                    <td>{{ \App\Enums\Options::UNITS[$c->unit] ?? $c->unit }}</td>
                    <td>{{ $c->crop?->name_en ?? '—' }}</td>
                    <td>{!! $c->is_active ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-secondary">Hidden</span>' !!}</td>
                    <td class="text-end"><a href="{{ route('admin.commodities.edit', $c) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $commodities->links() }}</div>
@endsection

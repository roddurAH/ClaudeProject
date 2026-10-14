@extends('admin.layouts.app')
@section('title', 'Crops & calendars')

@section('content')
<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
    <form class="d-flex gap-2" method="GET">
        <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Search crop…">
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('admin.crops.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New crop</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
            <tr><th>Crop</th><th>Category</th><th>Season</th><th class="text-center">Stages</th><th class="text-center">Problems</th><th class="text-center">Fertilizers</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($crops as $crop)
                <tr>
                    <td>
                        <span class="fs-5 me-1">{{ $crop->icon }}</span>
                        <strong lang="bn">{{ $crop->name_bn }}</strong> <span class="text-muted">{{ $crop->name_en }}</span>
                        @if($crop->is_garden_friendly)<span class="badge text-bg-success-subtle text-success-emphasis ms-1">Garden</span>@endif
                    </td>
                    <td>{{ \App\Enums\Options::CROP_CATEGORIES[$crop->category] ?? $crop->category }}</td>
                    <td>{{ \App\Enums\Options::SEASONS[$crop->season] ?? '—' }}</td>
                    <td class="text-center">{{ $crop->stages_count }}</td>
                    <td class="text-center"><a href="{{ route('admin.problems.index', ['crop_id' => $crop->id]) }}">{{ $crop->problems_count }}</a></td>
                    <td class="text-center">{{ $crop->fertilizer_doses_count }}</td>
                    <td>{!! $crop->is_published ? '<span class="badge text-bg-success">Published</span>' : '<span class="badge text-bg-secondary">Draft</span>' !!}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.crops.edit', $crop) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No crops yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $crops->links() }}</div>
@endsection

@extends('admin.layouts.app')
@section('title', 'Pests, diseases & deficiencies')

@section('content')
<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
    <form class="d-flex gap-2 flex-wrap" method="GET">
        <select name="crop_id" class="form-select" onchange="this.form.submit()">
            <option value="">All crops</option>
            @foreach($crops as $c)
                <option value="{{ $c->id }}" @selected(request('crop_id') == $c->id)>{{ $c->name_en }}</option>
            @endforeach
        </select>
        <select name="type" class="form-select" onchange="this.form.submit()">
            <option value="">All types</option>
            @foreach(\App\Enums\Options::PROBLEM_TYPES as $key => $label)
                <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Search…">
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('admin.problems.create', ['crop_id' => request('crop_id')]) }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New problem</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th></th><th>Problem</th><th>Crop</th><th>Type</th><th>Plant part</th><th>Severity</th><th></th></tr></thead>
            <tbody>
            @forelse($problems as $p)
                <tr>
                    <td style="width: 60px">
                        @if($p->image_path)<img src="{{ $p->imageUrl() }}" alt="" class="rounded" style="width: 48px; height: 48px; object-fit: cover">@endif
                    </td>
                    <td><strong lang="bn">{{ $p->name_bn }}</strong><br><small class="text-muted">{{ $p->name_en }}</small>
                        @unless($p->is_published)<span class="badge text-bg-secondary">Draft</span>@endunless
                    </td>
                    <td>{{ $p->crop->icon }} {{ $p->crop->name_en }}</td>
                    <td>{{ \App\Enums\Options::PROBLEM_TYPES[$p->type] ?? $p->type }}</td>
                    <td>{{ \App\Enums\Options::AFFECTED_PARTS[$p->affected_part] ?? $p->affected_part }}</td>
                    <td><span class="badge text-bg-{{ ['low' => 'secondary', 'medium' => 'warning', 'high' => 'danger'][$p->severity] ?? 'light' }}">{{ ucfirst($p->severity) }}</span></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.problems.edit', $p) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <x-delete-button :action="route('admin.problems.destroy', $p)" label="" />
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No problems found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $problems->links() }}</div>
@endsection

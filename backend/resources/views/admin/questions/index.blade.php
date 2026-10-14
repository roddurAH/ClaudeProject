@extends('admin.layouts.app')
@section('title', 'Farmer questions')

@section('content')
<ul class="nav nav-pills mb-3">
    @foreach(['pending' => 'Waiting', 'answered' => 'Answered', 'closed' => 'Closed', 'all' => 'All'] as $key => $label)
        <li class="nav-item">
            <a class="nav-link @if($status === $key) active bg-success @endif" href="{{ route('admin.questions.index', ['status' => $key, 'crop_id' => request('crop_id')]) }}">{{ $label }}</a>
        </li>
    @endforeach
    <li class="ms-auto">
        <form method="GET">
            <input type="hidden" name="status" value="{{ $status }}">
            <select name="crop_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All crops</option>
                @foreach($crops as $c)
                    <option value="{{ $c->id }}" @selected(request('crop_id') == $c->id)>{{ $c->name_en }}</option>
                @endforeach
            </select>
        </form>
    </li>
</ul>

<div class="card">
    <div class="list-group list-group-flush">
        @forelse($questions as $q)
            <a href="{{ route('admin.questions.show', $q) }}" class="list-group-item list-group-item-action py-3">
                <div class="d-flex gap-3">
                    @if($q->image_path)
                        <img src="{{ $q->imageUrl() }}" alt="" class="rounded" style="width: 64px; height: 64px; object-fit: cover">
                    @else
                        <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width: 64px; height: 64px"><i class="bi bi-chat-text fs-3 text-muted"></i></div>
                    @endif
                    <div class="flex-grow-1">
                        <div lang="bn">{{ \Illuminate\Support\Str::limit($q->body, 160) }}</div>
                        <small class="text-muted">
                            {{ $q->crop?->icon }} {{ $q->crop?->name_en ?? 'General' }} ·
                            {{ $q->district?->name_en ?? 'Unknown district' }} ·
                            {{ $q->user->name }} · {{ $q->created_at->diffForHumans() }}
                            @if($q->answers_count) · {{ $q->answers_count }} answer(s) @endif
                            @if($q->is_public) · <span class="text-success"><i class="bi bi-globe"></i> public</span> @endif
                        </small>
                    </div>
                </div>
            </a>
        @empty
            <div class="list-group-item text-muted text-center py-4">No questions here.</div>
        @endforelse
    </div>
</div>
<div class="mt-3">{{ $questions->links() }}</div>
@endsection

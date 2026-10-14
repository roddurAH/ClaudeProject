@extends('admin.layouts.app')
@section('title', 'AI doctor')

@section('content')
<div class="row g-3 mb-3">
    @foreach([
        ['AI answers today', $usage['today'], 'bi-robot'],
        ['Estimated cost today', '$'.number_format($usage['cost_today'], 2), 'bi-cash-coin'],
        ['Estimated cost this month', '$'.number_format($usage['cost_month'], 2), 'bi-calendar-month'],
        ['Marked "not helpful"', $usage['down'], 'bi-hand-thumbs-down'],
    ] as [$label, $value, $icon])
        <div class="col-6 col-xl-3">
            <div class="card h-100"><div class="card-body">
                <div class="d-flex justify-content-between"><span class="text-muted small">{{ $label }}</span><i class="bi {{ $icon }} fs-4 text-success"></i></div>
                <div class="stat-value">{{ $value }}</div>
            </div></div>
        </div>
    @endforeach
</div>
<p class="small text-muted">Model: <code>{{ config('krishi.ai.model') }}</code> · driver <code>{{ config('krishi.ai.driver') }}</code> · {{ config('krishi.ai.daily_limit') }} questions per user per day. Costs are estimates from token counts; the exact bill is in the Anthropic console.</p>

<ul class="nav nav-pills mb-3">
    @foreach(['all' => 'All', 'down' => '👎 Not helpful', 'expert' => 'AI suggested an expert', 'escalated' => 'Sent to expert'] as $key => $label)
        <li class="nav-item">
            <a class="nav-link @if($filter === $key) active bg-success @endif" href="{{ route('admin.ai.index', ['filter' => $key, 'crop_id' => request('crop_id')]) }}">{{ $label }}</a>
        </li>
    @endforeach
    <li class="ms-auto">
        <form method="GET">
            <input type="hidden" name="filter" value="{{ $filter }}">
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
        @forelse($conversations as $c)
            @php($first = $c->messages->firstWhere('role', 'user'))
            @php($photo = $c->messages->whereNotNull('image_path')->first())
            @php($answer = $c->messages->where('role', 'assistant')->last())
            <a href="{{ route('admin.ai.show', $c) }}" class="list-group-item list-group-item-action py-3">
                <div class="d-flex gap-3">
                    @if($photo)
                        <img src="{{ $photo->imageUrl() }}" alt="" class="rounded" style="width: 64px; height: 64px; object-fit: cover">
                    @else
                        <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width: 64px; height: 64px"><i class="bi bi-chat-text fs-3 text-muted"></i></div>
                    @endif
                    <div class="flex-grow-1">
                        <div lang="bn"><strong>{{ \Illuminate\Support\Str::limit($first?->text ?? '(photo only)', 100) }}</strong></div>
                        <div lang="bn" class="text-muted small">🤖 {{ \Illuminate\Support\Str::limit($answer?->text ?? '(no answer)', 140) }}</div>
                        <small class="text-muted">
                            {{ $c->crop?->icon }} {{ $c->crop?->name_en ?? 'General' }} · {{ $c->district?->name_en ?? 'Unknown district' }} ·
                            {{ $c->user->name }} · {{ $c->updated_at->diffForHumans() }} · {{ $c->messages->where('role', 'assistant')->count() }} answer(s)
                            @if($c->rating === 'down') · <span class="text-danger">👎</span> @elseif($c->rating === 'up') · 👍 @endif
                            @if($answer?->result['needs_expert'] ?? false) · <span class="text-warning">AI: ask an expert</span> @endif
                            @if($c->escalated_question_id) · <span class="text-primary">sent to expert</span> @endif
                        </small>
                    </div>
                </div>
            </a>
        @empty
            <div class="list-group-item text-muted text-center py-4">No AI conversations here.</div>
        @endforelse
    </div>
</div>
<div class="mt-3">{{ $conversations->links() }}</div>
@endsection

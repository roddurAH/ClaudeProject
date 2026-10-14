@extends('admin.layouts.app')
@section('title', 'AI conversation')

@section('content')
<a href="{{ route('admin.ai.index') }}" class="small"><i class="bi bi-arrow-left"></i> AI doctor</a>
<div class="row g-3 mt-1">
    <div class="col-lg-8">
        @foreach($conversation->messages as $m)
            @if($m->role === 'user')
                <div class="card mb-3 border-start border-4 border-success">
                    <div class="card-body">
                        <div class="small text-muted mb-2"><i class="bi bi-person"></i> {{ $conversation->user->name }} · {{ $m->created_at->format('d M Y H:i') }} · <code>{{ $m->context }}</code></div>
                        @if($m->image_path)
                            <a href="{{ $m->imageUrl() }}" target="_blank"><img src="{{ $m->imageUrl() }}" alt="" class="rounded mb-2" style="max-width: 100%; max-height: 360px"></a>
                        @endif
                        <div lang="bn">{{ $m->text ?? '(photo only)' }}</div>
                    </div>
                </div>
            @else
                @php($r = $m->result)
                <div class="card mb-3">
                    <div class="card-body" lang="bn">
                        <div class="small text-muted mb-2"><i class="bi bi-robot"></i> {{ $m->model }} · {{ number_format(($m->latency_ms ?? 0) / 1000, 1) }} s ·
                            tokens in {{ $m->input_tokens }} / out {{ $m->output_tokens }} / cache {{ $m->cache_read_tokens }} · ≈ ${{ number_format($m->estimatedCost(), 4) }}</div>
                        @if(! $r)
                            <div class="alert alert-secondary mb-0">The AI declined to answer ({{ $m->stop_reason }}). The farmer was offered an expert.</div>
                        @else
                            @if(! $r['is_plant_photo'])<span class="badge text-bg-secondary mb-2">Not a plant</span>@endif
                            @if($r['needs_expert'])<span class="badge text-bg-warning mb-2">AI suggested an expert</span>@endif
                            <p class="mb-2">{{ $r['answer'] }}</p>
                            @if($r['likely_problems'])
                                <div class="mb-2">
                                    @foreach($r['likely_problems'] as $p)
                                        <span class="badge rounded-pill text-bg-light border me-1">
                                            {{ $p['name'] }} · {{ $p['confidence'] }}
                                            @if($p['problem_id']) · <a href="{{ route('admin.problems.edit', $p['problem_id']) }}">KB #{{ $p['problem_id'] }}</a> @endif
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            @if($r['steps'])<ol class="mb-2">@foreach($r['steps'] as $s)<li>{{ $s }}</li>@endforeach</ol>@endif
                            @if($r['safety_note'])<div class="alert alert-warning py-2 small mb-2">⚠️ {{ $r['safety_note'] }}</div>@endif
                            @if($r['follow_up_question'])<div class="small"><i class="bi bi-question-circle"></i> {{ $r['follow_up_question'] }}</div>@endif
                        @endif
                    </div>
                </div>
            @endif
        @endforeach
    </div>
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-body">
            <h2 class="h6">Farmer</h2>
            <div>{{ $conversation->user->name }}</div>
            <div><a href="tel:{{ $conversation->user->phone }}">{{ $conversation->user->phone }}</a></div>
            <div class="text-muted small">{{ $conversation->district?->name_en ?? 'Unknown district' }} · {{ $conversation->crop?->name_en ?? 'No crop chosen' }}</div>
        </div></div>
        <div class="card mb-3"><div class="card-body">
            <h2 class="h6">Feedback</h2>
            <div>{{ ['up' => '👍 Helpful', 'down' => '👎 Not helpful'][$conversation->rating] ?? 'Not rated' }}</div>
            @if($conversation->feedback)<div class="small mt-1" lang="bn">“{{ $conversation->feedback }}”</div>@endif
            @if($conversation->escalatedQuestion)
                <a class="btn btn-sm btn-outline-primary mt-2" href="{{ route('admin.questions.show', $conversation->escalatedQuestion) }}"><i class="bi bi-chat-dots"></i> Sent to expert – open question</a>
            @endif
        </div></div>
        <div class="small text-muted">If the AI was wrong, correct it in the expert answer and consider improving the matching <a href="{{ route('admin.problems.index') }}">problem</a> in the knowledge base: the AI uses it for every farmer.</div>
    </div>
</div>
@endsection

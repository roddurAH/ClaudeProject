@extends('admin.layouts.app')
@section('title', 'Question #'.$question->id)

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="badge text-bg-{{ ['pending' => 'warning', 'answered' => 'success', 'closed' => 'secondary'][$question->status->value] }}">{{ ucfirst($question->status->value) }}</span>
                    <small class="text-muted">{{ $question->created_at->format('d M Y, h:i A') }} ({{ $question->created_at->diffForHumans() }})</small>
                </div>
                <p class="fs-5" lang="bn" style="white-space: pre-line">{{ $question->body }}</p>
                @if($question->image_path)
                    <a href="{{ $question->imageUrl() }}" target="_blank" rel="noopener">
                        <img src="{{ $question->imageUrl() }}" alt="Farmer's photo" class="img-fluid rounded border" style="max-height: 420px">
                    </a>
                @endif
            </div>
        </div>

        @foreach($question->answers as $answer)
            <div class="card mb-3 border-start border-success border-4">
                <div class="card-body">
                    <small class="text-muted"><i class="bi bi-person-badge"></i> {{ $answer->expert->name }} · {{ $answer->created_at->diffForHumans() }}</small>
                    <p class="mb-0 mt-2" lang="bn" style="white-space: pre-line">{{ $answer->body }}</p>
                </div>
            </div>
        @endforeach

        <div class="card">
            <div class="card-header bg-white"><strong>{{ $question->answers->isEmpty() ? 'Write an answer' : 'Add a follow-up answer' }}</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.questions.answer', $question) }}">
                    @csrf
                    <x-form.textarea name="body" label="Answer (in simple Bangla)" :rows="7" required lang="bn"
                                     help="Tips: name the problem, give 2–4 clear steps, prefer non-chemical control first, mention safety. Suggest calling 16123 or visiting the Upazila Agriculture Office if needed." />
                    <x-form.check name="is_public" label="Publish this Q&A to the community (farmer's name is never shown)" :checked="$question->is_public" />
                    <button class="btn btn-brand"><i class="bi bi-send"></i> Send answer</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-white"><strong>Farmer</strong></div>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item"><i class="bi bi-person"></i> {{ $question->user->name }}</li>
                <li class="list-group-item"><i class="bi bi-telephone"></i> <a href="tel:{{ $question->user->phone }}">{{ $question->user->phone }}</a></li>
                <li class="list-group-item"><i class="bi bi-geo-alt"></i> {{ $question->district?->name_en ?? '—' }}</li>
                <li class="list-group-item"><i class="bi bi-flower1"></i> {{ $question->crop?->name_en ?? 'General' }}</li>
                <li class="list-group-item"><i class="bi bi-person-vcard"></i> {{ \App\Enums\UserType::tryFrom((string) $question->user->user_type)?->label() ?? '—' }}</li>
            </ul>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white"><strong>Actions</strong></div>
            <div class="card-body d-grid gap-2">
                <form method="POST" action="{{ route('admin.questions.update', $question) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="is_public" value="{{ $question->is_public ? 0 : 1 }}">
                    <button class="btn btn-outline-success w-100" @disabled($question->answers->isEmpty())>
                        <i class="bi bi-globe"></i> {{ $question->is_public ? 'Hide from community' : 'Publish to community' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.questions.update', $question) }}">
                    @csrf @method('PATCH')
                    @if($question->status === \App\Enums\QuestionStatus::Closed)
                        <input type="hidden" name="status" value="{{ $question->answers->isEmpty() ? 'pending' : 'answered' }}">
                        <button class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-counterclockwise"></i> Re-open</button>
                    @else
                        <input type="hidden" name="status" value="closed">
                        <button class="btn btn-outline-secondary w-100" onclick="return confirm('Close without answering (e.g. spam or duplicate)?')"><i class="bi bi-x-circle"></i> Close (spam / duplicate)</button>
                    @endif
                </form>
            </div>
        </div>

        @if($related->isNotEmpty())
            <div class="card">
                <div class="card-header bg-white"><strong>Similar answered questions</strong></div>
                <div class="list-group list-group-flush small">
                    @foreach($related as $r)
                        <a class="list-group-item list-group-item-action" href="{{ route('admin.questions.show', $r) }}" lang="bn">{{ \Illuminate\Support\Str::limit($r->body, 80) }}</a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

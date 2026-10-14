@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    @php($cards = [
        ['Pending questions', $stats['pending_questions'], 'bi-chat-dots', 'warning', Gate::allows('answer-questions') ? route('admin.questions.index') : null],
        ['App users', $stats['app_users'], 'bi-people', 'success', Gate::allows('manage-users') ? route('admin.users.index', ['role' => 'farmer']) : null],
        ['New users (7 days)', $stats['new_users_week'], 'bi-person-plus', 'primary', null],
        ['Prices entered today', $stats['prices_today'], 'bi-currency-exchange', 'info', Gate::allows('manage-prices') ? route('admin.prices.entry') : null],
        ['Crops / problems', $stats['crops'].' / '.$stats['problems'], 'bi-flower1', 'success', Gate::allows('manage-content') ? route('admin.crops.index') : null],
        ['Answered questions', $stats['answered_questions'], 'bi-check2-circle', 'secondary', null],
        ['Active alerts', $stats['active_alerts'], 'bi-megaphone', 'danger', Gate::allows('manage-alerts') ? route('admin.alerts.index') : null],
        ['Latest price date', $stats['last_price_date'] ? \Illuminate\Support\Carbon::parse($stats['last_price_date'])->format('d M Y') : '—', 'bi-calendar-event', 'dark', null],
    ])
    @foreach($cards as [$label, $value, $icon, $color, $link])
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">{{ $label }}</span>
                        <i class="bi {{ $icon }} text-{{ $color }} fs-4"></i>
                    </div>
                    <div class="stat-value">{{ $value }}</div>
                    @if($link)<a href="{{ $link }}" class="small stretched-link">Open</a>@endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white"><strong>Waiting longest for an answer</strong></div>
            <div class="list-group list-group-flush">
                @forelse($oldestPending as $q)
                    <a href="{{ Gate::allows('answer-questions') ? route('admin.questions.show', $q) : '#' }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between">
                            <span lang="bn">{{ \Illuminate\Support\Str::limit($q->body, 90) }}</span>
                            <small class="text-danger text-nowrap ms-2">{{ $q->created_at->diffForHumans() }}</small>
                        </div>
                        <small class="text-muted">{{ $q->crop?->name_en ?? 'General' }} · {{ $q->district?->name_en ?? 'Unknown district' }}</small>
                    </a>
                @empty
                    <div class="list-group-item text-muted">No pending questions. 🎉</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-white"><strong>App users by type</strong></div>
            <ul class="list-group list-group-flush">
                @forelse($usersByType as $type => $total)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ \App\Enums\UserType::tryFrom((string) $type)?->label() ?? 'Not set' }}</span><strong>{{ $total }}</strong>
                    </li>
                @empty
                    <li class="list-group-item text-muted">No app users yet.</li>
                @endforelse
            </ul>
        </div>
        <div class="card">
            <div class="card-header bg-white"><strong>Most asked-about crops</strong></div>
            <ul class="list-group list-group-flush">
                @forelse($topCrops as $crop)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $crop->name_en }} <span class="text-muted" lang="bn">({{ $crop->name_bn }})</span></span><strong>{{ $crop->total }}</strong>
                    </li>
                @empty
                    <li class="list-group-item text-muted">No questions yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection

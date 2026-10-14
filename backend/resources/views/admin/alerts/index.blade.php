@extends('admin.layouts.app')
@section('title', 'Alerts')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <p class="text-muted small mb-0">Alerts appear at the top of the app's home screen for the targeted districts while they are active.</p>
    <a href="{{ route('admin.alerts.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> New alert</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Alert</th><th>Target</th><th>Active period</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($alerts as $a)
                @php($live = $a->starts_at <= now() && (! $a->ends_at || $a->ends_at >= now()))
                <tr>
                    <td><span class="badge text-bg-{{ $a->severity->color() }}">{{ ucfirst($a->severity->value) }}</span>
                        <strong lang="bn">{{ $a->title_bn }}</strong><br><small class="text-muted">{{ $a->title_en }}</small></td>
                    <td class="small">{{ $a->is_national ? 'Whole country' : $a->districts->pluck('name_en')->join(', ') }}</td>
                    <td class="small text-nowrap">{{ $a->starts_at->format('d M H:i') }} → {{ $a->ends_at?->format('d M H:i') ?? 'no end' }}</td>
                    <td>{!! $live ? '<span class="badge text-bg-success">Live</span>' : '<span class="badge text-bg-secondary">Not live</span>' !!}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.alerts.edit', $a) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <x-delete-button :action="route('admin.alerts.destroy', $a)" label="" />
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No alerts.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $alerts->links() }}</div>
@endsection

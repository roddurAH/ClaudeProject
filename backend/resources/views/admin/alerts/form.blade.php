@extends('admin.layouts.app')
@section('title', $alert->exists ? 'Edit alert' : 'New alert')

@php($selectedIds = array_map('intval', old('district_ids', $selected)))
@section('content')
<form method="POST" action="{{ $alert->exists ? route('admin.alerts.update', $alert) : route('admin.alerts.store') }}">
    @csrf
    @if($alert->exists) @method('PUT') @endif
    <div class="card mb-3"><div class="card-body">
        <div class="row">
            <div class="col-md-3"><x-form.select name="severity" label="Severity" :options="['info' => 'Info (blue)', 'warning' => 'Warning (yellow)', 'danger' => 'Danger (red)']" :value="$alert->severity" required /></div>
            <div class="col-md-3"><x-form.input name="starts_at" type="datetime-local" label="Starts" :value="$alert->starts_at?->format('Y-m-d\TH:i')" required /></div>
            <div class="col-md-3"><x-form.input name="ends_at" type="datetime-local" label="Ends (optional)" :value="$alert->ends_at?->format('Y-m-d\TH:i')" /></div>
        </div>
        <x-form.bilingual name="title" label="Title" :model="$alert" required />
        <x-form.bilingual name="body" label="Message" :model="$alert" textarea :rows="4" required help="Say what is happening and exactly what farmers should do." />
    </div></div>

    <div class="card mb-3"><div class="card-body">
        <x-form.check name="is_national" label="Send to the whole country" :checked="$alert->is_national" />
        <div id="districtPicker">
            <p class="small text-muted">Or choose districts (tick a division name to select all its districts):</p>
            <div class="row">
                @foreach($divisions as $division)
                    <div class="col-md-3 mb-3">
                        <div class="form-check">
                            <input class="form-check-input division-toggle" type="checkbox" id="div{{ $division->id }}" data-division="{{ $division->id }}">
                            <label class="form-check-label fw-semibold" for="div{{ $division->id }}">{{ $division->name_en }}</label>
                        </div>
                        @foreach($division->districts as $d)
                            <div class="form-check ms-3">
                                <input class="form-check-input" type="checkbox" name="district_ids[]" value="{{ $d->id }}" id="d{{ $d->id }}" data-division="{{ $division->id }}" @checked(in_array($d->id, $selectedIds, true))>
                                <label class="form-check-label small" for="d{{ $d->id }}">{{ $d->name_en }}</label>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div></div>

    <div class="text-end">
        <a href="{{ route('admin.alerts.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button class="btn btn-brand"><i class="bi bi-megaphone"></i> Save alert</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
const national = document.getElementById('f_is_national');
const picker = document.getElementById('districtPicker');
const sync = () => picker.style.display = national.checked ? 'none' : '';
national.addEventListener('change', sync); sync();
document.querySelectorAll('.division-toggle').forEach(t => t.addEventListener('change', () => {
    document.querySelectorAll(`input[name="district_ids[]"][data-division="${t.dataset.division}"]`).forEach(cb => cb.checked = t.checked);
}));
</script>
@endpush

@extends('admin.layouts.app')
@section('title', $problem->exists ? 'Edit problem' : 'New problem')

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $problem->exists ? route('admin.problems.update', $problem) : route('admin.problems.store') }}">
    @csrf
    @if($problem->exists) @method('PUT') @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><x-form.select name="crop_id" label="Crop" :options="$crops->pluck('name_en', 'id')->all()" :value="$problem->crop_id" placeholder="Choose…" required /></div>
                <div class="col-md-3"><x-form.select name="type" label="Type" :options="\App\Enums\Options::PROBLEM_TYPES" :value="$problem->type" required /></div>
                <div class="col-md-3"><x-form.select name="affected_part" label="Where on the plant?" :options="\App\Enums\Options::AFFECTED_PARTS" :value="$problem->affected_part" required
                                                     help="Farmers find problems by tapping the plant part." /></div>
                <div class="col-md-3"><x-form.select name="severity" label="Severity" :options="\App\Enums\Options::SEVERITIES" :value="$problem->severity" required /></div>
            </div>
            <x-form.bilingual name="name" label="Name (use the local name farmers know)" :model="$problem" required />
            <div class="row">
                <div class="col-md-6">
                    <x-form.input name="image" type="file" label="Photo of the symptom" accept="image/*" help="A clear close-up photo helps farmers recognise the problem." />
                </div>
                <div class="col-md-6">
                    @if($problem->image_path)<img src="{{ $problem->imageUrl() }}" alt="" class="img-thumbnail" style="max-height: 120px">@endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white"><strong>Advice</strong> <span class="text-muted small">– one point per line, simple words</span></div>
        <div class="card-body">
            <x-form.bilingual name="symptoms" label="Symptoms – how to recognise it" :model="$problem" textarea :rows="4" required />
            <x-form.bilingual name="prevention" label="Prevention & cultural control (IPM, shown first)" :model="$problem" textarea :rows="4" />
            <x-form.bilingual name="organic_control" label="Organic / biological control" :model="$problem" textarea :rows="3" />
            <x-form.bilingual name="chemical_control" label="Chemical control (last resort – name active ingredient, not brand)" :model="$problem" textarea :rows="3"
                              help="The app always shows a safety warning above this section." />
            <x-form.check name="is_published" label="Published (visible in app)" :checked="$problem->is_published" />
        </div>
        <div class="card-footer bg-white text-end">
            <a href="{{ route('admin.problems.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-brand"><i class="bi bi-save"></i> Save</button>
        </div>
    </div>
</form>
@endsection

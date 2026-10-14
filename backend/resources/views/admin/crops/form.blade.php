@extends('admin.layouts.app')
@section('title', $crop->exists ? 'Edit crop: '.$crop->name_en : 'New crop')

@php
    $months = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'];
    $sowing = array_map('intval', old('sowing_months', $crop->sowing_months ?? []));
    $harvest = array_map('intval', old('harvest_months', $crop->harvest_months ?? []));
@endphp

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $crop->exists ? route('admin.crops.update', $crop) : route('admin.crops.store') }}">
    @csrf
    @if($crop->exists) @method('PUT') @endif

    <div class="card mb-4">
        <div class="card-header bg-white"><strong>Basic information</strong></div>
        <div class="card-body">
            <x-form.bilingual name="name" label="Name" :model="$crop" required />
            <div class="row">
                <div class="col-md-3"><x-form.input name="slug" label="Slug (URL key)" :value="$crop->slug" help="Auto-generated from English name if empty." /></div>
                <div class="col-md-3"><x-form.select name="category" label="Category" :options="\App\Enums\Options::CROP_CATEGORIES" :value="$crop->category" required /></div>
                <div class="col-md-3"><x-form.select name="season" label="Season" :options="\App\Enums\Options::SEASONS" :value="$crop->season" placeholder="—" /></div>
                <div class="col-md-3"><x-form.input name="duration_days" type="number" label="Duration (days)" :value="$crop->duration_days" /></div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Sowing / planting months</label>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($months as $num => $name)
                            <input type="checkbox" class="btn-check" name="sowing_months[]" id="sow{{ $num }}" value="{{ $num }}" @checked(in_array($num, $sowing, true))>
                            <label class="btn btn-sm btn-outline-success" for="sow{{ $num }}">{{ $name }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Harvest months</label>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($months as $num => $name)
                            <input type="checkbox" class="btn-check" name="harvest_months[]" id="har{{ $num }}" value="{{ $num }}" @checked(in_array($num, $harvest, true))>
                            <label class="btn btn-sm btn-outline-warning" for="har{{ $num }}">{{ $name }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2"><x-form.input name="icon" label="Emoji icon" :value="$crop->icon" help="Shown if no image." /></div>
                <div class="col-md-4">
                    <x-form.input name="image" type="file" label="Photo" accept="image/*" help="Resized to 1280 px automatically." />
                    @if($crop->image_path)<img src="{{ $crop->imageUrl() }}" alt="" class="img-thumbnail mb-3" style="max-height: 90px">@endif
                </div>
                <div class="col-md-2"><x-form.input name="sort_order" type="number" label="Sort order" :value="$crop->sort_order ?? 0" /></div>
                <div class="col-md-4 pt-4">
                    <x-form.check name="is_garden_friendly" label="Suitable for rooftop / homestead garden" :checked="$crop->is_garden_friendly" />
                    <x-form.check name="is_published" label="Published (visible in app)" :checked="$crop->is_published" />
                </div>
            </div>
            <x-form.bilingual name="summary" label="Short summary" :model="$crop" textarea :rows="2" />
            <x-form.bilingual name="cultivation" label="Cultivation guide" :model="$crop" textarea :rows="10"
                              help="One tip per line. Keep sentences short and simple." />
        </div>
        <div class="card-footer bg-white text-end">
            <button class="btn btn-brand"><i class="bi bi-save"></i> Save crop</button>
        </div>
    </div>
</form>

@if($crop->exists)
    {{-- Calendar stages --}}
    <div class="card mb-4" id="stages">
        <div class="card-header bg-white">
            <strong>Crop calendar</strong>
            <span class="text-muted small">– day 0 = sowing / transplanting date the farmer enters in "My crops"</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th style="width: 80px">Day</th><th>Type</th><th>Task</th><th></th></tr></thead>
                <tbody>
                @forelse($crop->stages as $stage)
                    <tr>
                        <td><span class="badge text-bg-light border">{{ $stage->day_offset }}</span></td>
                        <td class="small">{{ \App\Enums\Options::STAGE_TYPES[$stage->stage_type] ?? $stage->stage_type }}</td>
                        <td><span lang="bn">{{ $stage->title_bn }}</span><br><small class="text-muted">{{ $stage->title_en }}</small></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.stages.edit', $stage) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <x-delete-button :action="route('admin.stages.destroy', $stage)" label="" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center py-3">No stages yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">
            <h6>Add stage</h6>
            <form method="POST" action="{{ route('admin.crops.stages.store', $crop) }}">
                @csrf
                <div class="row">
                    <div class="col-md-2"><x-form.input name="day_offset" type="number" label="Day" value="0" required /></div>
                    <div class="col-md-4"><x-form.select name="stage_type" label="Type" :options="\App\Enums\Options::STAGE_TYPES" required /></div>
                </div>
                <x-form.bilingual name="title" label="Task title" required />
                <x-form.bilingual name="details" label="Details" textarea :rows="2" />
                <button class="btn btn-outline-success"><i class="bi bi-plus-lg"></i> Add stage</button>
            </form>
        </div>
    </div>

    {{-- Fertilizer doses --}}
    <div class="card mb-4" id="doses">
        <div class="card-header bg-white">
            <strong>Fertilizer recommendation</strong>
            <span class="text-muted small">– kg per hectare for medium-fertility soil (BARC Fertilizer Recommendation Guide)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Fertilizer</th><th>kg / hectare</th><th>≈ kg / bigha (33 dec)</th><th>How to apply</th><th></th></tr></thead>
                <tbody>
                @forelse($crop->fertilizerDoses as $dose)
                    <tr>
                        <td>{{ $dose->fertilizer->name_en }} <span class="text-muted" lang="bn">{{ $dose->fertilizer->name_bn }}</span></td>
                        <td>{{ $dose->kg_per_hectare }}</td>
                        <td>{{ round($dose->kg_per_hectare * 33 / 247.105, 1) }}</td>
                        <td class="small" lang="bn">{{ \Illuminate\Support\Str::limit($dose->application_bn, 80) }}</td>
                        <td class="text-end"><x-delete-button :action="route('admin.doses.destroy', $dose)" label="" /></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center py-3">No fertilizer doses yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">
            <h6>Add or update a dose</h6>
            <form method="POST" action="{{ route('admin.crops.doses.store', $crop) }}">
                @csrf
                <div class="row">
                    <div class="col-md-4"><x-form.select name="fertilizer_id" label="Fertilizer" :options="$fertilizers->pluck('name_en', 'id')->all()" required /></div>
                    <div class="col-md-3"><x-form.input name="kg_per_hectare" type="number" step="0.1" label="kg per hectare" required /></div>
                </div>
                <x-form.bilingual name="application" label="When / how to apply" textarea :rows="2" />
                <button class="btn btn-outline-success"><i class="bi bi-save"></i> Save dose</button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Pests, diseases & deficiencies ({{ $crop->problems->count() }})</strong>
            <a href="{{ route('admin.problems.create', ['crop_id' => $crop->id]) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-plus-lg"></i> Add problem</a>
        </div>
        <ul class="list-group list-group-flush">
            @foreach($crop->problems as $problem)
                <li class="list-group-item d-flex justify-content-between">
                    <span><span lang="bn">{{ $problem->name_bn }}</span> <small class="text-muted">{{ $problem->name_en }}</small></span>
                    <a href="{{ route('admin.problems.edit', $problem) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="text-end">
        <x-delete-button :action="route('admin.crops.destroy', $crop)" label="Delete crop"
                         confirm="Delete this crop together with its calendar, problems and fertilizer doses?" />
    </div>
@endif
@endsection

@extends('admin.layouts.app')
@section('title', 'Edit calendar stage')

@section('content')
<div class="card">
    <div class="card-body">
        <p class="text-muted">Crop: <a href="{{ route('admin.crops.edit', $stage->crop_id) }}">{{ $stage->crop->name_en }}</a></p>
        <form method="POST" action="{{ route('admin.stages.update', $stage) }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-2"><x-form.input name="day_offset" type="number" label="Day" :value="$stage->day_offset" required /></div>
                <div class="col-md-4"><x-form.select name="stage_type" label="Type" :options="$stageTypes" :value="$stage->stage_type" required /></div>
            </div>
            <x-form.bilingual name="title" label="Task title" :model="$stage" required />
            <x-form.bilingual name="details" label="Details" :model="$stage" textarea :rows="4" />
            <a href="{{ route('admin.crops.edit', $stage->crop_id) }}#stages" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-brand"><i class="bi bi-save"></i> Save</button>
        </form>
    </div>
</div>
@endsection

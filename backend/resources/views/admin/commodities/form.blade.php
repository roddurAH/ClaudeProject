@extends('admin.layouts.app')
@section('title', $commodity->exists ? 'Edit commodity' : 'New commodity')

@section('content')
<div class="card"><div class="card-body">
    <form method="POST" action="{{ $commodity->exists ? route('admin.commodities.update', $commodity) : route('admin.commodities.store') }}">
        @csrf
        @if($commodity->exists) @method('PUT') @endif
        <x-form.bilingual name="name" label="Name" :model="$commodity" required />
        <div class="row">
            <div class="col-md-3"><x-form.select name="category" label="Category" :options="\App\Enums\Options::CROP_CATEGORIES" :value="$commodity->category" required /></div>
            <div class="col-md-3"><x-form.select name="unit" label="Price unit" :options="\App\Enums\Options::UNITS" :value="$commodity->unit" required /></div>
            <div class="col-md-3"><x-form.select name="crop_id" label="Linked crop (optional)" :options="$crops->pluck('name_en', 'id')->all()" :value="$commodity->crop_id" placeholder="—" /></div>
            <div class="col-md-1"><x-form.input name="icon" label="Emoji" :value="$commodity->icon" /></div>
            <div class="col-md-2"><x-form.input name="sort_order" type="number" label="Sort" :value="$commodity->sort_order ?? 0" /></div>
        </div>
        <x-form.check name="is_active" label="Active (shown in app and entry form)" :checked="$commodity->is_active" />
        <a href="{{ route('admin.commodities.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button class="btn btn-brand"><i class="bi bi-save"></i> Save</button>
    </form>
</div></div>
@endsection

@extends('admin.layouts.app')
@section('title', 'Edit fertilizer: '.$fertilizer->name_en)

@section('content')
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('admin.fertilizers.update', $fertilizer) }}">
        @csrf @method('PUT')
        <x-form.bilingual name="name" label="Name" :model="$fertilizer" required />
        <div class="row">
            <div class="col-md-4"><x-form.input name="official_price_per_kg" type="number" step="0.01" label="Official farmer price (৳ per kg)" :value="$fertilizer->official_price_per_kg" /></div>
            <div class="col-md-2"><x-form.input name="sort_order" type="number" label="Sort" :value="$fertilizer->sort_order" /></div>
        </div>
        <x-form.bilingual name="notes" label="Notes" :model="$fertilizer" textarea :rows="3" />
        <a href="{{ route('admin.fertilizers.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button class="btn btn-brand"><i class="bi bi-save"></i> Save</button>
    </form>
</div></div>
@endsection

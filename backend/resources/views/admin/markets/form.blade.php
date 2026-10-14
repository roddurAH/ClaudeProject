@extends('admin.layouts.app')
@section('title', $market->exists ? 'Edit market' : 'New market')

@section('content')
<div class="card"><div class="card-body">
    <form method="POST" action="{{ $market->exists ? route('admin.markets.update', $market) : route('admin.markets.store') }}">
        @csrf
        @if($market->exists) @method('PUT') @endif
        <x-form.bilingual name="name" label="Market name" :model="$market" required />
        <div class="row">
            <div class="col-md-4"><x-form.select name="district_id" label="District" :options="$districts->pluck('name_en', 'id')->all()" :value="$market->district_id" placeholder="Choose…" required /></div>
            <div class="col-md-4"><x-form.select name="type" label="Type" :options="\App\Enums\Options::MARKET_TYPES" :value="$market->type" required /></div>
            <div class="col-md-4 pt-4"><x-form.check name="is_active" label="Active" :checked="$market->is_active" /></div>
        </div>
        <a href="{{ route('admin.markets.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button class="btn btn-brand"><i class="bi bi-save"></i> Save</button>
    </form>
</div></div>
@endsection

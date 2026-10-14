@extends('admin.layouts.app')
@section('title', $user->exists ? 'Edit user: '.$user->name : 'New user / staff')

@php($isSelf = $user->exists && $user->is(auth()->user()))
@section('content')
<div class="card"><div class="card-body">
    <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
        @csrf
        @if($user->exists) @method('PUT') @endif
        <div class="row">
            <div class="col-md-4"><x-form.input name="name" label="Full name" :value="$user->name" required /></div>
            <div class="col-md-4"><x-form.input name="phone" label="Mobile number" :value="$user->phone" placeholder="01XXXXXXXXX" required /></div>
            <div class="col-md-4"><x-form.input name="email" type="email" label="Email (optional)" :value="$user->email" /></div>
        </div>
        <div class="row">
            <div class="col-md-4">
                @if($isSelf)
                    <label class="form-label">Role</label>
                    <input class="form-control mb-3" value="{{ $user->role->label() }}" disabled>
                    <input type="hidden" name="role" value="{{ $user->role->value }}">
                @else
                    <x-form.select name="role" label="Role" :options="collect($roles)->mapWithKeys(fn ($r) => [$r->value => $r->label()])->all()" :value="$user->role ?? 'farmer'" required
                                   help="Experts answer questions & edit crop content. Data entry staff manage prices." />
                @endif
            </div>
            <div class="col-md-4"><x-form.select name="user_type" label="User type" :options="collect(\App\Enums\UserType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])->all()" :value="$user->user_type" placeholder="—" /></div>
            <div class="col-md-4"><x-form.select name="district_id" label="District" :options="$districts->pluck('name_en', 'id')->all()" :value="$user->district_id" placeholder="—" /></div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <x-form.input name="password" type="password" :label="$user->exists ? 'New password / PIN (leave empty to keep)' : 'Password / PIN'" :required="! $user->exists"
                              help="Staff: at least 8 characters with letters and numbers. App users: 4–6 digit PIN." autocomplete="new-password" />
            </div>
            <div class="col-md-4 pt-4">
                @unless($isSelf)<x-form.check name="is_active" label="Account active" :checked="$user->is_active" />@else<input type="hidden" name="is_active" value="1">@endunless
            </div>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button class="btn btn-brand"><i class="bi bi-save"></i> Save</button>
    </form>
</div></div>

@if ($user->exists && $user->role === \App\Enums\Role::Farmer)
    <div class="card mt-3 border-danger"><div class="card-body">
        <h2 class="h6 text-danger">Delete account</h2>
        <p class="small text-muted mb-2">For deletion requests from users who cannot sign in (e.g. forgot their PIN). Verify that the request really comes from the owner of this number first.
            Deletes the profile, all questions, photos and answers permanently. Users can also do this themselves in the app or at <a href="{{ route('account.delete') }}" target="_blank">{{ route('account.delete') }}</a>.</p>
        <x-delete-button :action="route('admin.users.destroy', $user)" label="Delete account and all data" confirm="Permanently delete this account, its questions, photos and answers?" />
    </div></div>
@endif
@endsection

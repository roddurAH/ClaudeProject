@extends('admin.layouts.app')
@section('title', 'Users & staff')

@section('content')
<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
    <form class="d-flex gap-2" method="GET">
        <select name="role" class="form-select" onchange="this.form.submit()">
            <option value="">All roles</option>
            @foreach(\App\Enums\Role::cases() as $r)
                <option value="{{ $r->value }}" @selected(request('role') === $r->value)>{{ $r->label() }}</option>
            @endforeach
        </select>
        <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Name, phone or email">
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('admin.users.create') }}" class="btn btn-brand"><i class="bi bi-person-plus"></i> New user / staff</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Name</th><th>Phone</th><th>Role</th><th>Type</th><th>District</th><th>Last login</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach($users as $u)
                <tr>
                    <td>{{ $u->name }}<br><small class="text-muted">{{ $u->email }}</small></td>
                    <td>{{ $u->phone }}</td>
                    <td><span class="badge text-bg-{{ $u->isStaff() ? 'primary' : 'light border' }}">{{ $u->role->label() }}</span></td>
                    <td class="small">{{ \App\Enums\UserType::tryFrom((string) $u->user_type)?->label() ?? '—' }}</td>
                    <td class="small">{{ $u->district?->name_en ?? '—' }}</td>
                    <td class="small text-muted">{{ $u->last_login_at?->diffForHumans() ?? 'never' }}</td>
                    <td>{!! $u->is_active ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-danger">Disabled</span>' !!}</td>
                    <td class="text-end"><a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection

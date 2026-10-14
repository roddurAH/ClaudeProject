<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\District;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Admins manage app users and expert / data-entry staff.
 * Only a super admin can create or change admin and super-admin accounts,
 * and nobody can demote or disable their own account (prevents lock-out).
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('district')
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%'.$request->string('q').'%')
                ->orWhere('phone', 'like', '%'.$request->string('q').'%')
                ->orWhere('email', 'like', '%'.$request->string('q').'%')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User(['is_active' => true]),
            'roles' => $this->assignableRoles(),
            'districts' => District::orderBy('name_en')->get(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->authorizeRole(Role::from($data['role']));

        $user = new User($data);
        $user->role = Role::from($data['role']);
        $user->is_active = $data['is_active'];
        $user->save();

        return redirect()->route('admin.users.index')->with('status', 'User created.');
    }

    public function edit(User $user): View
    {
        $this->authorizeTarget($user);

        return view('admin.users.form', [
            'user' => $user,
            'roles' => $this->assignableRoles(),
            'districts' => District::orderBy('name_en')->get(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->authorizeTarget($user);
        $data = $request->validated();
        $newRole = Role::from($data['role']);
        $isSelf = $user->is($request->user());
        if (! ($isSelf && $newRole === $user->role)) {
            $this->authorizeRole($newRole);
        }

        if ($isSelf && ($newRole !== $user->role || ! $data['is_active'])) {
            throw ValidationException::withMessages(['role' => 'You cannot change your own role or disable yourself.']);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->fill($data);
        $user->role = $newRole;
        $user->is_active = $data['is_active'];
        $user->save();

        if (! $user->is_active) {
            $user->tokens()->delete(); // sign the user out of the app immediately
        }

        return redirect()->route('admin.users.index')->with('status', 'User saved.');
    }

    /** @return list<Role> */
    private function assignableRoles(): array
    {
        return Gate::allows('manage-admins')
            ? Role::cases()
            : [Role::Expert, Role::DataEntry, Role::Farmer];
    }

    private function authorizeRole(Role $role): void
    {
        abort_unless(in_array($role, $this->assignableRoles(), true), 403, 'You may not assign this role.');
    }

    private function authorizeTarget(User $user): void
    {
        $isAdminAccount = in_array($user->role, [Role::Admin, Role::SuperAdmin], true);
        abort_if($isAdminAccount && Gate::denies('manage-admins') && ! $user->is(auth()->user()), 403, 'Only a super admin can edit admin accounts.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with(['roles', 'company'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($companyId = $request->input('company_id')) {
            $query->where('company_id', $companyId);
        }

        if ($roleId = $request->input('role_id')) {
            $query->whereHas('roles', fn ($q) => $q->where('roles.id', $roleId));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::orderBy('name')->get();
        $companies = Company::orderBy('name')->get();

        return view('users.index', compact('users', 'roles', 'companies'));
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        return view('users.create', compact('roles', 'companies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if (Auth::user()?->isOwner()) {
            $rules['company_id'] = ['nullable', 'exists:companies,id'];
        }

        $validated = $request->validate($rules);

        $companyId = Auth::user()?->isOwner()
            ? ($validated['company_id'] ?? Auth::user()->company_id)
            : Auth::user()->company_id;

        $user = User::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (! empty($validated['role_id'])) {
            $role = Role::find($validated['role_id']);
            if ($role) {
                $user->syncRoles([$role]);
            }
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $roles = Role::orderBy('name')->get();
        $companies = Company::orderBy('name')->get();

        return view('users.edit', compact('user', 'roles', 'companies'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if (Auth::user()?->isOwner()) {
            $rules['company_id'] = ['nullable', 'exists:companies,id'];
        }

        $validated = $request->validate($rules);

        // Guard against self deactivation
        if ($user->id === Auth::id() && ! $request->boolean('is_active')) {
            return back()->withInput()->with('error', 'You cannot deactivate your own logged-in account.');
        }

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $request->boolean('is_active', true),
        ];

        if (Auth::user()?->isOwner() && array_key_exists('company_id', $validated)) {
            $userData['company_id'] = $validated['company_id'];
        }

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        if (! empty($validated['role_id'])) {
            $role = Role::find($validated['role_id']);
            if ($role) {
                $user->syncRoles([$role]);
            }
        } else {
            $user->syncRoles([]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isSuperAdmin() && User::role(['super-admin', 'Super Admin'])->count() <= 1) {
            return back()->with('error', 'Cannot delete the only remaining Super Admin.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}

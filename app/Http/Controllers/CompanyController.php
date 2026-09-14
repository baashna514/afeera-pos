<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        if (! auth()->user()?->isOwner()) {
            abort(403, 'Only System Owner can access Companies & Tenants management.');
        }

        $query = Company::withCount(['users', 'products', 'sales'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        $companies = $query->paginate(10)->withQueryString();

        return view('companies.index', compact('companies'));
    }

    public function create(): View
    {
        if (! auth()->user()?->isOwner()) {
            abort(403, 'Only System Owner can access Companies & Tenants management.');
        }

        return view('companies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (! auth()->user()?->isOwner()) {
            abort(403, 'Only System Owner can register new Companies & Tenants.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:companies,code'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'currency' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
            'admin_name' => ['nullable', 'string', 'max:255'],
            'admin_email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['nullable', 'string', 'min:6'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['currency'] = $validated['currency'] ?? 'PKR';

        $company = Company::create([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'currency' => $validated['currency'],
            'is_active' => $validated['is_active'],
        ]);

        // Auto-create default Super Admin for the new company
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web'],
            [
                'slug' => 'super-admin',
                'description' => 'Full unrestricted company access with automatic permission rights.',
                'is_system' => true,
            ]
        );

        $adminEmail = $validated['admin_email'] ?? ('admin.'.Str::slug($company->name).'@smartpos.com');
        $adminPassword = $validated['admin_password'] ?? 'password123';
        $adminName = $validated['admin_name'] ?? ($company->name.' Super Admin');

        $superAdminUser = User::create([
            'company_id' => $company->id,
            'name' => $adminName,
            'email' => $adminEmail,
            'password' => Hash::make($adminPassword),
            'is_active' => true,
        ]);

        $superAdminUser->syncRoles([$superAdminRole]);

        return redirect()->route('companies.index')
            ->with('success', 'Company "'.$company->name.'" registered successfully with Super Admin user: '.$adminEmail);
    }

    public function edit(Company $company): View
    {
        if (! auth()->user()?->isOwner()) {
            abort(403, 'Only System Owner can edit Companies & Tenants.');
        }

        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        if (! auth()->user()?->isOwner()) {
            abort(403, 'Only System Owner can update Companies & Tenants.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('companies')->ignore($company->id)],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'currency' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $company->update($validated);

        return redirect()->route('companies.index')
            ->with('success', 'Company "'.$company->name.'" updated successfully.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        if (! auth()->user()?->isOwner()) {
            abort(403, 'Only System Owner can delete Companies & Tenants.');
        }
        if ($company->users()->count() > 0 || $company->sales()->count() > 0 || $company->products()->count() > 0) {
            return back()->with('error', 'Cannot delete company with active records (users, products, or sales). You may deactivate it instead.');
        }

        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Company deleted successfully.');
    }
}

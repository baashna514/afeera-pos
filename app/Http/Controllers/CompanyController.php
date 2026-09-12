<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
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
        return view('companies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:companies,code'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'currency' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['currency'] = $validated['currency'] ?? 'PKR';

        $company = Company::create($validated);

        return redirect()->route('companies.index')
            ->with('success', 'Company "'.$company->name.'" registered successfully.');
    }

    public function edit(Company $company): View
    {
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
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
        if ($company->users()->count() > 0 || $company->sales()->count() > 0 || $company->products()->count() > 0) {
            return back()->with('error', 'Cannot delete company with active records (users, products, or sales). You may deactivate it instead.');
        }

        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Company deleted successfully.');
    }
}

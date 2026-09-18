<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Purchase;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\User;
use App\Services\CompanySettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Owner (Level 1) SaaS multi-company dashboard.
     */
    public function index(Request $request): View
    {
        // Fetch all tenant companies with aggregated metrics
        $query = Company::withCount(['users', 'products', 'categories'])
            ->withSum('sales', 'total_amount')
            ->withSum('purchases', 'total_amount')
            ->withSum('saleReturns', 'total_amount')
            ->with(['users' => function ($q) {
                $q->whereHas('roles', fn ($rq) => $rq->whereIn('name', ['Super Admin', 'super-admin']));
            }]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $companies = $query->latest()->get();

        // Platform-wide aggregate KPIs
        $totalCompanies = Company::count();
        $totalUsers = User::withoutGlobalScopes()->whereNotNull('company_id')->count();
        $totalPlatformSales = (float) Sale::withoutGlobalScopes()->sum('total_amount');
        $totalPlatformPurchases = (float) Purchase::withoutGlobalScopes()->sum('total_amount');
        $totalPlatformReturns = (float) SaleReturn::withoutGlobalScopes()->sum('total_amount');

        return view('owner.dashboard', compact(
            'companies',
            'totalCompanies',
            'totalUsers',
            'totalPlatformSales',
            'totalPlatformPurchases',
            'totalPlatformReturns'
        ));
    }

    /**
     * Store a newly created tenant company and auto-provision its Level 2 Super Admin.
     */
    public function storeCompany(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_code' => ['nullable', 'string', 'max:50', 'unique:companies,code'],
            'currency' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:6'],
        ]);

        $code = ! empty($validated['company_code'])
            ? strtoupper(trim($validated['company_code']))
            : 'COMP-'.strtoupper(Str::random(4));

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('companies', 'public');
        }

        // 1. Create the Tenant Company
        $company = Company::create([
            'name' => $validated['company_name'],
            'code' => $code,
            'email' => $validated['admin_email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'currency' => $validated['currency'] ?? 'PKR',
            'logo' => $logoPath,
            'is_active' => true,
        ]);

        if ($logoPath) {
            CompanySettingService::set('branding.logo_dark', $logoPath, $company->id);
            CompanySettingService::set('branding.logo_light', $logoPath, $company->id);
        }

        // 2. Auto-create the Company's Level 2 Super Admin
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web'],
            [
                'slug' => 'super-admin',
                'description' => 'Level 2 Company Admin with full control over their tenant company data, users, and settings.',
                'is_system' => true,
            ]
        );

        $superAdmin = User::create([
            'company_id' => $company->id,
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'is_active' => true,
        ]);

        $superAdmin->syncRoles([$superAdminRole]);

        return redirect()->route('owner.dashboard')
            ->with('success', "Company '{$company->name}' and Super Admin account ({$superAdmin->email}) created successfully!");
    }

    /**
     * Delete/deactivate a tenant company.
     */
    public function destroyCompany(Company $company): RedirectResponse
    {
        $companyName = $company->name;

        // Delete associated company users
        User::withoutGlobalScopes()->where('company_id', $company->id)->delete();
        $company->delete();

        return redirect()->route('owner.dashboard')
            ->with('success', "Company '{$companyName}' and its users deleted successfully.");
    }
}

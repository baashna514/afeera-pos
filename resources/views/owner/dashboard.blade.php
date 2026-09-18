@extends('layouts.app', ['title' => 'Platform Owner Dashboard'])

@section('content')
<div class="space-y-6 pb-12">

    <!-- Top Architecture Banner (Matching SaaS Multi-Tenant Diagram) -->
    <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-indigo-900/40 relative overflow-hidden">
        <div class="absolute -right-12 -top-12 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-12 w-64 h-64 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center relative z-10">
            <!-- Left Branding -->
            <div class="lg:col-span-4 space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-xs font-bold uppercase tracking-wider">
                    <i class="fa-solid fa-cloud"></i> SaaS Multi-Tenant Platform
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3">
                    <span>👑 Owner Dashboard</span>
                </h1>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Platform-wide multi-company oversight, tenant lifecycle provisioning, and consolidated analytics across all client companies.
                </p>
            </div>

            <!-- Middle: Owner Role Profile Box (Level 1) -->
            <div class="lg:col-span-4 bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/30 text-indigo-300 border border-indigo-400/40 flex items-center justify-center text-xl flex-shrink-0 font-black shadow-inner">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-bold text-sm text-white">{{ auth()->user()->name }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-500 text-white">
                            Level 1 Owner
                        </span>
                    </div>
                    <ul class="text-[11px] text-slate-300 space-y-0.5">
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-check text-brand-400 text-[10px]"></i> Has full access to the entire platform</li>
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-check text-brand-400 text-[10px]"></i> Manages all companies & tenants</li>
                        <li class="flex items-center gap-1.5"><i class="fa-solid fa-check text-brand-400 text-[10px]"></i> Not attached to any single company</li>
                    </ul>
                </div>
            </div>

            <!-- Right: Architectural Principle Summary -->
            <div class="lg:col-span-4 bg-slate-800/60 backdrop-blur-md rounded-2xl p-4 border border-slate-700/60">
                <div class="flex items-center gap-2 text-amber-400 text-xs font-bold uppercase tracking-wider mb-1.5">
                    <i class="fa-solid fa-shield-halved"></i> Multi-Tenancy Architecture
                </div>
                <p class="text-[11px] text-slate-300 leading-snug">
                    Each company operates with its own <span class="text-white font-semibold">Level 2 Super Admin</span> who possesses full administrative control strictly within their company boundary.
                </p>
                <div class="mt-3 flex items-center gap-2 text-[10px] font-mono text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-brand-400 animate-ping"></span>
                    <span>Multi-tenant query scoping active system-wide</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Platform-wide Consolidated Statistics KPIs -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <!-- Total Companies -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-black">
                <i class="fa-solid fa-building"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Total Companies</span>
                <span class="text-xl font-black text-slate-800">{{ $totalCompanies }}</span>
            </div>
        </div>

        <!-- Total System Users -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-black">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Tenant Users</span>
                <span class="text-xl font-black text-slate-800">{{ $totalUsers }}</span>
            </div>
        </div>

        <!-- Gross Platform Sales -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-lg font-black">
                <i class="fa-solid fa-cash-register"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Platform Sales</span>
                <span class="text-lg font-black text-brand-600">Rs. {{ number_format($totalPlatformSales, 0) }}</span>
            </div>
        </div>

        <!-- Gross Platform Purchases -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-black">
                <i class="fa-solid fa-cart-flatbed"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Total Purchases</span>
                <span class="text-lg font-black text-amber-600">Rs. {{ number_format($totalPlatformPurchases, 0) }}</span>
            </div>
        </div>

        <!-- Gross Sale Returns -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-3 col-span-2 sm:col-span-1">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg font-black">
                <i class="fa-solid fa-arrow-rotate-left"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Sale Returns</span>
                <span class="text-lg font-black text-purple-600">Rs. {{ number_format($totalPlatformReturns, 0) }}</span>
            </div>
        </div>
    </div>

    <!-- Create New Company & Super Admin Form Section -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
            <div>
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-brand-100 text-brand-700 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-plus"></i>
                    </span>
                    <span>Register New Company & Provision Super Admin</span>
                </h3>
                <p class="text-xs text-slate-500 mt-1">
                    Creates an isolated tenant company database container and automatically generates the Level 2 Super Admin credentials for the client.
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 w-fit">
                <i class="fa-solid fa-lock text-[10px] mr-1"></i> Owner Only Action
            </span>
        </div>

        <form action="{{ route('owner.companies.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ logoPreview: null }">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Company Name -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Company Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" required placeholder="e.g. ABC Traders LLC"
                           class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:outline-none transition">
                </div>

                <!-- Company Code -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Company Code (Optional)
                    </label>
                    <input type="text" name="company_code" value="{{ old('company_code') }}" placeholder="e.g. ABC-01 (Auto-generated if empty)"
                           class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:outline-none transition uppercase">
                </div>

                <!-- Currency -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Currency
                    </label>
                    <select name="currency" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
                        <option value="PKR" selected>PKR - Pakistani Rupee (Rs.)</option>
                        <option value="AED">AED - UAE Dirham</option>
                        <option value="USD">USD - US Dollar ($)</option>
                        <option value="SAR">SAR - Saudi Riyal</option>
                    </select>
                </div>

                <!-- Super Admin Name -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Super Admin Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="admin_name" value="{{ old('admin_name') }}" required placeholder="e.g. Muhammad Ali"
                           class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:outline-none transition">
                </div>

                <!-- Super Admin Email -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Super Admin Email (Login ID) <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" required placeholder="e.g. admin@abctraders.com"
                           class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:outline-none transition">
                </div>

                <!-- Super Admin Password -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Password <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" name="admin_password" required placeholder="Minimum 6 characters"
                           class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:outline-none transition">
                </div>

                <!-- Company Logo Upload -->
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Company Logo / Brand Mark (Optional)
                    </label>
                    <div class="flex items-center gap-4 p-3.5 border border-dashed border-slate-300 rounded-xl bg-slate-50/50 hover:bg-slate-50 transition">
                        <div class="w-12 h-12 rounded-lg border border-slate-200 bg-white flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" class="w-full h-full object-contain p-1" alt="Logo preview">
                            </template>
                            <template x-if="!logoPreview">
                                <i class="fa-solid fa-building text-slate-300 text-lg"></i>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0">
                            <input type="file" name="logo" id="owner_logo" accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                   @change="const file = $event.target.files[0]; if (file) { logoPreview = URL.createObjectURL(file); }"
                                   class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                            <p class="text-[11px] text-slate-400 mt-1">PNG, JPG, WEBP, or SVG (max 2MB). Shown in tenant's sidebar and receipts.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white text-sm font-bold rounded-xl shadow-lg shadow-brand-600/25 transition duration-150 flex items-center gap-2">
                    <i class="fa-solid fa-building-circle-check"></i>
                    <span>Create Company & Provision Super Admin</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Tenant Companies Grid Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-city text-indigo-600"></i>
                <span>Registered Tenant Companies ({{ $companies->count() }})</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Overview of all tenant companies, assigned Super Admins, and live inventory/sales statistics.
            </p>
        </div>
    </div>

    <!-- Company Cards Grid (Matching Multi-Tenant Architecture Style) -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @php
            $accentColors = [
                ['border' => 'border-blue-500', 'header' => 'bg-blue-500', 'badge' => 'bg-blue-50 text-blue-700 border-blue-200', 'icon' => 'text-blue-500'],
                ['border' => 'border-brand-500', 'header' => 'bg-brand-600', 'badge' => 'bg-brand-50 text-brand-700 border-brand-200', 'icon' => 'text-brand-600'],
                ['border' => 'border-amber-500', 'header' => 'bg-amber-500', 'badge' => 'bg-amber-50 text-amber-800 border-amber-200', 'icon' => 'text-amber-600'],
                ['border' => 'border-purple-500', 'header' => 'bg-purple-600', 'badge' => 'bg-purple-50 text-purple-700 border-purple-200', 'icon' => 'text-purple-600'],
            ];
        @endphp

        @forelse($companies as $index => $company)
            @php
                $color = $accentColors[$index % count($accentColors)];
                $superAdmin = $company->users->first();
            @endphp
            <div class="bg-white rounded-3xl border-2 {{ $color['border'] }} shadow-md hover:shadow-xl transition flex flex-col overflow-hidden group">
                
                <!-- Card Header (Company Identity) -->
                <div class="p-5 {{ $color['header'] }} text-white flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur-sm text-white flex items-center justify-center text-lg font-black flex-shrink-0 shadow-inner overflow-hidden p-1">
                            @if($company->logo)
                                <img src="{{ asset('storage/' . $company->logo) }}" class="w-full h-full object-contain" alt="{{ $company->name }}">
                            @else
                                <i class="fa-solid fa-building"></i>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-black text-base text-white truncate" title="{{ $company->name }}">
                                {{ $company->name }}
                            </h4>
                            <span class="text-[11px] text-white/80 font-mono block">Code: {{ $company->code }}</span>
                        </div>
                    </div>

                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-white text-slate-900 shadow-xs flex-shrink-0">
                        Level 2 Active
                    </span>
                </div>

                <!-- Super Admin Profile Block -->
                <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-black flex-shrink-0 text-xs">
                            <i class="fa-solid fa-user-gear"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="font-bold text-slate-800 block truncate">
                                {{ $superAdmin->name ?? 'Super Admin' }}
                            </span>
                            <span class="text-[11px] text-slate-400 font-mono block truncate">
                                {{ $superAdmin->email ?? ($company->email ?? 'No admin email') }}
                            </span>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase">Tenant Admin</span>
                </div>

                <!-- Company Statistics Breakdown -->
                <div class="p-5 flex-1 space-y-3">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Company Metrics & Stats</p>

                    <div class="grid grid-cols-2 gap-2.5 text-xs">
                        <!-- Total Users -->
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-users text-indigo-500 text-sm w-4 text-center"></i>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Total Users</span>
                                <span class="font-black text-slate-800">{{ $company->users_count ?? 0 }}</span>
                            </div>
                        </div>

                        <!-- Total Products -->
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-box-open text-brand-500 text-sm w-4 text-center"></i>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Total Products</span>
                                <span class="font-black text-slate-800">{{ $company->products_count ?? 0 }}</span>
                            </div>
                        </div>

                        <!-- Categories -->
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-tags text-amber-500 text-sm w-4 text-center"></i>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Categories</span>
                                <span class="font-black text-slate-800">{{ $company->categories_count ?? 0 }}</span>
                            </div>
                        </div>

                        <!-- Currency / Code -->
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-2">
                            <i class="fa-solid fa-coins text-purple-500 text-sm w-4 text-center"></i>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Currency</span>
                                <span class="font-black text-slate-800">{{ $company->currency ?? 'PKR' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summaries (Sales, Purchases, Returns) -->
                    <div class="pt-2 border-t border-slate-100 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 flex items-center gap-1.5">
                                <i class="fa-solid fa-cash-register text-brand-500 text-xs"></i> Total Sales:
                            </span>
                            <span class="font-black text-brand-600">
                                Rs. {{ number_format($company->sales_sum_total_amount ?? 0, 2) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 flex items-center gap-1.5">
                                <i class="fa-solid fa-bag-shopping text-amber-500 text-xs"></i> Total Purchases:
                            </span>
                            <span class="font-black text-amber-600">
                                Rs. {{ number_format($company->purchases_sum_total_amount ?? 0, 2) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 flex items-center gap-1.5">
                                <i class="fa-solid fa-arrow-rotate-left text-purple-500 text-xs"></i> Sale Returns:
                            </span>
                            <span class="font-black text-purple-600">
                                Rs. {{ number_format($company->sale_returns_sum_total_amount ?? 0, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card Footer Actions -->
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
                    <span class="text-[11px] text-slate-400">
                        <i class="fa-regular fa-calendar mr-1"></i> Created {{ $company->created_at->format('d M Y') }}
                    </span>

                    <form action="{{ route('owner.companies.destroy', $company) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete {{ $company->name }} and all its users?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 text-xs font-bold text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition flex items-center gap-1">
                            <i class="fa-solid fa-trash-can"></i>
                            <span>Delete</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-dashed border-slate-300 p-8">
                <div class="w-16 h-16 rounded-3xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fa-solid fa-building-circle-exclamation"></i>
                </div>
                <h4 class="text-base font-bold text-slate-800">No Tenant Companies Registered Yet</h4>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                    Use the form above to register your first client company and automatically provision its Level 2 Super Admin user.
                </p>
            </div>
        @endforelse
    </div>
</div>
@endsection

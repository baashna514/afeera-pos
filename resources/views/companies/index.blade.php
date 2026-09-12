@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Companies & Tenants</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage multi-tenant business entities, branch organizations, and data isolation.</p>
        </div>
        <div class="flex items-center gap-2 sm:gap-3">
            @if(auth()->user()?->hasPermission('users.view'))
                <a href="{{ route('users.index') }}" class="px-3.5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-xl transition flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-users text-xs text-slate-500"></i>
                    <span>Staff Users</span>
                </a>
            @endif
            @if(auth()->user()?->hasPermission('companies.create'))
                <a href="{{ route('companies.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-building-circle-arrow-right text-xs"></i>
                    <span>Register New Company</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Multi-Tenant Architecture Notice -->
    <div class="p-4 bg-blue-50 border border-blue-200/80 rounded-2xl flex items-start gap-3">
        <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 mt-0.5">
            <i class="fa-solid fa-shield-halved text-sm"></i>
        </div>
        <div>
            <h4 class="text-sm font-bold text-blue-900">Zero Data Leakage (Automated Company Scope)</h4>
            <p class="text-xs text-blue-700 mt-0.5">
                Every company operates in a strictly segregated environment. When staff users of a company log in, all inventory, sales, purchases, customers, and staff lists are automatically filtered by their company ID. Super Admin has central oversight across all companies.
            </p>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4 justify-between items-center">
        <form action="{{ route('companies.index') }}" method="GET" class="flex-1 flex items-center gap-2 w-full md:w-auto">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, code, email, or phone..." 
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
            </div>

            <select name="status" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition text-slate-600">
                <option value="">All Statuses</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('companies.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Companies Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 text-xs font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Company Details</th>
                        <th class="py-3 px-4">Contact Info</th>
                        <th class="py-3 px-4 text-center">Currency</th>
                        <th class="py-3 px-4 text-center">Tenancy Stats</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($companies as $company)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm shrink-0">
                                        <i class="fa-solid fa-building"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 flex items-center gap-2">
                                            <span>{{ $company->name }}</span>
                                            @if($company->code)
                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[11px] font-semibold rounded-md">
                                                    {{ $company->code }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($company->address)
                                            <div class="text-xs text-slate-400 mt-0.5 truncate max-w-xs" title="{{ $company->address }}">
                                                <i class="fa-solid fa-location-dot text-[10px] mr-1 text-slate-300"></i>{{ $company->address }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-xs text-slate-600 space-y-0.5">
                                    @if($company->email)
                                        <div><i class="fa-regular fa-envelope text-slate-400 mr-1.5"></i>{{ $company->email }}</div>
                                    @endif
                                    @if($company->phone)
                                        <div><i class="fa-solid fa-phone text-slate-400 mr-1.5"></i>{{ $company->phone }}</div>
                                    @endif
                                    @if(! $company->email && ! $company->phone)
                                        <span class="text-slate-400 italic">No contact provided</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg font-bold text-xs">
                                    {{ $company->currency }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="inline-flex items-center gap-3 text-xs">
                                    <span class="px-2 py-1 bg-purple-50 text-purple-700 rounded-lg font-semibold" title="Staff Users">
                                        <i class="fa-solid fa-users text-[10px] mr-1"></i>{{ $company->users_count }}
                                    </span>
                                    <span class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded-lg font-semibold" title="Products">
                                        <i class="fa-solid fa-box text-[10px] mr-1"></i>{{ $company->products_count }}
                                    </span>
                                    <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded-lg font-semibold" title="Sales Invoices">
                                        <i class="fa-solid fa-file-invoice text-[10px] mr-1"></i>{{ $company->sales_count }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($company->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if(auth()->user()?->hasPermission('companies.edit'))
                                        <a href="{{ route('companies.edit', $company) }}" class="p-2 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition" title="Edit Company">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()?->hasPermission('companies.delete'))
                                        <form action="{{ route('companies.destroy', $company) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove {{ $company->name }}?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Delete Company">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                <i class="fa-regular fa-building text-3xl mb-2 block"></i>
                                No companies found.
                                @if(auth()->user()?->hasPermission('companies.create'))
                                    <div class="mt-2">
                                        <a href="{{ route('companies.create') }}" class="text-xs font-bold text-emerald-600 hover:underline">
                                            + Register First Company
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($companies->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $companies->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Edit Company: {{ $company->name }}</h2>
            <p class="text-xs text-slate-500 mt-0.5">Update organizational details, contact information, and operating status.</p>
        </div>
        <a href="{{ route('companies.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition flex items-center gap-1.5 shadow-2xs">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Companies</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form action="{{ route('companies.update', $company) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Company Name -->
                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Company Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $company->name) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition @error('name') border-rose-400 bg-rose-50/30 @enderror"
                           placeholder="e.g. Apex Retailers LLC">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Company Code / Identifier -->
                <div>
                    <label for="code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Company Code / Short Prefix
                    </label>
                    <input type="text" name="code" id="code" value="{{ old('code', $company->code) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition @error('code') border-rose-400 bg-rose-50/30 @enderror"
                           placeholder="e.g. APEX-01">
                    @error('code')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Default Currency -->
                <div>
                    <label for="currency" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Default Currency
                    </label>
                    <input type="text" name="currency" id="currency" value="{{ old('currency', $company->currency) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition @error('currency') border-rose-400 bg-rose-50/30 @enderror"
                           placeholder="PKR, USD, AED, SAR">
                    @error('currency')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Official Email -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Official Email
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email', $company->email) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition @error('email') border-rose-400 bg-rose-50/30 @enderror"
                           placeholder="contact@company.com">
                    @error('email')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Phone / Mobile
                    </label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $company->phone) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition @error('phone') border-rose-400 bg-rose-50/30 @enderror"
                           placeholder="+92 300 1234567">
                    @error('phone')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Physical Address -->
                <div class="sm:col-span-2">
                    <label for="address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Registered Address
                    </label>
                    <textarea name="address" id="address" rows="3"
                              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition @error('address') border-rose-400 bg-rose-50/30 @enderror"
                              placeholder="Street address, city, region">{{ old('address', $company->address) }}</textarea>
                    @error('address')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Checkbox -->
                <div class="sm:col-span-2 pt-2">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $company->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        <span class="text-sm font-semibold text-slate-700">Company is Active</span>
                    </label>
                    <p class="text-xs text-slate-400 ml-6.5 mt-0.5">Inactive companies prevent staff from accessing the POS or logging transactions.</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('companies.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    <span>Update Company</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
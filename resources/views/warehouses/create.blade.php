@extends('layouts.app')

@section('title', 'Add New Warehouse')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-warehouse text-emerald-600"></i> Add New Warehouse
            </h1>
            <p class="text-xs text-slate-500 font-medium">Create a new storage location or distribution hub.</p>
        </div>
        <a href="{{ route('warehouses.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <form action="{{ route('warehouses.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Warehouse Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Central Warehouse A"
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Code / Identifier</label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g. WH-MAIN"
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Contact Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. +92 300 1234567"
                       class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition font-medium">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Address / Location</label>
                <textarea name="address" rows="3" placeholder="Full street address or location details..."
                          class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition font-medium">{{ old('address') }}</textarea>
            </div>

            <div class="pt-2 border-t border-slate-100 flex items-center gap-6">
                <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                    <span>Active Location</span>
                </label>

                <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                    <span>Set as Primary Default Warehouse</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('warehouses.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-xs transition">Save Warehouse</button>
            </div>
        </form>
    </div>
</div>
@endsection

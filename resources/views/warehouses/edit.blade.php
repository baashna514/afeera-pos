@extends('layouts.app')

@section('title', 'Edit Warehouse')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-brand-600"></i> Edit Warehouse
            </h1>
            <p class="text-xs text-slate-500 font-medium">Update warehouse details and location preferences.</p>
        </div>
        <a href="{{ route('warehouses.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <form action="{{ route('warehouses.update', $warehouse) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Warehouse Name *</label>
                    <input type="text" name="name" value="{{ old('name', $warehouse->name) }}" required
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none transition font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Code / Identifier</label>
                    <input type="text" name="code" value="{{ old('code', $warehouse->code) }}"
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none transition font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Contact Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $warehouse->phone) }}"
                       class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none transition font-medium">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Address / Location</label>
                <textarea name="address" rows="3"
                          class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none transition font-medium">{{ old('address', $warehouse->address) }}</textarea>
            </div>

            <div class="pt-2 border-t border-slate-100 flex items-center gap-6">
                <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ $warehouse->is_active ? 'checked' : '' }} class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-slate-300">
                    <span>Active Location</span>
                </label>

                <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1" {{ $warehouse->is_default ? 'checked' : '' }} class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-slate-300">
                    <span>Set as Primary Default Warehouse</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('warehouses.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs shadow-xs transition">Update Warehouse</button>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Warehouses & Locations')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-warehouse text-brand-600"></i> Warehouses & Locations
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Manage storage locations, multi-warehouse stock, and fulfillment hubs.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('stock-transfers.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-2">
                <i class="fa-solid fa-right-left text-brand-600"></i>
                <span>Stock Transfers</span>
            </a>
            <a href="{{ route('warehouses.create') }}" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>Add Warehouse</span>
            </a>
        </div>
    </div>

    <!-- Warehouses Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-900 text-slate-300 font-bold uppercase tracking-wider text-[11px]">
                        <th class="p-4">Code & Name</th>
                        <th class="p-4">Contact Info</th>
                        <th class="p-4">Address</th>
                        <th class="p-4 text-center">Stock Items</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($warehouses as $wh)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="p-4 font-medium text-slate-900">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-700 font-mono text-[10px] rounded-md font-bold border border-slate-200">
                                        {{ $wh->code ?? 'WH-'.$wh->id }}
                                    </span>
                                    <span class="font-bold text-slate-800 text-sm">{{ $wh->name }}</span>
                                    @if($wh->is_default)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-brand-100 text-brand-800 border border-brand-200">
                                            Default
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 text-slate-600">
                                <i class="fa-solid fa-phone text-slate-400 mr-1.5"></i>{{ $wh->phone ?? 'N/A' }}
                            </td>
                            <td class="p-4 text-slate-600 max-w-xs truncate">
                                {{ $wh->address ?? 'No address set' }}
                            </td>
                            <td class="p-4 text-center font-bold font-mono text-slate-800">
                                {{ number_format($wh->total_quantity ?? 0) }} units
                            </td>
                            <td class="p-4 text-center">
                                @if($wh->is_active)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-brand-100 text-brand-800 border border-brand-200">Active</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">Inactive</span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('warehouses.edit', $wh) }}" class="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    @if(!$wh->is_default)
                                        <form action="{{ route('warehouses.destroy', $wh) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this warehouse?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Delete">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                <i class="fa-solid fa-warehouse text-3xl mb-2 text-slate-300"></i>
                                <p class="font-medium">No warehouses registered yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($warehouses->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $warehouses->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

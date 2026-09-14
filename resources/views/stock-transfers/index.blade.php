@extends('layouts.app')

@section('title', 'Internal Stock Transfers')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-right-left text-emerald-600"></i> Internal Stock Transfers
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Transfer inventory seamlessly between warehouses & track movement audit history.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('warehouses.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-2">
                <i class="fa-solid fa-warehouse text-emerald-600"></i>
                <span>Warehouses</span>
            </a>
            <a href="{{ route('stock-transfers.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-2">
                <i class="fa-solid fa-arrow-right-arrow-left"></i>
                <span>New Stock Transfer</span>
            </a>
        </div>
    </div>

    <!-- Transfers Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-900 text-slate-300 font-bold uppercase tracking-wider text-[11px]">
                        <th class="p-4">Reference No</th>
                        <th class="p-4">Date</th>
                        <th class="p-4">From (Source)</th>
                        <th class="p-4">To (Destination)</th>
                        <th class="p-4 text-center">Items Transferred</th>
                        <th class="p-4">Created By</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transfers as $trf)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="p-4 font-mono font-bold text-emerald-700">
                                {{ $trf->transfer_number }}
                            </td>
                            <td class="p-4 text-slate-600 font-medium">
                                {{ $trf->transfer_date ? $trf->transfer_date->format('d M Y') : $trf->created_at->format('d M Y') }}
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fa-solid fa-building-circle-arrow-right mr-1"></i>{{ $trf->fromWarehouse->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fa-solid fa-building-circle-check mr-1"></i>{{ $trf->toWarehouse->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="p-4 text-center font-bold font-mono text-slate-800">
                                {{ $trf->items->sum('quantity') }} items ({{ $trf->items->count() }} products)
                            </td>
                            <td class="p-4 text-slate-600 font-medium">
                                {{ $trf->creator->name ?? 'System' }}
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('stock-transfers.show', $trf) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition inline-flex items-center gap-1">
                                    <i class="fa-solid fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">
                                <i class="fa-solid fa-right-left text-3xl mb-2 text-slate-300"></i>
                                <p class="font-medium">No stock transfers recorded yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transfers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

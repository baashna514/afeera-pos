@extends('layouts.app')

@section('title', 'Transfer Details - '.$stockTransfer->transfer_number)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-receipt text-emerald-600"></i> Stock Transfer #{{ $stockTransfer->transfer_number }}
            </h1>
            <p class="text-xs text-slate-500 font-medium">Processed on {{ $stockTransfer->transfer_date ? $stockTransfer->transfer_date->format('d M Y') : $stockTransfer->created_at->format('d M Y') }}</p>
        </div>
        <a href="{{ route('stock-transfers.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to Transfers
        </a>
    </div>

    <!-- Summary Box -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-4 border-b border-slate-100">
            <div class="p-4 bg-amber-50/50 rounded-xl border border-amber-100">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-700 block">From (Source Warehouse)</span>
                <span class="text-base font-black text-amber-900 mt-1 block">{{ $stockTransfer->fromWarehouse->name ?? 'N/A' }}</span>
                <span class="text-xs text-amber-700 font-mono">{{ $stockTransfer->fromWarehouse->code ?? '' }}</span>
            </div>

            <div class="p-4 bg-emerald-50/50 rounded-xl border border-emerald-100">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 block">To (Destination Warehouse)</span>
                <span class="text-base font-black text-emerald-900 mt-1 block">{{ $stockTransfer->toWarehouse->name ?? 'N/A' }}</span>
                <span class="text-xs text-emerald-700 font-mono">{{ $stockTransfer->toWarehouse->code ?? '' }}</span>
            </div>
        </div>

        @if($stockTransfer->notes)
            <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="font-bold text-slate-800">Notes:</span> {{ $stockTransfer->notes }}
            </div>
        @endif

        <!-- Transferred Products Table -->
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Transferred Products</h3>
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-slate-300 font-bold uppercase text-[10px]">
                        <th class="p-3">Product Name</th>
                        <th class="p-3">SKU / Code</th>
                        <th class="p-3 text-right">Quantity Transferred</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($stockTransfer->items as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 font-bold text-slate-800">{{ $item->product->name ?? 'Deleted Product' }}</td>
                            <td class="p-3 font-mono text-slate-500">{{ $item->product->code ?? '-' }}</td>
                            <td class="p-3 text-right font-mono font-black text-emerald-700 text-sm">
                                {{ number_format($item->quantity) }} {{ $item->product->unit->short_code ?? 'pcs' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

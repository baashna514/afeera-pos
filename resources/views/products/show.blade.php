@extends('layouts.app')

@section('title', 'Product Details - '.$product->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-3">
                <span class="px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    {{ $product->barcode ?? 'SKU-'.$product->id }}
                </span>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">{{ $product->name }}</h1>
            </div>
            <p class="text-xs text-slate-500 font-medium mt-1">Category: {{ $product->category->name ?? 'Uncategorized' }} • Base Unit: {{ $product->unit->name ?? 'pcs' }}</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('products.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> New Product
            </a>
            <a href="{{ route('products.edit', $product) }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-2">
                <i class="fa-solid fa-pen"></i> Edit Product
            </a>
        </div>
    </div>

    <!-- Product Details & Stock Tabs Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden" x-data="{ activeTab: 'stock' }">
        <!-- Tab Headers -->
        <div class="flex border-b border-slate-200 bg-slate-50/50 px-6 pt-4 gap-4">
            <button @click="activeTab = 'detail'"
                    :class="activeTab === 'detail' ? 'border-emerald-600 text-emerald-600 font-bold border-b-2' : 'text-slate-500 font-medium hover:text-slate-700'"
                    class="pb-3 text-xs uppercase tracking-wider transition">
                <i class="fa-solid fa-circle-info mr-1.5"></i> Detail
            </button>
            <button @click="activeTab = 'stock'"
                    :class="activeTab === 'stock' ? 'border-emerald-600 text-emerald-600 font-bold border-b-2' : 'text-slate-500 font-medium hover:text-slate-700'"
                    class="pb-3 text-xs uppercase tracking-wider transition">
                <i class="fa-solid fa-boxes-stacked mr-1.5"></i> Stock Overview
            </button>
        </div>

        <!-- Tab 1: Product Detail -->
        <div x-show="activeTab === 'detail'" class="p-6 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 space-y-1">
                    <span class="text-[10px] font-bold uppercase text-slate-400">Purchase / Cost Price</span>
                    <p class="text-lg font-black text-slate-800">Rs. {{ number_format($product->purchase_price, 2) }}</p>
                </div>
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 space-y-1">
                    <span class="text-[10px] font-bold uppercase text-slate-400">Selling / Retail Price</span>
                    <p class="text-lg font-black text-emerald-600">Rs. {{ number_format($product->selling_price, 2) }}</p>
                </div>
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 space-y-1">
                    <span class="text-[10px] font-bold uppercase text-slate-400">Total System Stock</span>
                    <p class="text-lg font-black text-indigo-900">{{ number_format($product->quantity) }} {{ $product->unit->short_code ?? 'pcs' }}</p>
                </div>
            </div>

            @if($product->description)
                <div class="space-y-1">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Description / Specifications</h4>
                    <p class="text-xs text-slate-600 bg-slate-50 p-4 rounded-xl border border-slate-100">{{ $product->description }}</p>
                </div>
            @endif

            <!-- Secondary Packaging Units -->
            @if($product->secondaryUnits->count() > 0)
                <div class="space-y-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Packaging & Secondary Units</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse border border-slate-200">
                            <thead class="bg-slate-100 text-slate-700 font-bold">
                                <tr>
                                    <th class="p-3 border-b">Unit Name</th>
                                    <th class="p-3 border-b">Conversion Rate</th>
                                    <th class="p-3 border-b">Calculated Selling Price</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($product->secondaryUnits as $su)
                                    <tr>
                                        <td class="p-3 font-bold text-slate-800">{{ $su->unit->name ?? 'Unit' }} ({{ $su->unit->short_code ?? '' }})</td>
                                        <td class="p-3 font-mono">1 {{ $su->unit->short_code ?? 'Unit' }} = {{ $su->conversion_rate }} {{ $product->unit->short_code ?? 'base units' }}</td>
                                        <td class="p-3 font-mono font-bold text-emerald-600">Rs. {{ number_format($product->selling_price * $su->conversion_rate, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Tab 2: Stock Overview by Warehouse -->
        <div x-show="activeTab === 'stock'" class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-900 text-slate-300 font-bold uppercase tracking-wider text-[11px]">
                            <th class="p-4 w-16">SR#</th>
                            <th class="p-4">Warehouse</th>
                            <th class="p-4">Batch No.</th>
                            <th class="p-4">Expiry Date</th>
                            <th class="p-4 text-right">Quantity Breakdown</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($warehouseStocks as $index => $ws)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="p-4 font-mono font-bold text-slate-500">{{ $index + 1 }}</td>
                                <td class="p-4 font-bold text-slate-800">
                                    <span class="text-emerald-700">{{ $ws->warehouse->name ?? 'Default Warehouse' }}</span>
                                    @if($ws->warehouse?->is_default)
                                        <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">Default</span>
                                    @endif
                                </td>
                                <td class="p-4 font-mono text-slate-500">-</td>
                                <td class="p-4 font-mono text-slate-500">-</td>
                                <td class="p-4 text-right">
                                    <div class="inline-block bg-slate-50 border border-slate-200 rounded-xl p-3 min-w-[200px] text-right space-y-1">
                                        <div class="flex items-center justify-between text-[11px]">
                                            <span class="font-bold text-slate-500">Unit</span>
                                            <span class="font-bold text-slate-500">Qty</span>
                                        </div>
                                        <div class="flex items-center justify-between font-mono pt-1 border-t border-slate-200 text-xs">
                                            <span class="font-bold text-slate-700">{{ $product->unit->name ?? 'Default Unit' }}</span>
                                            <span class="font-black text-slate-900">{{ number_format($ws->quantity, 2) }}</span>
                                        </div>

                                        @foreach($product->secondaryUnits as $su)
                                            @php
                                                $convertedQty = $su->conversion_rate > 0 ? $ws->quantity / $su->conversion_rate : 0;
                                            @endphp
                                            <div class="flex items-center justify-between font-mono text-[11px] text-slate-600">
                                                <span>{{ $su->unit->name ?? 'Packaging' }}</span>
                                                <span class="font-bold text-emerald-700">{{ number_format($convertedQty, 2) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">
                                    <i class="fa-solid fa-boxes-stacked text-3xl mb-2 text-slate-300"></i>
                                    <p class="font-medium">No stock allocated to any warehouse yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Alpine.js script for tabs -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection

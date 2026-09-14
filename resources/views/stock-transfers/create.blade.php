@extends('layouts.app')

@section('title', 'New Internal Stock Transfer')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-arrow-right-arrow-left text-emerald-600"></i> New Internal Stock Transfer
            </h1>
            <p class="text-xs text-slate-500 font-medium">Transfer inventory from one warehouse location to another.</p>
        </div>
        <a href="{{ route('stock-transfers.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to Transfers
        </a>
    </div>

    <form action="{{ route('stock-transfers.store') }}" method="POST" id="transferForm" class="space-y-6">
        @csrf

        <!-- Warehouse Selection Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">1. Select Locations & Date</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">From Warehouse (Source) *</label>
                    <select name="from_warehouse_id" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-medium">
                        <option value="">-- Select Source Warehouse --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('from_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }} ({{ $wh->code ?? 'WH-'.$wh->id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">To Warehouse (Destination) *</label>
                    <select name="to_warehouse_id" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-medium">
                        <option value="">-- Select Target Warehouse --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('to_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }} ({{ $wh->code ?? 'WH-'.$wh->id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Transfer Date *</label>
                    <input type="date" name="transfer_date" value="{{ old('transfer_date', date('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Transfer Notes (Optional)</label>
                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="e.g. Weekly stock rebalancing for retail store"
                       class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
        </div>

        <!-- Items Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">2. Products & Quantities</h2>
                <button type="button" id="addItemBtn" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold rounded-lg text-xs transition flex items-center gap-1">
                    <i class="fa-solid fa-plus"></i> Add Product
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="itemsTable">
                    <thead>
                        <tr class="text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100 pb-2 text-[10px]">
                            <th class="p-2 w-7/12">Select Product</th>
                            <th class="p-2 w-4/12">Transfer Quantity</th>
                            <th class="p-2 w-1/12 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="itemsTbody">
                        <tr class="item-row">
                            <td class="p-2">
                                <select name="items[0][product_id]" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-medium">
                                    <option value="">-- Choose Product --</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">
                                            {{ $p->name }} (Available System Total: {{ $p->quantity }} {{ $p->unit->short_code ?? 'pcs' }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="p-2">
                                <input type="number" name="items[0][quantity]" min="1" value="1" required
                                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-bold text-slate-800">
                            </td>
                            <td class="p-2 text-center">
                                <button type="button" class="remove-row-btn p-2 text-slate-300 hover:text-rose-600 transition">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('stock-transfers.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-xs transition flex items-center gap-2">
                <i class="fa-solid fa-check"></i>
                <span>Confirm & Process Transfer</span>
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let rowIdx = 1;
        const tbody = document.getElementById('itemsTbody');

        document.getElementById('addItemBtn').addEventListener('click', function () {
            const firstRow = tbody.querySelector('.item-row');
            const newRow = firstRow.cloneNode(true);

            newRow.querySelectorAll('select, input').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/\[\d+\]/, '[' + rowIdx + ']'));
                }
                if (input.tagName === 'INPUT') {
                    input.value = 1;
                } else if (input.tagName === 'SELECT') {
                    input.selectedIndex = 0;
                }
            });

            tbody.appendChild(newRow);
            rowIdx++;
        });

        tbody.addEventListener('click', function (e) {
            if (e.target.closest('.remove-row-btn')) {
                if (tbody.querySelectorAll('.item-row').length > 1) {
                    e.target.closest('.item-row').remove();
                } else {
                    alert('At least one product is required for transfer.');
                }
            }
        });
    });
</script>
@endsection

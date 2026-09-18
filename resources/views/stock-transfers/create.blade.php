@extends('layouts.app')

@section('title', 'New Internal Stock Transfer')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-arrow-right-arrow-left text-brand-600"></i> New Internal Stock Transfer
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
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">From Warehouse (Source) *</label>
                        <button type="button" onclick="openQuickWarehouseModal()" class="text-[11px] font-bold text-brand-600 hover:text-brand-700 flex items-center gap-1">
                            <i class="fa-solid fa-plus-circle"></i> New Warehouse
                        </button>
                    </div>
                    <select name="from_warehouse_id" id="from_warehouse_id" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
                        <option value="">-- Select Source Warehouse --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('from_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }} ({{ $wh->code ?? 'WH-'.$wh->id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">To Warehouse (Destination) *</label>
                        <button type="button" onclick="openQuickWarehouseModal()" class="text-[11px] font-bold text-brand-600 hover:text-brand-700 flex items-center gap-1">
                            <i class="fa-solid fa-plus-circle"></i> New Warehouse
                        </button>
                    </div>
                    <select name="to_warehouse_id" id="to_warehouse_id" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
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
                           class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none font-bold">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Transfer Notes (Optional)</label>
                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="e.g. Weekly stock rebalancing for retail store"
                       class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

        <!-- Items Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">2. Products & Quantities</h2>
                <button type="button" id="addItemBtn" class="px-3 py-1.5 bg-brand-50 text-brand-700 hover:bg-brand-100 font-bold rounded-lg text-xs transition flex items-center gap-1">
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
                                <select name="items[0][product_id]" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 font-medium">
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
                                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 font-bold text-slate-800">
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
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs shadow-xs transition flex items-center gap-2">
                <i class="fa-solid fa-check"></i>
                <span>Confirm & Process Transfer</span>
            </button>
        </div>
    </form>
</div>

<!-- Quick Add Warehouse Modal -->
<div id="quickWarehouseModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-warehouse text-brand-600"></i> Quick Add Warehouse
            </h3>
            <button type="button" onclick="closeQuickWarehouseModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form onsubmit="saveQuickWarehouse(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Warehouse Name <span class="text-rose-500">*</span></label>
                <input type="text" id="qc_wh_name" required placeholder="e.g. Main Godown, North Branch..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Code</label>
                    <input type="text" id="qc_wh_code" placeholder="e.g. WH-02" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Phone</label>
                    <input type="text" id="qc_wh_phone" placeholder="Contact number..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Address</label>
                <input type="text" id="qc_wh_address" placeholder="Physical location..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeQuickWarehouseModal()" class="px-3 py-1.5 text-xs text-slate-600 font-semibold hover:text-slate-900">Cancel</button>
                <button type="submit" id="qc_wh_submit" class="px-4 py-1.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-lg shadow-xs transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i> Save &amp; Select
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openQuickWarehouseModal() {
        const modal = document.getElementById('quickWarehouseModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('qc_wh_name').focus();
        }
    }

    function closeQuickWarehouseModal() {
        const modal = document.getElementById('quickWarehouseModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('qc_wh_name').value = '';
            document.getElementById('qc_wh_code').value = '';
            document.getElementById('qc_wh_phone').value = '';
            document.getElementById('qc_wh_address').value = '';
        }
    }

    async function saveQuickWarehouse(e) {
        e.preventDefault();
        const name = document.getElementById('qc_wh_name').value.trim();
        const code = document.getElementById('qc_wh_code').value.trim();
        const phone = document.getElementById('qc_wh_phone').value.trim();
        const address = document.getElementById('qc_wh_address').value.trim();
        if (!name) return;

        const btn = document.getElementById('qc_wh_submit');
        btn.disabled = true;

        try {
            const res = await fetch("{{ route('warehouses.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ name, code, phone, address }),
            });

            const data = await res.json();
            if (data.success && data.warehouse) {
                ['from_warehouse_id', 'to_warehouse_id'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        const optName = `${data.warehouse.name} (${data.warehouse.code || 'WH-' + data.warehouse.id})`;
                        const opt = new Option(optName, data.warehouse.id);
                        el.add(opt);
                    }
                });
                closeQuickWarehouseModal();
            } else {
                alert(data.message || 'Could not save warehouse.');
            }
        } catch (err) {
            console.error(err);
            alert('Error creating warehouse.');
        } finally {
            btn.disabled = false;
        }
    }

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

@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800 flex items-center gap-2.5">
                <i class="fa-solid fa-[file-invoice-dollar] text-emerald-600"></i>
                <span>Record Cash &amp; Payment Voucher</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Receive payments from customers or pay outstanding balances to suppliers/vendors.</p>
        </div>
        <a href="{{ route('vouchers.index') }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Vouchers</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('vouchers.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Voucher Type Selector -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Voucher Category / Transaction Type <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 gap-4">
                    <label id="lbl_receipt" class="p-4 rounded-xl border-2 border-emerald-500 bg-emerald-50/50 cursor-pointer flex items-center gap-3 transition">
                        <input type="radio" name="type" value="receipt" checked onchange="toggleVoucherParty('receipt')" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="block text-xs font-black text-emerald-900 uppercase">Cash Receipt</span>
                            <span class="text-[11px] text-emerald-700 font-medium">Receive Money from Customer (Khata Credit)</span>
                        </div>
                    </label>

                    <label id="lbl_payment" class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer flex items-center gap-3 transition hover:border-slate-300">
                        <input type="radio" name="type" value="payment" onchange="toggleVoucherParty('payment')" class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="block text-xs font-black text-slate-800 uppercase">Cash Payment</span>
                            <span class="text-[11px] text-slate-500 font-medium">Pay Money to Vendor (Khata Debit)</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Customer Dropdown (for Receipt) -->
            <div id="div_customer">
                <label for="customer_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Select Customer <span class="text-rose-500">*</span></label>
                <select name="customer_id" id="customer_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    <option value="">-- Choose Customer --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->phone ?? 'No phone' }})
                        </option>
                    @endforeach
                </select>
                @error('customer_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Vendor Dropdown (for Payment) -->
            <div id="div_vendor" class="hidden">
                <label for="vendor_id" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Select Vendor / Supplier <span class="text-rose-500">*</span></label>
                <select name="vendor_id" id="vendor_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    <option value="">-- Choose Vendor --</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" {{ old('vendor_id') == $v->id ? 'selected' : '' }}>
                            {{ $v->name }} ({{ $v->phone ?? 'No phone' }})
                        </option>
                    @endforeach
                </select>
                @error('vendor_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Voucher Date -->
                <div>
                    <label for="voucher_date" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Transaction Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="voucher_date" id="voucher_date" value="{{ old('voucher_date', date('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                </div>

                <!-- Amount -->
                <div>
                    <label for="amount" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Amount (Rs.) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="amount" value="{{ old('amount') }}" required placeholder="0.00"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-black focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    @error('amount') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Payment Method <span class="text-rose-500">*</span></label>
                    <select name="payment_method" id="payment_method" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                        <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                        <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="online" {{ old('payment_method') === 'online' ? 'selected' : '' }}>Online / Wallet</option>
                    </select>
                </div>

                <!-- Reference No -->
                <div>
                    <label for="reference_no" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Cheque / Reference #</label>
                    <input type="text" name="reference_no" id="reference_no" value="{{ old('reference_no') }}" placeholder="e.g. CHQ-987123"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Transaction Notes / Description</label>
                <textarea name="notes" id="notes" rows="3" placeholder="e.g. Partial cash payment received for invoice settling..."
                          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">{{ old('notes') }}</textarea>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('vouchers.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-500/25 transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    <span>Save &amp; Generate Voucher</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleVoucherParty(type) {
        const custDiv = document.getElementById('div_customer');
        const vendDiv = document.getElementById('div_vendor');
        const lblReceipt = document.getElementById('lbl_receipt');
        const lblPayment = document.getElementById('lbl_payment');

        if (type === 'receipt') {
            custDiv.classList.remove('hidden');
            vendDiv.classList.add('hidden');
            lblReceipt.className = 'p-4 rounded-xl border-2 border-emerald-500 bg-emerald-50/50 cursor-pointer flex items-center gap-3 transition';
            lblPayment.className = 'p-4 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer flex items-center gap-3 transition hover:border-slate-300';
        } else {
            custDiv.classList.add('hidden');
            vendDiv.classList.remove('hidden');
            lblPayment.className = 'p-4 rounded-xl border-2 border-blue-500 bg-blue-50/50 cursor-pointer flex items-center gap-3 transition';
            lblReceipt.className = 'p-4 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer flex items-center gap-3 transition hover:border-slate-300';
        }
    }
</script>
@endsection

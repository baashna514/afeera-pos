@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 flex items-center gap-2.5">
                <i class="fa-solid fa-receipt text-emerald-600"></i>
                <span>Cash &amp; Payment Vouchers (پیمنٹ واؤچرز)</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Record and manage cash receipts from customers and payments to suppliers/vendors.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('vouchers.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Create Payment Voucher</span>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
        <form action="{{ route('vouchers.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-center">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Voucher #, ref or note..."
                       class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <select name="type" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">All Voucher Types</option>
                    <option value="receipt" {{ request('type') === 'receipt' ? 'selected' : '' }}>Cash Receipt (Customer)</option>
                    <option value="payment" {{ request('type') === 'payment' ? 'selected' : '' }}>Cash Payment (Vendor)</option>
                </select>
            </div>

            <div>
                <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="From Date"
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-xs font-bold rounded-lg hover:bg-slate-700 transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'type', 'date_from', 'date_to']))
                    <a href="{{ route('vouchers.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Voucher #</th>
                        <th class="px-5 py-3.5">Date</th>
                        <th class="px-5 py-3.5">Type</th>
                        <th class="px-5 py-3.5">Customer / Vendor</th>
                        <th class="px-5 py-3.5">Method</th>
                        <th class="px-5 py-3.5 text-right">Amount</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($vouchers as $voucher)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5 font-bold text-slate-800">
                                <a href="{{ route('vouchers.show', $voucher) }}" class="text-emerald-700 hover:underline">
                                    {{ $voucher->voucher_number }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-600">
                                {{ $voucher->voucher_date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $voucher->type_badge_class }}">
                                    {{ $voucher->type_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-slate-700">
                                {{ $voucher->party_name }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 capitalize">
                                {{ str_replace('_', ' ', $voucher->payment_method) }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-black text-slate-900">
                                Rs. {{ number_format($voucher->amount, 2) }}
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <a href="{{ route('vouchers.show', $voucher) }}" class="p-1.5 text-slate-400 hover:text-emerald-600 transition" title="View / Print Voucher">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <form action="{{ route('vouchers.destroy', $voucher) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this voucher?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300"></i>
                                <p class="text-xs font-semibold">No payment vouchers found.</p>
                                <a href="{{ route('vouchers.create') }}" class="mt-2 inline-block px-3 py-1.5 bg-emerald-600 text-white font-bold text-xs rounded-lg">Create First Voucher</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vouchers->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $vouchers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

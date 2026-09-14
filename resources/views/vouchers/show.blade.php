@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <!-- Header Controls -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800">Voucher Details</h2>
            <p class="text-xs text-slate-500">Official cash &amp; payment transaction voucher receipt.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-2">
                <i class="fa-solid fa-print"></i>
                <span>Print Voucher</span>
            </button>
            <a href="{{ route('vouchers.index') }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>
    </div>

    <!-- Printable Voucher Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-md p-6 sm:p-8 space-y-6">
        <!-- Voucher Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-5">
            <div>
                <h3 class="text-xl font-black text-slate-900 tracking-tight">SmartPOS</h3>
                <p class="text-[11px] text-slate-400 font-medium">Financial Transaction Receipt</p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider {{ $voucher->type_badge_class }}">
                    {{ $voucher->type_label }}
                </span>
                <p class="text-xs font-mono font-bold text-slate-700 mt-2">{{ $voucher->voucher_number }}</p>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-2 gap-4 bg-slate-50/70 p-4 rounded-xl border border-slate-100 text-xs">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Date</span>
                <span class="font-bold text-slate-800">{{ $voucher->voucher_date->format('d M Y') }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Payment Method</span>
                <span class="font-bold text-slate-800 capitalize">{{ str_replace('_', ' ', $voucher->payment_method) }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Party / Account</span>
                <span class="font-bold text-emerald-800 text-sm">{{ $voucher->party_name }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Reference / Cheque #</span>
                <span class="font-bold text-slate-800">{{ $voucher->reference_no ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Amount Box -->
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-5 text-center">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 block">Total Amount {{ $voucher->type === 'receipt' ? 'Received' : 'Paid' }}</span>
            <span class="text-3xl font-black text-emerald-900 mt-1 block">Rs. {{ number_format($voucher->amount, 2) }}</span>
        </div>

        @if($voucher->notes)
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Notes / Description</span>
                <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded-lg border border-slate-100 font-medium">{{ $voucher->notes }}</p>
            </div>
        @endif

        <!-- Footer Signatures -->
        <div class="pt-8 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
            <div>
                <span class="block text-slate-600 font-semibold">Recorded By: {{ $voucher->creator ? $voucher->creator->name : 'System' }}</span>
                <span>{{ $voucher->created_at->format('d M Y, h:i A') }}</span>
            </div>
            <div class="text-right pt-6 border-t border-slate-300 w-32">
                <span class="block text-[10px] text-slate-400 uppercase font-bold">Authorized Sign</span>
            </div>
        </div>
    </div>
</div>
@endsection

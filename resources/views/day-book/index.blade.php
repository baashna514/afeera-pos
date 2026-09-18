@extends('layouts.app', ['title' => 'Day Book / Cash Book'])

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-book text-brand-600"></i>
                <span>Day Book / Cash Book (کیش بک / ڈے بک)</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Daily cash register balance, counter drawer cash tracking, and cash inflow/outflow audit.</p>
        </div>
        <div class="flex items-center gap-2 no-print">
            <button onclick="document.getElementById('openingBalanceModal').classList.remove('hidden')" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                <i class="fa-solid fa-wallet"></i>
                <span>Set Opening Balance</span>
            </button>
            <button onclick="window.print()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-print"></i>
                <span>Print Day Book</span>
            </button>
        </div>
    </div>

    <!-- Date Navigation Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 no-print">
        <form action="{{ route('day-book.index') }}" method="GET" class="flex items-center gap-2">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Select Date:</label>
            <input type="date" name="date" value="{{ $date }}" class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none font-bold text-slate-800">
            <button type="submit" class="px-4 py-1.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl transition">
                Load Day Book
            </button>
        </form>

        <div class="flex items-center gap-2">
            @php
                $yesterdayDate = Carbon\Carbon::parse($date)->subDay()->toDateString();
                $tomorrowDate = Carbon\Carbon::parse($date)->addDay()->toDateString();
            @endphp
            <a href="{{ route('day-book.index', ['date' => $yesterdayDate]) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1">
                <i class="fa-solid fa-chevron-left"></i> Previous Day
            </a>
            <a href="{{ route('day-book.index', ['date' => date('Y-m-d')]) }}" class="px-3 py-1.5 bg-brand-50 text-brand-700 border border-brand-200 text-xs font-bold rounded-xl transition">
                Today
            </a>
            <a href="{{ route('day-book.index', ['date' => $tomorrowDate]) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1">
                Next Day <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Opening Cash Balance -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Opening Cash Balance</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-black">
                    <i class="fa-solid fa-vault"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-black text-indigo-900">Rs. {{ number_format($openingBalance, 2) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Initial counter cash at shift start</p>
            </div>
        </div>

        <!-- Total Cash In -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Cash In (+)</span>
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-lg font-black">
                    <i class="fa-solid fa-circle-arrow-down"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-black text-brand-600">Rs. {{ number_format($totalCashIn, 2) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Sales: Rs. {{ number_format($totalSalesCash, 0) }} | Receipts: Rs. {{ number_format($totalReceiptsCash, 0) }}</p>
            </div>
        </div>

        <!-- Total Cash Out -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Cash Out (-)</span>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-black">
                    <i class="fa-solid fa-circle-arrow-up"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-black text-rose-600">Rs. {{ number_format($totalCashOut, 2) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Exp: Rs. {{ number_format($totalExpensesCash, 0) }} | Pay: Rs. {{ number_format($totalPaymentsCash, 0) }}</p>
            </div>
        </div>

        <!-- Expected Net Cash in Drawer -->
        <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white p-6 rounded-2xl shadow-md border border-indigo-900/50">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-300">Expected Net Cash Drawer</span>
                <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-xs text-brand-400 flex items-center justify-center text-lg font-black">
                    <i class="fa-solid fa-cash-register"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-3xl font-black text-brand-400">Rs. {{ number_format($expectedNetCashInHand, 2) }}</p>
                <p class="text-[11px] text-slate-300 mt-1">Expected drawer cash at shift end</p>
            </div>
        </div>
    </div>

    <!-- Detailed Cash Breakdown Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs text-center">
            <div class="p-2.5 bg-brand-50 rounded-xl border border-brand-100">
                <span class="text-[10px] text-brand-700 font-bold uppercase block">Cash Sales</span>
                <span class="font-black text-brand-900 text-sm">Rs. {{ number_format($totalSalesCash, 2) }}</span>
            </div>
            <div class="p-2.5 bg-blue-50 rounded-xl border border-blue-100">
                <span class="text-[10px] text-blue-700 font-bold uppercase block">Receipt Vouchers</span>
                <span class="font-black text-blue-900 text-sm">Rs. {{ number_format($totalReceiptsCash, 2) }}</span>
            </div>
            <div class="p-2.5 bg-purple-50 rounded-xl border border-purple-100">
                <span class="text-[10px] text-purple-700 font-bold uppercase block">Cash Purchases</span>
                <span class="font-black text-purple-900 text-sm">Rs. {{ number_format($totalPurchasesCash, 2) }}</span>
            </div>
            <div class="p-2.5 bg-indigo-50 rounded-xl border border-indigo-100">
                <span class="text-[10px] text-indigo-700 font-bold uppercase block">Payment Vouchers</span>
                <span class="font-black text-indigo-900 text-sm">Rs. {{ number_format($totalPaymentsCash, 2) }}</span>
            </div>
            <div class="p-2.5 bg-amber-50 rounded-xl border border-amber-100 col-span-2 sm:col-span-1">
                <span class="text-[10px] text-amber-700 font-bold uppercase block">Cash Expenses</span>
                <span class="font-black text-amber-900 text-sm">Rs. {{ number_format($totalExpensesCash, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Daily Cash Flow Transactions Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-list-check text-brand-600"></i>
                <h3 class="font-bold text-slate-800 text-sm">Daily Cash Register Ledger ({{ Carbon\Carbon::parse($date)->format('d M Y') }})</h3>
            </div>
            <span class="text-xs text-slate-500 font-medium">{{ count($transactions) }} transaction(s) recorded</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase tracking-wider text-[11px] font-bold">
                        <th class="p-4">Time</th>
                        <th class="p-4">Transaction Type</th>
                        <th class="p-4">Reference</th>
                        <th class="p-4">Party / Details</th>
                        <th class="p-4 text-right">Cash In (+)</th>
                        <th class="p-4 text-right">Cash Out (-)</th>
                        <th class="p-4 text-right">Drawer Cash Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <!-- Opening Balance Row -->
                    <tr class="bg-indigo-50/50 font-bold text-slate-800">
                        <td class="p-4 font-mono text-slate-500">00:00 AM</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-xs bg-indigo-100 text-indigo-800 border border-indigo-200">
                                Opening Balance
                            </span>
                        </td>
                        <td class="p-4 font-mono text-slate-500">-</td>
                        <td class="p-4 text-slate-600">Initial counter cash at shift start</td>
                        <td class="p-4 text-right text-indigo-700">Rs. {{ number_format($openingBalance, 2) }}</td>
                        <td class="p-4 text-right text-slate-400">Rs. 0.00</td>
                        <td class="p-4 text-right font-black text-indigo-900">Rs. {{ number_format($openingBalance, 2) }}</td>
                    </tr>

                    @forelse($transactions as $index => $row)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="p-4 font-mono text-slate-500">{{ $row['time']->format('h:i A') }}</td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $row['badge_class'] }}">
                                    {{ $row['type'] }}
                                </span>
                            </td>
                            <td class="p-4 font-mono text-slate-700 font-bold">
                                <a href="{{ $row['url'] }}" class="text-brand-600 hover:underline">
                                    {{ $row['reference'] }}
                                </a>
                            </td>
                            <td class="p-4 text-slate-700 font-medium">
                                {{ $row['party'] }}
                                <span class="block text-[10px] text-slate-400 font-normal">{{ $row['description'] }}</span>
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-brand-600">
                                {{ $row['cash_in'] > 0 ? 'Rs. '.number_format($row['cash_in'], 2) : '-' }}
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-rose-600">
                                {{ $row['cash_out'] > 0 ? 'Rs. '.number_format($row['cash_out'], 2) : '-' }}
                            </td>
                            <td class="p-4 text-right font-black text-slate-900 font-mono">
                                Rs. {{ number_format($row['running_balance'], 2) }}
                            </td>
                        </tr>
                    @empty
                        @if($openingBalance == 0)
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400">
                                    <i class="fa-solid fa-receipt text-2xl mb-2 block"></i>
                                    No cash transactions recorded for {{ Carbon\Carbon::parse($date)->format('d M Y') }}.
                                </td>
                            </tr>
                        @endif
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-900 text-white font-bold text-xs">
                    <tr>
                        <td colspan="4" class="p-4 text-right uppercase tracking-wider text-[11px] text-slate-300">Final Day Book Cash Totals:</td>
                        <td class="p-4 text-right text-brand-400 font-black">Rs. {{ number_format($totalCashIn, 2) }}</td>
                        <td class="p-4 text-right text-rose-400 font-black">Rs. {{ number_format($totalCashOut, 2) }}</td>
                        <td class="p-4 text-right text-brand-300 font-black text-sm">Rs. {{ number_format($expectedNetCashInHand, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Opening Balance Modal -->
<div id="openingBalanceModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden no-print">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-black text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-wallet text-brand-600"></i> Set Opening Cash Balance
            </h3>
            <button onclick="document.getElementById('openingBalanceModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form action="{{ route('day-book.store-opening') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Date</label>
                <input type="date" name="date" value="{{ $date }}" required class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none font-bold">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Opening Cash Amount (Rs.) *</label>
                <input type="number" step="0.01" min="0" name="opening_balance" value="{{ $openingBalance }}" required placeholder="e.g. 500"
                       class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none transition font-black text-slate-800 text-base">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Note (Optional)</label>
                <input type="text" name="notes" value="{{ $dayBook?->notes }}" placeholder="e.g. Counter drawer cash at shift start"
                       class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('openingBalanceModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs shadow-xs transition">Save Opening Balance</button>
            </div>
        </form>
    </div>
</div>
@endsection

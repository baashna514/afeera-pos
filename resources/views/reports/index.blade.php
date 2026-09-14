@extends('layouts.app', ['title' => 'Profit & Loss & Business Reports'])

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-emerald-600"></i>
                <span>Profit & Loss & Business Analytics</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Comprehensive P&L Statement, Product-level profitability, and Operating Expenses report.</p>
        </div>
        <button type="button" onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 no-print">
            <i class="fa-solid fa-print"></i>
            <span>Print Report</span>
        </button>
    </div>

    <!-- Date Range Filter Card -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs no-print">
        <form action="{{ route('reports.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">From Date:</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">To Date:</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                       class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                <i class="fa-solid fa-filter"></i> Apply Filter
            </button>
        </form>
    </div>

    <!-- Executive Profit & Loss Statement Card -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-800">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-700/60 pb-5 mb-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 block mb-1">Financial Performance Statement</span>
                <h3 class="text-2xl font-black text-white">Profit and Loss Overview</h3>
                <p class="text-xs text-slate-300 mt-0.5">Period: {{ Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-bold text-slate-400 block">Overall Profit Margin</span>
                <span class="text-2xl font-black {{ $overallMargin >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                    {{ number_format($overallMargin, 1) }}%
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 text-xs">
            <!-- 1. Total Sales Revenue -->
            <div class="p-4 bg-white/10 backdrop-blur-xs rounded-2xl border border-white/10 space-y-1">
                <span class="text-[11px] font-bold text-slate-300 uppercase block">1. Total Sales Revenue</span>
                <span class="text-xl font-black text-emerald-400 block">Rs. {{ number_format($totalSalesAmount, 2) }}</span>
                <span class="text-[10px] text-slate-400 block">{{ $totalOrdersCount }} completed invoice(s)</span>
            </div>

            <!-- 2. COGS (Cost of Goods Sold) -->
            <div class="p-4 bg-white/10 backdrop-blur-xs rounded-2xl border border-white/10 space-y-1">
                <span class="text-[11px] font-bold text-slate-300 uppercase block">2. Cost of Goods Sold</span>
                <span class="text-xl font-black text-amber-400 block">Rs. {{ number_format($cogsAmount, 2) }}</span>
                <span class="text-[10px] text-slate-400 block">Inventory purchase cost</span>
            </div>

            <!-- 3. Gross Profit -->
            <div class="p-4 bg-white/10 backdrop-blur-xs rounded-2xl border border-white/10 space-y-1">
                <span class="text-[11px] font-bold text-slate-300 uppercase block">3. Gross Profit (1 - 2)</span>
                <span class="text-xl font-black text-blue-400 block">Rs. {{ number_format($grossProfit, 2) }}</span>
                <span class="text-[10px] text-slate-400 block">Before operating expenses</span>
            </div>

            <!-- 4. Total Operating Expenses -->
            <div class="p-4 bg-white/10 backdrop-blur-xs rounded-2xl border border-white/10 space-y-1">
                <span class="text-[11px] font-bold text-slate-300 uppercase block">4. Total Expenses</span>
                <span class="text-xl font-black text-purple-400 block">Rs. {{ number_format($totalExpensesAmount, 2) }}</span>
                <span class="text-[10px] text-slate-400 block">Bills, rent, salaries, etc.</span>
            </div>

            <!-- 5. Net Profit / Loss -->
            <div class="p-4 bg-white/10 backdrop-blur-xs rounded-2xl border border-white/10 space-y-1 sm:col-span-2 lg:col-span-1">
                <span class="text-[11px] font-bold text-slate-300 uppercase block">5. Net Profit / Loss</span>
                <span class="text-xl font-black {{ $netProfit >= 0 ? 'text-emerald-400' : 'text-rose-400' }} block">
                    Rs. {{ number_format($netProfit, 2) }}
                </span>
                <span class="text-[10px] text-slate-400 block">Final bottom line</span>
            </div>
        </div>
    </div>

    <!-- Product-wise Profitability Breakdown Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-emerald-600"></i>
                <h3 class="font-bold text-slate-800 text-sm">Product-wise Profit Breakdown</h3>
            </div>
            <span class="text-xs text-slate-500 font-medium">{{ count($productProfits) }} product(s) sold in selected period</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase tracking-wider text-[11px] font-bold">
                        <th class="p-4">#</th>
                        <th class="p-4">Product Name</th>
                        <th class="p-4">Category</th>
                        <th class="p-4 text-center">Units Sold</th>
                        <th class="p-4 text-right">Total Revenue</th>
                        <th class="p-4 text-right">Total Purchase Cost</th>
                        <th class="p-4 text-right">Gross Profit</th>
                        <th class="p-4 text-right">Margin %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($productProfits as $idx => $row)
                        @php
                            $prod = $row['product'];
                            $margin = $row['revenue'] > 0 ? ($row['profit'] / $row['revenue']) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="p-4 font-mono text-slate-400">{{ $idx + 1 }}</td>
                            <td class="p-4 font-bold text-slate-800">
                                {{ $prod->name ?? 'Deleted Product' }}
                                <span class="block text-[10px] text-slate-400 font-mono">Code: {{ $prod->code ?? '-' }}</span>
                            </td>
                            <td class="p-4 text-slate-500">
                                {{ $prod->category->name ?? 'General' }}
                            </td>
                            <td class="p-4 text-center font-bold text-slate-700">
                                {{ number_format($row['units_sold']) }}
                            </td>
                            <td class="p-4 text-right font-mono text-slate-700">
                                Rs. {{ number_format($row['revenue'], 2) }}
                            </td>
                            <td class="p-4 text-right font-mono text-slate-500">
                                Rs. {{ number_format($row['cogs'], 2) }}
                            </td>
                            <td class="p-4 text-right font-black {{ $row['profit'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                Rs. {{ number_format($row['profit'], 2) }}
                            </td>
                            <td class="p-4 text-right font-bold">
                                <span class="px-2 py-0.5 rounded-full text-[10px] {{ $margin >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ number_format($margin, 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400">
                                <i class="fa-solid fa-box-open text-2xl mb-2 block"></i>
                                No sales recorded in this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Operating Expense Breakdown Table -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Expense Categories Summary -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-amber-600"></i>
                    <h3 class="font-bold text-slate-800 text-sm">Expenses Breakdown by Category</h3>
                </div>
                <a href="{{ route('expenses.index') }}" class="text-xs font-semibold text-amber-600 hover:underline">View Expenses &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase tracking-wider text-[11px] font-bold">
                            <th class="p-4">Category</th>
                            <th class="p-4 text-right">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($expenseCategoryBreakdown as $catRow)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="p-4 font-bold text-slate-800">
                                    {{ $catRow->category->name ?? 'General Expense' }}
                                </td>
                                <td class="p-4 text-right font-black text-amber-600">
                                    Rs. {{ number_format($catRow->total, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="p-6 text-center text-slate-400 text-xs">No expenses in selected period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Methods Summary -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-money-bill-transfer text-blue-600"></i>
                    <h3 class="font-bold text-slate-800 text-sm">Payment Methods Revenue Breakdown</h3>
                </div>
            </div>
            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-xl border border-emerald-100 text-xs">
                    <span class="font-bold text-emerald-800 flex items-center gap-2">
                        <i class="fa-solid fa-money-bill-wave"></i> Cash Collections
                    </span>
                    <span class="font-black text-emerald-900 text-sm">Rs. {{ number_format($cashSales, 2) }}</span>
                </div>

                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl border border-blue-100 text-xs">
                    <span class="font-bold text-blue-800 flex items-center gap-2">
                        <i class="fa-solid fa-credit-card"></i> Card Payments
                    </span>
                    <span class="font-black text-blue-900 text-sm">Rs. {{ number_format($cardSales, 2) }}</span>
                </div>

                <div class="flex items-center justify-between p-3 bg-purple-50 rounded-xl border border-purple-100 text-xs">
                    <span class="font-bold text-purple-800 flex items-center gap-2">
                        <i class="fa-solid fa-building-columns"></i> Bank Transfers
                    </span>
                    <span class="font-black text-purple-900 text-sm">Rs. {{ number_format($bankSales, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

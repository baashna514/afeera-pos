@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Welcome & Quick Action Header -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 text-white p-6 md:p-8 rounded-2xl shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 text-emerald-400 text-xs font-bold uppercase tracking-wider mb-2">
                <i class="fa-solid fa-store"></i> Point of Sale & Inventory System
            </div>
            <h2 class="text-2xl md:text-3xl font-black tracking-tight">SmartPOS Dashboard</h2>
            <p class="text-slate-300 text-sm mt-1">Real-time overview of sales, stock alerts, purchases, expenses, and profit & loss analytics.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('pos.index') }}" class="px-5 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/30 flex items-center gap-2 transition duration-150 group">
                <i class="fa-solid fa-cart-plus text-base group-hover:scale-110 transition-transform"></i>
                <span>Open POS Terminal</span>
            </a>
            <a href="{{ route('expenses.create') }}" class="px-4 py-3 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-xl flex items-center gap-2 transition shadow-md shadow-amber-600/20">
                <i class="fa-solid fa-receipt text-xs"></i>
                <span>Add Expense</span>
            </a>
            <a href="{{ route('purchases.create') }}" class="px-4 py-3 bg-slate-800/80 hover:bg-slate-700 text-slate-100 font-semibold rounded-xl border border-slate-700 flex items-center gap-2 transition">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>New Purchase</span>
            </a>
        </div>
    </div>

    <!-- Date Range Filter Bar for Analytics -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        <!-- Preset Filter Buttons -->
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-1">
                <i class="fa-solid fa-calendar text-emerald-600"></i> Period:
            </span>
            <a href="{{ route('dashboard', ['preset_filter' => 'today']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $presetFilter === 'today' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Today
            </a>
            <a href="{{ route('dashboard', ['preset_filter' => 'yesterday']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $presetFilter === 'yesterday' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Yesterday
            </a>
            <a href="{{ route('dashboard', ['preset_filter' => 'this_week']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $presetFilter === 'this_week' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                This Week
            </a>
            <a href="{{ route('dashboard', ['preset_filter' => 'this_month']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $presetFilter === 'this_month' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                This Month
            </a>
            <a href="{{ route('dashboard', ['preset_filter' => 'all_time']) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $presetFilter === 'all_time' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All Time
            </a>
        </div>

        <!-- Custom Date Range Form -->
        <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="preset_filter" value="custom">
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="px-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            <span class="text-xs text-slate-400 font-bold">to</span>
            <input type="date" name="date_to" value="{{ $dateTo }}" class="px-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            <button type="submit" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition">
                Apply Filter
            </button>
        </form>
    </div>

    <!-- Profit & Loss Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Today's Net Profit -->
        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white p-6 rounded-2xl shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-100">Today's Net Profit</span>
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-xs text-white flex items-center justify-center text-lg font-black">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-3xl font-black">Rs. {{ number_format($todayNetProfit, 2) }}</p>
                <p class="text-xs text-emerald-100 mt-1">
                    Sales: <span class="font-bold">Rs. {{ number_format($todaySales, 0) }}</span> | Exp: <span class="font-bold">Rs. {{ number_format($todayExpenses, 0) }}</span>
                </p>
            </div>
        </div>

        <!-- Filtered Period Net Profit / Loss -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Period Net Profit</span>
                <div class="w-10 h-10 rounded-xl {{ $filteredNetProfit >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center">
                    <i class="fa-solid {{ $filteredNetProfit >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} text-lg"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-black {{ $filteredNetProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    Rs. {{ number_format($filteredNetProfit, 2) }}
                </p>
                <p class="text-xs text-slate-500 mt-1">
                    Margin: <span class="font-bold text-slate-700">{{ number_format($filteredProfitMargin, 1) }}%</span> (Gross: Rs. {{ number_format($filteredGrossProfit, 0) }})
                </p>
            </div>
        </div>

        <!-- Period Total Sales -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Period Sales</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-sack-dollar text-lg"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-black text-slate-800">Rs. {{ number_format($filteredSales, 2) }}</p>
                <p class="text-xs text-slate-500 mt-1">
                    COGS Cost: <span class="font-bold text-slate-700">Rs. {{ number_format($filteredCogs, 0) }}</span>
                </p>
            </div>
        </div>

        <!-- Period Expenses -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Period Expenses</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-receipt text-lg"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-black text-amber-600">Rs. {{ number_format($filteredExpenses, 2) }}</p>
                <p class="text-xs text-slate-500 mt-1">
                    <a href="{{ route('expenses.index') }}" class="text-amber-600 hover:underline font-semibold">
                        View expense log &rarr;
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Secondary Summary Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-black">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase">Customers</p>
                <p class="text-lg font-black text-slate-800">{{ number_format($totalCustomers) }}</p>
            </div>
            <a href="{{ route('customers.index') }}" class="ml-auto text-xs text-indigo-600 font-semibold hover:underline">Manage</a>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200/80 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-black">
                <i class="fa-solid fa-truck-moving"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase">Vendors</p>
                <p class="text-lg font-black text-slate-800">{{ number_format($totalVendors) }}</p>
            </div>
            <a href="{{ route('vendors.index') }}" class="ml-auto text-xs text-emerald-600 font-semibold hover:underline">Manage</a>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200/80 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-black">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase">Low Stock</p>
                <p class="text-lg font-black text-rose-600">{{ number_format($lowStockCount) }}</p>
            </div>
            <a href="{{ route('stock.index', ['status' => 'low_stock']) }}" class="ml-auto text-xs text-rose-600 font-semibold hover:underline">Stock</a>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200/80 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl font-black">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase">Stock Valuation</p>
                <p class="text-lg font-black text-slate-800">Rs. {{ number_format($totalStockValue, 0) }}</p>
            </div>
            <a href="{{ route('stock.index') }}" class="ml-auto text-xs text-teal-600 font-semibold hover:underline">Stock</a>
        </div>
    </div>

    <!-- Data Tables Grid: Recent Sales & Recent Expenses -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Sales -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-emerald-600"></i>
                    <h3 class="font-bold text-slate-800">Recent Sales Invoices</h3>
                </div>
                <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">View All &rarr;</a>
            </div>
            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3">Invoice</th>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentSales as $sale)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-5 py-3 font-semibold text-slate-800">
                                    <a href="{{ route('sales.show', $sale) }}" class="text-emerald-600 hover:underline font-mono">
                                        {{ $sale->invoice_number }}
                                    </a>
                                    <span class="block text-[10px] text-slate-400">{{ $sale->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="px-5 py-3 text-slate-600">
                                    {{ $sale->customer_display_name }}
                                </td>
                                <td class="px-5 py-3 font-bold text-slate-800">
                                    Rs. {{ number_format($sale->total_amount, 2) }}
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('sales.show', $sale) }}" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-md text-[11px]">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-6 text-center text-slate-400 text-xs">No recent sales found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Expenses -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-wallet text-amber-600"></i>
                    <h3 class="font-bold text-slate-800">Recent Operating Expenses</h3>
                </div>
                <a href="{{ route('expenses.index') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700">View All &rarr;</a>
            </div>
            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Category</th>
                            <th class="px-5 py-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentExpenses as $exp)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-5 py-3 font-mono text-slate-600">
                                    {{ $exp->expense_date->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3 font-bold text-slate-800">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] bg-amber-50 text-amber-800 border border-amber-100">
                                        {{ $exp->category->name ?? 'General' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right font-black text-rose-600">
                                    Rs. {{ number_format($exp->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-6 text-center text-slate-400 text-xs">No recent expenses logged.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

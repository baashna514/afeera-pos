@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Today & Filtered Profit Analytics Bar -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 text-white p-4 rounded-xl shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg font-bold border border-emerald-500/30">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <span class="block text-[11px] font-bold uppercase tracking-wider text-emerald-400">Net Profit Analytics</span>
                <span class="text-xl font-black text-white">Rs. {{ number_format($filteredNetProfit, 2) }}</span>
                <span class="text-xs text-slate-400 ml-2">Today Net Profit: <strong class="text-emerald-400">Rs. {{ number_format($todayNetProfit, 2) }}</strong></span>
            </div>
        </div>
        <div class="flex items-center gap-6 text-xs text-slate-300">
            <div>
                <span class="block text-[10px] text-slate-400 font-semibold uppercase">Filtered Sales</span>
                <span class="font-bold text-white">Rs. {{ number_format($filteredSales, 2) }}</span>
            </div>
            <div>
                <span class="block text-[10px] text-slate-400 font-semibold uppercase">Filtered Expenses</span>
                <span class="font-bold text-amber-400">Rs. {{ number_format($filteredExpenses, 2) }}</span>
            </div>
            <div>
                <span class="block text-[10px] text-slate-400 font-semibold uppercase">Net Profit Margin</span>
                <span class="font-bold text-emerald-400">{{ number_format($filteredProfitMargin, 1) }}%</span>
            </div>
        </div>
    </div>

    <!-- Top Row 1: Financial Overview Cards (4 Grid Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Receivables -->
        <div class="bg-amber-400 text-slate-900 p-4 rounded-xl shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-white/30 flex items-center justify-center text-slate-900 text-xl font-bold">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-800">Total Receivables</span>
                    <span class="text-xl font-black">Rs. {{ number_format($totalReceivables, 2) }}</span>
                    <span class="block text-[10px] text-slate-700 font-medium">This Month</span>
                </div>
            </div>
        </div>

        <!-- Total Payables -->
        <div class="bg-cyan-500 text-white p-4 rounded-xl shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-white/25 flex items-center justify-center text-white text-xl font-bold">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-cyan-100">Total Payables</span>
                    <span class="text-xl font-black">Rs. {{ number_format($totalPayables, 2) }}</span>
                    <span class="block text-[10px] text-cyan-100 font-medium">This Month</span>
                </div>
            </div>
        </div>

        <!-- Cash Balance -->
        <div class="bg-rose-600 text-white p-4 rounded-xl shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-white/25 flex items-center justify-center text-white text-2xl font-black">
                    $
                </div>
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-rose-100">Cash Balance</span>
                    <span class="text-xl font-black">Rs. {{ number_format($cashBalance, 2) }}</span>
                    <span class="block text-[10px] text-rose-100 font-medium">This Month</span>
                </div>
            </div>
        </div>

        <!-- Bank Balance -->
        <div class="bg-emerald-500 text-white p-4 rounded-xl shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-white/25 flex items-center justify-center text-white text-2xl font-black">
                    $
                </div>
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-emerald-100">Bank Balance</span>
                    <span class="text-xl font-black">Rs. {{ number_format($bankBalance, 2) }}</span>
                    <span class="block text-[10px] text-emerald-100 font-medium">This Month</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Row 2: Sales Timeframe Breakdown Cards (4 Grid Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Daily Sale -->
        <div class="bg-emerald-500 text-white p-4 rounded-xl shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-white/25 flex items-center justify-center text-white text-xl font-bold">
                    <i class="fa-solid fa-money-bill-1"></i>
                </div>
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-emerald-100">Daily Sale</span>
                    <span class="text-xl font-black">Rs. {{ number_format($dailySale, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Weekly Sale -->
        <div class="bg-rose-600 text-white p-4 rounded-xl shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-white/25 flex items-center justify-center text-white text-xl font-bold">
                    <i class="fa-solid fa-money-bill-1"></i>
                </div>
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-rose-100">Weekly Sale</span>
                    <span class="text-xl font-black">Rs. {{ number_format($weeklySale, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Monthly Sale -->
        <div class="bg-cyan-500 text-white p-4 rounded-xl shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-white/25 flex items-center justify-center text-white text-2xl font-black">
                    $
                </div>
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-cyan-100">Monthly Sale</span>
                    <span class="text-xl font-black">Rs. {{ number_format($monthlySale, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Yearly Sale -->
        <div class="bg-amber-400 text-slate-900 p-4 rounded-xl shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-white/30 flex items-center justify-center text-slate-900 text-2xl font-black">
                    $
                </div>
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-800">Yearly Sale</span>
                    <span class="text-xl font-black">Rs. {{ number_format($yearlySale, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Row 3: Expense Timeframe Breakdown Cards (4 Grid Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Daily Expense -->
        <div class="bg-amber-400 text-slate-900 p-4 rounded-xl shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-white/30 flex items-center justify-center text-slate-900 text-xl font-bold">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-800">Daily Expense</span>
                    <span class="text-xl font-black">Rs. {{ number_format($dailyExpense, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Weekly Expense -->
        <div class="bg-cyan-500 text-white p-4 rounded-xl shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-white/25 flex items-center justify-center text-white text-xl font-bold">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-cyan-100">Weekly Expense</span>
                    <span class="text-xl font-black">Rs. {{ number_format($weeklyExpense, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Monthly Expense -->
        <div class="bg-rose-600 text-white p-4 rounded-xl shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-white/25 flex items-center justify-center text-white text-2xl font-black">
                    $
                </div>
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-rose-100">Monthly Expense</span>
                    <span class="text-xl font-black">Rs. {{ number_format($monthlyExpense, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Yearly Expense -->
        <div class="bg-emerald-500 text-white p-4 rounded-xl shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-white/25 flex items-center justify-center text-white text-2xl font-black">
                    $
                </div>
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-emerald-100">Yearly Expense</span>
                    <span class="text-xl font-black">Rs. {{ number_format($yearlyExpense, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Middle Section: Left Quick Actions Grid + Right Tabbed Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
        <!-- Left Quick Actions Red Button Grid (4 cols out of 12) -->
        <div class="lg:col-span-4 bg-white p-4.5 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="grid grid-cols-2 gap-2.5">
                <!-- Add Customer -->
                <a href="{{ route('customers.create') }}" class="px-3 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold rounded-xl shadow-xs flex items-center justify-between gap-1 transition">
                    <span>Add Customer</span>
                    <i class="fa-solid fa-user-plus text-xs"></i>
                </a>

                <!-- Add Vendor -->
                <a href="{{ route('vendors.create') }}" class="px-3 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold rounded-xl shadow-xs flex items-center justify-between gap-1 transition">
                    <span>Add Vendor</span>
                    <i class="fa-solid fa-user-tie text-xs"></i>
                </a>

                <!-- Add Product -->
                <a href="{{ route('products.create') }}" class="px-3 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold rounded-xl shadow-xs flex items-center justify-between gap-1 transition">
                    <span>Add Product</span>
                    <i class="fa-solid fa-box text-xs"></i>
                </a>

                <!-- Add Brand -->
                <a href="{{ route('brands.create') }}" class="px-3 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold rounded-xl shadow-xs flex items-center justify-between gap-1 transition">
                    <span>Add Brand</span>
                    <i class="fa-solid fa-copyright text-xs"></i>
                </a>

                <!-- New Sale -->
                <a href="{{ route('sales.create') }}" class="px-3 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold rounded-xl shadow-xs flex items-center justify-between gap-1 transition">
                    <span>New Sale</span>
                    <i class="fa-solid fa-file-invoice text-xs"></i>
                </a>

                <!-- New Purchase -->
                <a href="{{ route('purchases.create') }}" class="px-3 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold rounded-xl shadow-xs flex items-center justify-between gap-1 transition">
                    <span>New Purchase</span>
                    <i class="fa-solid fa-cart-shopping text-xs"></i>
                </a>

                <!-- Cash Receipt -->
                <a href="{{ route('vouchers.create', ['type' => 'receipt']) }}" class="px-3 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold rounded-xl shadow-xs flex items-center justify-between gap-1 transition">
                    <span>Cash Receipt</span>
                    <i class="fa-solid fa-circle-plus text-xs"></i>
                </a>

                <!-- Cash Payment -->
                <a href="{{ route('vouchers.create', ['type' => 'payment']) }}" class="px-3 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold rounded-xl shadow-xs flex items-center justify-between gap-1 transition">
                    <span>Cash Payment</span>
                    <i class="fa-solid fa-circle-minus text-xs"></i>
                </a>

                <!-- Add Warehouse -->
                <a href="{{ route('warehouses.create') }}" class="px-3 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold rounded-xl shadow-xs flex items-center justify-between gap-1 transition">
                    <span>Add Warehouse</span>
                    <i class="fa-solid fa-warehouse text-xs"></i>
                </a>

                <!-- Add Category -->
                <a href="{{ route('categories.create') }}" class="px-3 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold rounded-xl shadow-xs flex items-center justify-between gap-1 transition">
                    <span>Add Category</span>
                    <i class="fa-solid fa-tags text-xs"></i>
                </a>
            </div>
        </div>

        <!-- Right Tabbed Panel (8 cols out of 12) -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between" x-data="{ activeTab: 'chart' }">
            <!-- Tabs Navigation -->
            <div class="flex items-center gap-6 border-b border-slate-200 pb-3">
                <button @click="activeTab = 'chart'" :class="activeTab === 'chart' ? 'text-rose-600 border-b-2 border-rose-600 font-bold' : 'text-slate-500 font-semibold hover:text-slate-800'" class="text-xs pb-2 transition">
                    Sales/Purchases
                </button>
                <button @click="activeTab = 'expenses'" :class="activeTab === 'expenses' ? 'text-rose-600 border-b-2 border-rose-600 font-bold' : 'text-slate-500 font-semibold hover:text-slate-800'" class="text-xs pb-2 transition">
                    Recent Expenses
                </button>
                <button @click="activeTab = 'due'" :class="activeTab === 'due' ? 'text-rose-600 border-b-2 border-rose-600 font-bold' : 'text-slate-500 font-semibold hover:text-slate-800'" class="text-xs pb-2 transition">
                    Client Due
                </button>
                <button @click="activeTab = 'received'" :class="activeTab === 'received' ? 'text-rose-600 border-b-2 border-rose-600 font-bold' : 'text-slate-500 font-semibold hover:text-slate-800'" class="text-xs pb-2 transition">
                    Amount Received
                </button>
            </div>

            <!-- Tab 1: Sales/Purchases Chart -->
            <div x-show="activeTab === 'chart'" class="pt-4 flex-1">
                <div class="relative h-64 w-full">
                    <canvas id="salesPurchasesChart"></canvas>
                </div>
            </div>

            <!-- Tab 2: Recent Expenses -->
            <div x-show="activeTab === 'expenses'" class="pt-4 flex-1 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-3 py-2">Date</th>
                            <th class="px-3 py-2">Category</th>
                            <th class="px-3 py-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentExpenses as $exp)
                            <tr>
                                <td class="px-3 py-2 text-slate-600 font-mono">{{ $exp->expense_date->format('d M Y') }}</td>
                                <td class="px-3 py-2 font-bold text-slate-800">{{ $exp->category->name ?? 'General' }}</td>
                                <td class="px-3 py-2 text-right font-bold text-rose-600">Rs. {{ number_format($exp->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-3 py-4 text-center text-slate-400">No expenses recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Tab 3: Client Due -->
            <div x-show="activeTab === 'due'" class="pt-4 flex-1 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-3 py-2">Invoice</th>
                            <th class="px-3 py-2">Customer</th>
                            <th class="px-3 py-2 text-right">Due Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($clientDues as $due)
                            <tr>
                                <td class="px-3 py-2 font-mono font-bold text-emerald-600">{{ $due->invoice_number }}</td>
                                <td class="px-3 py-2 text-slate-800 font-semibold">{{ $due->customer_display_name }}</td>
                                <td class="px-3 py-2 text-right font-black text-rose-600">Rs. {{ number_format($due->due_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-3 py-4 text-center text-slate-400">No client dues pending.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Tab 4: Amount Received -->
            <div x-show="activeTab === 'received'" class="pt-4 flex-1 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-3 py-2">Invoice</th>
                            <th class="px-3 py-2">Customer</th>
                            <th class="px-3 py-2 text-right">Paid Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($amountReceived as $rec)
                            <tr>
                                <td class="px-3 py-2 font-mono font-bold text-emerald-600">{{ $rec->invoice_number }}</td>
                                <td class="px-3 py-2 text-slate-800 font-semibold">{{ $rec->customer_display_name }}</td>
                                <td class="px-3 py-2 text-right font-black text-emerald-600">Rs. {{ number_format($rec->paid_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-3 py-4 text-center text-slate-400">No recent payment receipts.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
    </div>
</div>

<!-- Chart.js Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesPurchasesChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [
                        {
                            label: 'Sales (Rs.)',
                            data: @json($salesChartData),
                            backgroundColor: '#ef4444',
                            borderRadius: 4,
                        },
                        {
                            label: 'Purchases (Rs.)',
                            data: @json($purchasesChartData),
                            backgroundColor: '#94a3b8',
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection

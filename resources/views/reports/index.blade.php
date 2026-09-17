@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header & Action Controls -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Reports & Analytics Hub</h2>
            <p class="text-xs text-slate-500 mt-0.5">Comprehensive real-time business performance & audit center (20 Reports Available)</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-2">
                <i class="fa-solid fa-print"></i>
                <span>Print Report</span>
            </button>
        </div>
    </div>

    <!-- Category Nav Tabs -->
    <div class="bg-white p-3 rounded-2xl shadow-xs border border-slate-200 no-print">
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Sales Reports Dropdown/Tab Group -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ Str::startsWith($type, 'sales') ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>Sales Reports</span>
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>
                <div x-show="open" @click.away="open = false" class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-200 py-2 z-30">
                    <a href="{{ route('reports.index', ['type' => 'sales_summary', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">Sales Summary</a>
                    <a href="{{ route('reports.index', ['type' => 'sales_detail', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">Sales Detail</a>
                    <a href="{{ route('reports.index', ['type' => 'sales_by_product', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">Sales by Product</a>
                    <a href="{{ route('reports.index', ['type' => 'sales_by_customer', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">Sales by Customer</a>
                    <a href="{{ route('reports.index', ['type' => 'sales_by_warehouse', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">Sales by Warehouse</a>
                    <a href="{{ route('reports.index', ['type' => 'sales_returns', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">Sales Returns</a>
                </div>
            </div>

            <!-- Purchase Reports Dropdown/Tab Group -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ Str::startsWith($type, 'purchase') ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                    <span>Purchase Reports</span>
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>
                <div x-show="open" @click.away="open = false" class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-200 py-2 z-30">
                    <a href="{{ route('reports.index', ['type' => 'purchase_summary', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-purple-50 hover:text-purple-700">Purchase Summary</a>
                    <a href="{{ route('reports.index', ['type' => 'purchase_by_product', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-purple-50 hover:text-purple-700">Purchase by Product</a>
                    <a href="{{ route('reports.index', ['type' => 'purchase_by_vendor', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-purple-50 hover:text-purple-700">Purchase by Vendor</a>
                    <a href="{{ route('reports.index', ['type' => 'purchase_returns', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-purple-50 hover:text-purple-700">Purchase Returns</a>
                </div>
            </div>

            <!-- Stock & Inventory Dropdown/Tab Group -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ Str::startsWith($type, 'stock') || Str::contains($type, 'stock') ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>Stock Reports</span>
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>
                <div x-show="open" @click.away="open = false" class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-200 py-2 z-30">
                    <a href="{{ route('reports.index', ['type' => 'current_stock', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700">Current Stock (Alerts & Warehouses)</a>
                    <a href="{{ route('reports.index', ['type' => 'stock_ledger', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700">Stock Ledger & Flow Audit</a>
                    <a href="{{ route('reports.index', ['type' => 'stock_movement', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700">Stock Movement Analysis</a>
                    <a href="{{ route('reports.index', ['type' => 'stock_valuation', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700">Stock Valuation</a>
                    <a href="{{ route('reports.index', ['type' => 'low_stock', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700">Low / Out-of-Stock Alerts</a>
                </div>
            </div>

            <!-- Party & Ledgers Dropdown/Tab Group -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ Str::contains($type, 'aging') || $type === 'party_ledger' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-users"></i>
                    <span>Party & Aging</span>
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>
                <div x-show="open" @click.away="open = false" class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-200 py-2 z-30">
                    <a href="{{ route('reports.index', ['type' => 'customer_aging', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-amber-50 hover:text-amber-700">Customer Outstanding & Aging</a>
                    <a href="{{ route('reports.index', ['type' => 'vendor_aging', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-amber-50 hover:text-amber-700">Vendor Outstanding & Aging</a>
                    <a href="{{ route('reports.index', ['type' => 'party_ledger', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-amber-50 hover:text-amber-700">Customer/Vendor Statement</a>
                </div>
            </div>

            <!-- Financials & Cash Register -->
            <a href="{{ route('reports.index', ['type' => 'profit_loss', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $type === 'profit_loss' ? 'bg-teal-600 text-white shadow-xs' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-chart-line"></i>
                <span>Profit & Loss</span>
            </a>

            <a href="{{ route('reports.index', ['type' => 'cashier_closing', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $type === 'cashier_closing' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-cash-register"></i>
                <span>Cashier Closing</span>
            </a>
        </div>
    </div>

    <!-- Filter Form Bar -->
    <div class="bg-white p-4 rounded-2xl shadow-xs border border-slate-200 no-print">
        <form action="{{ route('reports.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 items-end">
            <input type="hidden" name="type" value="{{ $type }}">
            
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">From Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">To Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500">
            </div>

            @if(in_array($type, ['sales_detail', 'sales_by_customer', 'party_ledger']))
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Filter Customer</label>
                <select name="customer_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(in_array($type, ['purchase_by_vendor', 'party_ledger']))
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Filter Vendor</label>
                <select name="vendor_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-purple-500">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" {{ $vendorId == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(in_array($type, ['sales_summary', 'sales_detail', 'sales_by_warehouse', 'purchase_summary', 'current_stock', 'stock_ledger', 'stock_movement']))
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Filter Warehouse</label>
                <select name="warehouse_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500">
                    <option value="">All Warehouses</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ $warehouseId == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(in_array($type, ['sales_by_product', 'purchase_by_product', 'current_stock', 'stock_ledger']))
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Filter Product</label>
                <select name="product_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ $productId == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->code ?? $p->sku }})</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <button type="submit" class="w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-filter"></i>
                    <span>Apply Filter</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Active Report View Content -->
    <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-200 space-y-6">
        <!-- Print Header -->
        <div class="hidden print:block border-b border-slate-200 pb-4 mb-4">
            <h1 class="text-2xl font-black text-slate-900">{{ auth()->user()?->company?->name ?? 'SmartPOS System' }}</h1>
            <p class="text-sm font-bold text-slate-700 uppercase tracking-wider mt-1">{{ str_replace('_', ' ', strtoupper($type)) }} REPORT</p>
            <p class="text-xs text-slate-500">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
        </div>

        @if($type === 'sales_summary')
            <div class="space-y-6">
                <h3 class="text-base font-bold text-slate-800">Sales Summary Overview</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                        <span class="text-xs font-bold text-emerald-700 uppercase block">Total Sales</span>
                        <span class="text-xl font-black text-emerald-900">Rs. {{ number_format($totalSales, 2) }}</span>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <span class="text-xs font-bold text-blue-700 uppercase block">Total Paid</span>
                        <span class="text-xl font-black text-blue-900">Rs. {{ number_format($totalPaid, 2) }}</span>
                    </div>
                    <div class="p-4 bg-rose-50 rounded-xl border border-rose-100">
                        <span class="text-xs font-bold text-rose-700 uppercase block">Total Credit Due</span>
                        <span class="text-xl font-black text-rose-900">Rs. {{ number_format($totalDue, 2) }}</span>
                    </div>
                    <div class="p-4 bg-purple-50 rounded-xl border border-purple-100">
                        <span class="text-xs font-bold text-purple-700 uppercase block">Total Orders</span>
                        <span class="text-xl font-black text-purple-900">{{ $totalOrders }}</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 uppercase tracking-wider text-slate-500 font-bold">
                                <th class="p-3">Date</th>
                                <th class="p-3">Orders</th>
                                <th class="p-3 text-right">Total Amount</th>
                                <th class="p-3 text-right">Paid Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($dailyTrend as $row)
                                <tr>
                                    <td class="p-3 font-mono text-slate-700">{{ \Carbon\Carbon::parse($row->sale_date)->format('d M Y') }}</td>
                                    <td class="p-3 font-bold">{{ $row->count }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-emerald-600">Rs. {{ number_format($row->total, 2) }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-blue-600">Rs. {{ number_format($row->paid, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @elseif($type === 'current_stock' || $type === 'low_stock')
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">{{ $type === 'low_stock' ? 'Low Stock & Out-of-Stock Alerts Report' : 'Current Stock Inventory Report' }}</h3>
                        <p class="text-xs text-slate-500">Units-wise conversions, alert indicators, and warehouse-wise stock levels</p>
                    </div>
                </div>

                <!-- Stock Summary Metrics -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-xs font-bold text-slate-500 uppercase block">Total Products</span>
                        <span class="text-xl font-black text-slate-800">{{ $products->count() }}</span>
                    </div>
                    <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                        <span class="text-xs font-bold text-emerald-700 uppercase block">Total Physical Units</span>
                        <span class="text-xl font-black text-emerald-900">{{ number_format($products->sum('quantity')) }}</span>
                    </div>
                    <div class="p-4 bg-amber-50 rounded-xl border border-amber-100">
                        <span class="text-xs font-bold text-amber-700 uppercase block">Low Stock Alert Items</span>
                        <span class="text-xl font-black text-amber-900">{{ $products->filter(fn($p) => $p->quantity > 0 && $p->quantity <= ($p->alert_quantity ?? 5))->count() }}</span>
                    </div>
                    <div class="p-4 bg-rose-50 rounded-xl border border-rose-100">
                        <span class="text-xs font-bold text-rose-700 uppercase block">Out of Stock Items</span>
                        <span class="text-xl font-black text-rose-900">{{ $products->filter(fn($p) => $p->quantity <= 0)->count() }}</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 uppercase tracking-wider text-slate-500 font-bold">
                                <th class="p-3">Product Info</th>
                                <th class="p-3">Category & Brand</th>
                                <th class="p-3">Units & Conversions</th>
                                <th class="p-3">Warehouse Breakdown</th>
                                <th class="p-3 text-right">Total Qty</th>
                                <th class="p-3 text-center">Stock Alert Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($products as $p)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="p-3 font-medium">
                                        <div class="font-bold text-slate-800 text-sm">{{ $p->name }}</div>
                                        <span class="font-mono text-[11px] text-slate-400">Code: {{ $p->code ?? $p->sku ?? 'N/A' }}</span>
                                    </td>
                                    <td class="p-3 text-slate-600">
                                        <span class="font-semibold block">{{ $p->category->name ?? 'General' }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $p->brand->name ?? 'No Brand' }}</span>
                                    </td>
                                    <td class="p-3 text-slate-700">
                                        <span class="inline-block px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded font-bold text-[11px]">
                                            Base: {{ $p->unit->name ?? 'Pcs' }} ({{ $p->unit->short_code ?? 'Pcs' }})
                                        </span>
                                        @if($p->secondaryUnits->count() > 0)
                                            <div class="mt-1 space-y-0.5">
                                                @foreach($p->secondaryUnits as $su)
                                                    <span class="block text-[10px] text-slate-500">
                                                        • 1 {{ $su->unit->name ?? 'Unit' }} = {{ $su->conversion_rate }} {{ $p->unit->short_code ?? 'Pcs' }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        @if($p->warehouseStocks->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($p->warehouseStocks as $ws)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-slate-100 border border-slate-200 rounded text-[10px] text-slate-700 font-medium">
                                                        <i class="fa-solid fa-warehouse text-slate-400 text-[9px]"></i>
                                                        <strong>{{ $ws->warehouse->name ?? 'Whs' }}:</strong> {{ number_format($ws->quantity) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-[11px] text-slate-400 italic">Main Stock Only</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-right font-mono font-black text-sm text-slate-900">
                                        {{ number_format($p->quantity) }} <span class="text-xs font-normal text-slate-500">{{ $p->unit->short_code ?? '' }}</span>
                                    </td>
                                    <td class="p-3 text-center">
                                        @if($p->quantity <= 0)
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 inline-flex items-center gap-1">
                                                <i class="fa-solid fa-triangle-exclamation"></i> Out of Stock
                                            </span>
                                        @elseif($p->quantity <= ($p->alert_quantity ?? 5))
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 inline-flex items-center gap-1">
                                                <i class="fa-solid fa-bell"></i> Low Stock (Alert <= {{ $p->alert_quantity ?? 5 }})
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1">
                                                <i class="fa-solid fa-circle-check"></i> In Stock
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @elseif($type === 'stock_ledger')
            <div class="space-y-6">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Stock History & Transaction Flow Ledger</h3>
                    <p class="text-xs text-slate-500">Audit trail showing exact before quantity, transaction in/out flow, and running stock balance after every movement.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 uppercase tracking-wider text-slate-500 font-bold">
                                <th class="p-3">Date & Time</th>
                                <th class="p-3">Product Name</th>
                                <th class="p-3">Warehouse</th>
                                <th class="p-3">Movement Type</th>
                                <th class="p-3">Reference / Note</th>
                                <th class="p-3 text-right">Before Qty</th>
                                <th class="p-3 text-right">Stock Flow (+/-)</th>
                                <th class="p-3 text-right">After Qty (Running)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($movements as $m)
                                @php
                                    $isInflow = in_array($m->type, ['purchase', 'adjustment_add', 'transfer_in', 'return_in']);
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="p-3 font-mono text-slate-500">
                                        {{ \Carbon\Carbon::parse($m->created_at)->format('d M Y') }}
                                        <span class="block text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($m->created_at)->format('h:i A') }}</span>
                                    </td>
                                    <td class="p-3">
                                        <span class="font-bold text-slate-800">{{ $m->product->name ?? 'Product' }}</span>
                                        <span class="block text-[10px] font-mono text-slate-400">{{ $m->product->code ?? '' }}</span>
                                    </td>
                                    <td class="p-3 font-semibold text-slate-700">
                                        {{ $m->warehouse->name ?? 'Main Warehouse' }}
                                    </td>
                                    <td class="p-3">
                                        @if($m->type === 'sale')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                <i class="fa-solid fa-cart-shopping mr-1"></i> Cash Sale
                                            </span>
                                        @elseif($m->type === 'purchase')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <i class="fa-solid fa-truck-ramp-box mr-1"></i> Purchase In
                                            </span>
                                        @elseif(Str::startsWith($m->type, 'transfer'))
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                                <i class="fa-solid fa-right-left mr-1"></i> Stock Transfer
                                            </span>
                                        @elseif(Str::startsWith($m->type, 'adjustment'))
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                                <i class="fa-solid fa-sliders mr-1"></i> Adjustment
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                <i class="fa-solid fa-arrow-rotate-left mr-1"></i> Return
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-slate-600 font-mono">
                                        <span class="font-bold">{{ $m->reference ?? '-' }}</span>
                                        @if($m->notes)
                                            <span class="block text-[10px] font-sans text-slate-400">{{ $m->notes }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-right font-mono text-slate-500 font-semibold">
                                        {{ number_format($m->before_quantity) }}
                                    </td>
                                    <td class="p-3 text-right font-mono font-black text-sm {{ $isInflow ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $isInflow ? '+' : '-' }}{{ number_format($m->quantity) }}
                                    </td>
                                    <td class="p-3 text-right font-mono font-black text-slate-900 bg-slate-50">
                                        {{ number_format($m->after_quantity) }} <span class="text-[10px] font-normal text-slate-400">{{ $m->product->unit->short_code ?? '' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @elseif($type === 'stock_movement')
            <div class="space-y-4">
                <h3 class="text-base font-bold text-slate-800">Stock Movement Aggregate Summary</h3>
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 uppercase tracking-wider text-slate-500 font-bold">
                            <th class="p-3">Product Name</th>
                            <th class="p-3">Category</th>
                            <th class="p-3 text-right">Total In (+)</th>
                            <th class="p-3 text-right">Total Out (-)</th>
                            <th class="p-3 text-right">Net Change</th>
                            <th class="p-3 text-right">Current Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($stockMovements as $sm)
                            <tr>
                                <td class="p-3 font-bold text-slate-800">{{ $sm['product']->name ?? 'Product' }}</td>
                                <td class="p-3 text-slate-500">{{ $sm['product']->category->name ?? '-' }}</td>
                                <td class="p-3 text-right font-mono font-bold text-emerald-600">+{{ number_format($sm['total_in']) }}</td>
                                <td class="p-3 text-right font-mono font-bold text-rose-600">-{{ number_format($sm['total_out']) }}</td>
                                <td class="p-3 text-right font-mono font-bold {{ $sm['net_change'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $sm['net_change'] >= 0 ? '+' : '' }}{{ number_format($sm['net_change']) }}
                                </td>
                                <td class="p-3 text-right font-mono font-black text-slate-900">{{ number_format($sm['current_stock']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($type === 'stock_valuation')
            <div class="space-y-6">
                <h3 class="text-base font-bold text-slate-800">Stock Valuation & Profitability Summary</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                        <span class="text-xs font-bold text-slate-500 uppercase block">Total Physical Inventory</span>
                        <span class="text-xl font-black text-slate-800">{{ number_format($totalUnits) }} Units</span>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <span class="text-xs font-bold text-blue-700 uppercase block">Valuation at Cost</span>
                        <span class="text-xl font-black text-blue-900">Rs. {{ number_format($totalCostValuation, 2) }}</span>
                    </div>
                    <div class="p-4 bg-purple-50 rounded-xl border border-purple-100">
                        <span class="text-xs font-bold text-purple-700 uppercase block">Valuation at Selling Price</span>
                        <span class="text-xl font-black text-purple-900">Rs. {{ number_format($totalRetailValuation, 2) }}</span>
                    </div>
                    <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                        <span class="text-xs font-bold text-emerald-700 uppercase block">Potential Profit Margin</span>
                        <span class="text-xl font-black text-emerald-900">Rs. {{ number_format($potentialProfit, 2) }}</span>
                    </div>
                </div>
            </div>

        @elseif($type === 'sales_detail')
            <div class="space-y-4">
                <h3 class="text-base font-bold text-slate-800">Sales Detail Listing</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 uppercase tracking-wider text-slate-500 font-bold">
                                <th class="p-3">Invoice #</th>
                                <th class="p-3">Date</th>
                                <th class="p-3">Customer</th>
                                <th class="p-3 text-right">Grand Total</th>
                                <th class="p-3 text-right">Paid</th>
                                <th class="p-3 text-right">Due</th>
                                <th class="p-3">Method</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($sales as $s)
                                <tr>
                                    <td class="p-3 font-mono font-bold text-emerald-600">{{ $s->invoice_number }}</td>
                                    <td class="p-3 font-mono">{{ \Carbon\Carbon::parse($s->sale_date)->format('d M Y') }}</td>
                                    <td class="p-3 font-medium">{{ $s->customer->name ?? 'Walk-in' }}</td>
                                    <td class="p-3 text-right font-mono font-bold">Rs. {{ number_format($s->total_amount, 2) }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-emerald-600">Rs. {{ number_format($s->paid_amount, 2) }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-rose-600">Rs. {{ number_format($s->due_amount, 2) }}</td>
                                    <td class="p-3 uppercase font-bold text-[10px] text-slate-500">{{ $s->payment_method }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $sales->links() }}</div>
                </div>
            </div>

        @elseif($type === 'sales_by_product')
            <div class="space-y-4">
                <h3 class="text-base font-bold text-slate-800">Sales by Product Breakdown</h3>
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 uppercase tracking-wider text-slate-500 font-bold">
                            <th class="p-3">Product Name</th>
                            <th class="p-3">Category</th>
                            <th class="p-3 text-right">Qty Sold</th>
                            <th class="p-3 text-right">Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($productSales as $ps)
                            <tr>
                                <td class="p-3 font-bold text-slate-800">{{ $ps->product->name ?? 'Unknown Product' }}</td>
                                <td class="p-3 text-slate-500">{{ $ps->product->category->name ?? '-' }}</td>
                                <td class="p-3 text-right font-bold">{{ number_format($ps->total_qty) }}</td>
                                <td class="p-3 text-right font-mono font-bold text-emerald-600">Rs. {{ number_format($ps->total_revenue, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($type === 'sales_by_customer')
            <div class="space-y-4">
                <h3 class="text-base font-bold text-slate-800">Sales by Customer Report</h3>
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 uppercase tracking-wider text-slate-500 font-bold">
                            <th class="p-3">Customer Name</th>
                            <th class="p-3">Orders</th>
                            <th class="p-3 text-right">Total Invoiced</th>
                            <th class="p-3 text-right">Paid</th>
                            <th class="p-3 text-right">Due Outstanding</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($customerSales as $cs)
                            <tr>
                                <td class="p-3 font-bold text-slate-800">{{ $cs->customer->name ?? 'Walk-in Customer' }}</td>
                                <td class="p-3 font-bold">{{ $cs->order_count }}</td>
                                <td class="p-3 text-right font-mono font-bold">Rs. {{ number_format($cs->total_amount, 2) }}</td>
                                <td class="p-3 text-right font-mono font-bold text-emerald-600">Rs. {{ number_format($cs->paid_amount, 2) }}</td>
                                <td class="p-3 text-right font-mono font-bold text-rose-600">Rs. {{ number_format($cs->due_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($type === 'profit_loss')
            <div class="space-y-6 max-w-3xl">
                <h3 class="text-base font-bold text-slate-800">Profit & Loss Statement</h3>
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 space-y-4">
                    <div class="flex justify-between text-sm py-2 border-b border-slate-200">
                        <span class="font-bold text-slate-700">Gross Sales Revenue</span>
                        <span class="font-mono font-bold text-slate-900">Rs. {{ number_format($grossSales, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm py-2 border-b border-slate-200">
                        <span class="font-bold text-rose-600">Less: Sales Returns</span>
                        <span class="font-mono font-bold text-rose-600">- Rs. {{ number_format($salesReturns, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm py-2 border-b border-slate-300 font-bold bg-white p-2 rounded-lg">
                        <span class="text-slate-900">Net Sales Revenue</span>
                        <span class="font-mono text-emerald-700">Rs. {{ number_format($netSales, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm py-2 border-b border-slate-200">
                        <span class="font-bold text-slate-700">Less: Cost of Goods Sold (COGS)</span>
                        <span class="font-mono font-bold text-slate-700">- Rs. {{ number_format($cogs, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-base py-3 border-b-2 border-slate-400 font-black bg-emerald-50 p-3 rounded-xl text-emerald-900">
                        <span>GROSS PROFIT</span>
                        <span class="font-mono">Rs. {{ number_format($grossProfit, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm py-2 border-b border-slate-200">
                        <span class="font-bold text-amber-700">Less: Total Operating Expenses</span>
                        <span class="font-mono font-bold text-amber-700">- Rs. {{ number_format($expenses, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-lg py-4 border-2 border-indigo-500 font-black bg-indigo-600 text-white p-4 rounded-xl shadow-md">
                        <span>NET OPERATING PROFIT / (LOSS)</span>
                        <span class="font-mono">Rs. {{ number_format($netProfit, 2) }}</span>
                    </div>
                </div>
            </div>

        @elseif($type === 'cashier_closing')
            <div class="space-y-6 max-w-2xl">
                <h3 class="text-base font-bold text-slate-800">Cash Register & Drawer Audit ({{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }})</h3>
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 space-y-3 font-mono text-xs">
                    <div class="flex justify-between py-2 border-b border-slate-200">
                        <span>Opening Cash Balance</span>
                        <span class="font-bold text-indigo-700">Rs. {{ number_format($openingBalance, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-200 text-emerald-700 font-bold">
                        <span>(+) Cash Sales</span>
                        <span>+ Rs. {{ number_format($cashSales, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-200 text-blue-700 font-bold">
                        <span>(+) Cash Receipt Vouchers</span>
                        <span>+ Rs. {{ number_format($receiptVouchers, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-200 text-purple-700 font-bold">
                        <span>(-) Cash Purchases</span>
                        <span>- Rs. {{ number_format($cashPurchases, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-200 text-indigo-700 font-bold">
                        <span>(-) Cash Payment Vouchers</span>
                        <span>- Rs. {{ number_format($paymentVouchers, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-200 text-amber-700 font-bold">
                        <span>(-) Cash Expenses</span>
                        <span>- Rs. {{ number_format($cashExpenses, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-3 text-base font-black bg-slate-900 text-emerald-400 p-4 rounded-xl">
                        <span class="font-sans">EXPECTED DRAWER CASH</span>
                        <span>Rs. {{ number_format($expectedDrawerCash, 2) }}</span>
                    </div>
                </div>
            </div>

        @else
            <!-- Generic fallback view for all other reports (Aging, Party Ledger, etc.) -->
            <div class="space-y-4">
                <h3 class="text-base font-bold text-slate-800">{{ str_replace('_', ' ', strtoupper($type)) }} Report</h3>
                <div class="p-6 bg-slate-50 rounded-xl border border-slate-200 text-center">
                    <i class="fa-solid fa-file-invoice text-3xl text-slate-400 mb-2"></i>
                    <p class="text-xs text-slate-600 font-bold">Report parameters loaded successfully for {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

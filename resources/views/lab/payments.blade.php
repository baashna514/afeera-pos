@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ selectedTx: null, showModal: false }">
    <!-- Top Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
        <div>
            <div class="flex items-center gap-2.5">
                <h2 class="text-xl font-bold text-slate-800">Laboratory Payments & Reports</h2>
                @if($isOnline)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Connected
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold text-rose-700 bg-rose-50 border border-rose-200 rounded-full">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        Lab Connection Offline
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">Real-time payment synchronization & diagnostic test reports from Laboratory System (API Endpoint: <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700 font-mono text-[11px]">{{ $labApiUrl }}/fetch-payments</code>)</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('lab.payments') }}" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-2">
                <i class="fa-solid fa-rotate"></i>
                <span>Refresh Live Data</span>
            </a>
            <button onclick="window.print()" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-2">
                <i class="fa-solid fa-print"></i>
                <span>Print</span>
            </button>
        </div>
    </div>

    <!-- Error Alert if Connection Failed -->
    @if(!$isOnline && $errorMessage)
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-xs flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg mt-0.5 shrink-0"></i>
            <div>
                <h4 class="font-bold text-sm text-rose-900 mb-0.5">Laboratory System Connection Warning</h4>
                <p class="text-xs text-rose-700">{{ $errorMessage }}</p>
                <p class="text-[11px] text-rose-600 mt-2 font-medium">To resolve: Make sure the Laboratory system application is running on <code class="bg-rose-100 px-1 rounded font-mono">{{ $labApiUrl }}</code> (run <code class="bg-rose-100 px-1 rounded font-mono">php artisan serve --port=8000</code> in laboratory-system directory).</p>
            </div>
        </div>
    @endif

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Records Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Total Bookings</p>
                <h3 class="text-2xl font-black text-slate-800">{{ number_format($summary['total_records'] ?? 0) }}</h3>
                <p class="text-[11px] text-slate-500 mt-1 font-medium">Recorded Invoices</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-xl shrink-0">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>

        <!-- Total Collected Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Total Collected</p>
                <h3 class="text-2xl font-black text-emerald-600">Rs. {{ number_format($summary['total_collected'] ?? 0, 2) }}</h3>
                <p class="text-[11px] text-emerald-700 mt-1 font-medium">Actual Cash Received</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 text-xl shrink-0">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>

        <!-- Total Invoiced Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Total Billed</p>
                <h3 class="text-2xl font-black text-[#da1705]">Rs. {{ number_format($summary['total_invoiced'] ?? 0, 2) }}</h3>
                <p class="text-[11px] text-slate-500 mt-1 font-medium">Gross Invoiced Revenue</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-red-50 border border-red-100 flex items-center justify-center text-[#da1705] text-xl shrink-0">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
        </div>

        <!-- Total Tests Conducted Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tests Performed</p>
                <h3 class="text-2xl font-black text-purple-600">{{ number_format($summary['total_tests_conducted'] ?? 0) }}</h3>
                <p class="text-[11px] text-slate-500 mt-1 font-medium">Diagnostic Procedures</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 text-xl shrink-0">
                <i class="fa-solid fa-flask-vial"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Search Section -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs no-print">
        <form method="GET" action="{{ route('lab.payments') }}" class="flex flex-col md:flex-row items-end gap-3">
            <div class="w-full md:w-48">
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#da1705]">
            </div>
            <div class="w-full md:w-48">
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#da1705]">
            </div>
            <div class="w-full md:flex-1">
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Search Keywords</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by Invoice #, Patient name, Phone, Hospital..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#da1705]">
                </div>
            </div>
            <div class="flex items-center gap-2 w-full md:w-auto pt-2 md:pt-0">
                <button type="submit" class="w-full md:w-auto px-5 py-2 bg-[#da1705] hover:bg-[#b81204] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-filter"></i>
                    <span>Apply Filter</span>
                </button>
                @if($startDate || $endDate || $search)
                    <a href="{{ route('lab.payments') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center justify-center">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Main Transactions Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between flex-wrap gap-2">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-microscope text-[#da1705]"></i>
                <span>Laboratory Payments & Invoices</span>
            </h3>
            <span class="text-xs text-slate-500 font-semibold">{{ $transactions->count() }} Records Found</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-[11px] font-bold uppercase tracking-wider border-b border-slate-200">
                        <th class="px-5 py-3.5">Invoice #</th>
                        <th class="px-5 py-3.5">Booking Date</th>
                        <th class="px-5 py-3.5">Patient Details</th>
                        <th class="px-5 py-3.5">Hospital / Company</th>
                        <th class="px-5 py-3.5">Tests Performed</th>
                        <th class="px-5 py-3.5">Billed Amount</th>
                        <th class="px-5 py-3.5">Paid Amount</th>
                        <th class="px-5 py-3.5">Due Amount</th>
                        <th class="px-5 py-3.5">Payment Status</th>
                        <th class="px-5 py-3.5 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-3.5 font-extrabold text-slate-900 font-mono">
                                {{ $tx['invoice_number'] ?? 'N/A' }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 font-medium">
                                {{ $tx['booking_date'] ?? 'N/A' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-800">{{ $tx['patient']['name'] ?? 'Walk-in Patient' }}</div>
                                <div class="text-[11px] text-slate-400 font-medium">
                                    {{ $tx['patient']['phone'] ?? 'No Phone' }} 
                                    @if(isset($tx['patient']['gender'])) • {{ ucfirst($tx['patient']['gender']) }} @endif
                                    @if(isset($tx['patient']['age'])) ({{ $tx['patient']['age'] }} yrs) @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-slate-700">
                                {{ $tx['company']['name'] ?? 'General Lab' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-extrabold text-[11px] border border-indigo-100">
                                        {{ $tx['total_tests_count'] ?? count($tx['tests'] ?? []) }} Tests
                                    </span>
                                    @if(!empty($tx['tests']))
                                        <span class="text-slate-500 truncate max-w-[140px] text-[11px]" title="{{ implode(', ', array_column($tx['tests'], 'test_name')) }}">
                                            {{ $tx['tests'][0]['test_name'] ?? '' }}
                                            @if(count($tx['tests']) > 1)
                                                +{{ count($tx['tests']) - 1 }} more
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-extrabold text-slate-900 font-mono">
                                Rs. {{ number_format($tx['payment']['total_amount'] ?? 0, 2) }}
                                @if(($tx['payment']['discount'] ?? 0) > 0)
                                    <div class="text-[10px] text-emerald-600 font-medium">Disc: Rs. {{ number_format($tx['payment']['discount'], 2) }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-extrabold text-emerald-600 font-mono">
                                Rs. {{ number_format($tx['payment']['paid_amount'] ?? 0, 2) }}
                            </td>
                            <td class="px-5 py-3.5 font-bold font-mono {{ ($tx['payment']['due_amount'] ?? 0) > 0 ? 'text-[#da1705]' : 'text-slate-400' }}">
                                Rs. {{ number_format($tx['payment']['due_amount'] ?? 0, 2) }}
                            </td>
                            <td class="px-5 py-3.5">
                                @php
                                    $st = strtolower($tx['payment']['payment_status'] ?? 'unpaid');
                                @endphp
                                @if($st === 'paid')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800">
                                        PAID
                                    </span>
                                @elseif($st === 'partial')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800">
                                        PARTIAL
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800">
                                        UNPAID
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <button type="button" 
                                        @click="selectedTx = {{ json_encode($tx) }}; showModal = true"
                                        class="px-3 py-1.5 bg-slate-100 hover:bg-[#da1705] hover:text-white text-slate-700 font-bold text-xs rounded-lg transition duration-150 inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-eye text-[11px]"></i>
                                    <span>Details</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-flask-vial text-4xl mb-3 text-slate-300 block"></i>
                                <p class="text-sm font-semibold text-slate-600">No Laboratory Payments Found</p>
                                <p class="text-xs text-slate-400 mt-1">There are no diagnostic booking transactions matching your filter criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Transaction Detail Modal (Alpine.js) -->
    <div x-show="showModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-2xl w-full overflow-hidden" @click.away="showModal = false">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#da1705] flex items-center justify-center text-white">
                        <i class="fa-solid fa-receipt text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-sm text-white flex items-center gap-2">
                            <span>Lab Invoice:</span>
                            <span class="font-mono text-[#da1705]" x-text="selectedTx?.invoice_number"></span>
                        </h3>
                        <p class="text-[11px] text-slate-400" x-text="'Date: ' + (selectedTx?.booking_date || '')"></p>
                    </div>
                </div>
                <button type="button" @click="showModal = false" class="text-slate-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6 max-h-[75vh] overflow-y-auto space-y-5 text-xs">
                <!-- Patient & Hospital Info Grid -->
                <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Patient Details</p>
                        <p class="font-extrabold text-slate-900 text-sm" x-text="selectedTx?.patient?.name || 'Walk-in Patient'"></p>
                        <p class="text-slate-600 mt-0.5" x-text="'Phone: ' + (selectedTx?.patient?.phone || 'N/A')"></p>
                        <p class="text-slate-500" x-text="'Gender/Age: ' + (selectedTx?.patient?.gender || 'N/A') + ' / ' + (selectedTx?.patient?.age || 'N/A') + ' yrs'"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Hospital / Lab Facility</p>
                        <p class="font-extrabold text-slate-900 text-sm" x-text="selectedTx?.company?.name || 'General Laboratory'"></p>
                        <p class="text-slate-600 mt-0.5" x-text="'Facility Phone: ' + (selectedTx?.company?.phone || 'N/A')"></p>
                        <p class="text-slate-500" x-text="'Status: ' + (selectedTx?.payment?.booking_status || 'Completed')"></p>
                    </div>
                </div>

                <!-- Conducted Tests Table -->
                <div>
                    <h4 class="font-bold text-slate-800 text-xs mb-2 uppercase tracking-wider flex items-center justify-between">
                        <span>Conducted Diagnostic Tests</span>
                        <span class="text-indigo-600 font-extrabold" x-text="(selectedTx?.tests?.length || 0) + ' Test Items'"></span>
                    </h4>
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-100 text-slate-600 text-[10px] font-bold uppercase">
                                <tr>
                                    <th class="px-3.5 py-2">Test Name</th>
                                    <th class="px-3.5 py-2">Department</th>
                                    <th class="px-3.5 py-2">Barcode</th>
                                    <th class="px-3.5 py-2 text-right">Standard Price</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="t in (selectedTx?.tests || [])" :key="t.item_id">
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-3.5 py-2.5 font-bold text-slate-800" x-text="t.test_name"></td>
                                        <td class="px-3.5 py-2.5 text-slate-500 font-semibold" x-text="t.category || 'General'"></td>
                                        <td class="px-3.5 py-2.5 font-mono text-slate-600" x-text="t.barcode || 'N/A'"></td>
                                        <td class="px-3.5 py-2.5 text-right font-bold text-slate-800 font-mono" x-text="'Rs. ' + Number(t.price || 0).toFixed(2)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Financial Breakdown Card -->
                <div class="bg-slate-900 text-slate-100 p-4 rounded-2xl space-y-2 font-mono">
                    <div class="flex justify-between text-slate-400">
                        <span>Gross Total Amount:</span>
                        <span class="font-bold text-white" x-text="'Rs. ' + Number(selectedTx?.payment?.total_amount || 0).toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>Discount Offered:</span>
                        <span class="font-bold text-emerald-400" x-text="'- Rs. ' + Number(selectedTx?.payment?.discount || 0).toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-slate-400 border-t border-slate-800 pt-2 font-bold text-white">
                        <span>Net Payable:</span>
                        <span x-text="'Rs. ' + Number(selectedTx?.payment?.net_amount || 0).toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-emerald-400 font-bold">
                        <span>Paid Amount (Collected):</span>
                        <span x-text="'Rs. ' + Number(selectedTx?.payment?.paid_amount || 0).toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-[#da1705] font-bold border-t border-slate-800 pt-2 text-sm">
                        <span>Balance Due:</span>
                        <span x-text="'Rs. ' + Number(selectedTx?.payment?.due_amount || 0).toFixed(2)"></span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                <button type="button" @click="showModal = false" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition">
                    Close Details
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

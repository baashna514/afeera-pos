@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: '{{ $activeTab ?? 'features' }}',
    receipt: {
        paper_size: '{{ $settings['receipt.paper_size'] ?? '80mm' }}',
        show_logo: {{ !empty($settings['receipt.show_logo']) ? 'true' : 'false' }},
        show_customer_name: {{ !empty($settings['receipt.show_customer_name']) ? 'true' : 'false' }},
        show_cashier_name: {{ !empty($settings['receipt.show_cashier_name']) ? 'true' : 'false' }},
        show_tax_breakdown: {{ !empty($settings['receipt.show_tax_breakdown']) ? 'true' : 'false' }},
        show_barcode: {{ !empty($settings['receipt.show_barcode']) ? 'true' : 'false' }},
        show_qr_code: {{ !empty($settings['receipt.show_qr_code']) ? 'true' : 'false' }},
        show_sku: {{ !empty($settings['receipt.show_sku']) ? 'true' : 'false' }},
        header_text: `{{ addslashes($settings['receipt.header_text'] ?? 'Welcome to ' . ($company->name ?? 'SmartPOS')) }}`,
        footer_text: `{{ addslashes($settings['receipt.footer_text'] ?? 'Thank you for shopping with us! Please come again.') }}`,
        return_policy: `{{ addslashes($settings['receipt.return_policy'] ?? 'Items can be exchanged within 7 days with original receipt.') }}`
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-xs border border-slate-200">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white shadow-md shadow-indigo-100">
                <i class="fa-solid fa-sliders text-xl"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-slate-800 tracking-tight">System & Company Settings</h2>
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                        {{ $company->name ?? 'SmartPOS' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Customize company parameters, toggle active feature modules, design receipt templates, and configure POS defaults.</p>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-brand-50 border border-brand-200 flex items-center gap-3 text-brand-800 text-sm font-medium shadow-xs">
            <i class="fa-solid fa-circle-check text-brand-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs">
            <div class="flex items-center gap-2 font-bold mb-1">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                <span>Please correct the errors below:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2 overflow-x-auto">
        <button type="button" @click="activeTab = 'features'" 
                :class="activeTab === 'features' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-toggle-on text-sm"></i>
            <span>Feature Modules (On/Off)</span>
        </button>

        <button type="button" @click="activeTab = 'receipt'" 
                :class="activeTab === 'receipt' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-receipt text-sm"></i>
            <span>Receipt Template & Live Preview</span>
        </button>

        <button type="button" @click="activeTab = 'defaults'" 
                :class="activeTab === 'defaults' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-cash-register text-sm"></i>
            <span>POS & Default Values</span>
        </button>

        <button type="button" @click="activeTab = 'general'" 
                :class="activeTab === 'general' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-building text-sm"></i>
            <span>General Business Profile</span>
        </button>

        <button type="button" @click="activeTab = 'branding'" 
                :class="activeTab === 'branding' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-palette text-sm"></i>
            <span>Branding & Theme</span>
        </button>
    </div>

    <!-- TAB 1: FEATURE MODULES ON/OFF -->
    <div x-show="activeTab === 'features'" x-cloak class="space-y-6">
        <form action="{{ route('settings.features.update') }}" method="POST">
            @csrf
            <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-200 space-y-6">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Module & Attribute Visibility Control</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Disable modules that your business does not require. When disabled, links are removed from sidebars, fields become optional in product/order forms, and unused menus are hidden.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Brands Toggle -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-copyright text-base"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Product Brands</h4>
                                <p class="text-[11px] text-slate-500 leading-snug mt-0.5">Show Brand selection in product management and sidebar navigation.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                            <input type="checkbox" name="features_brands" value="1" class="sr-only peer" {{ company_setting('features.brands', true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        </label>
                    </div>

                    <!-- Categories Toggle -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-brand-100 text-brand-700 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-tags text-base"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Product Categories</h4>
                                <p class="text-[11px] text-slate-500 leading-snug mt-0.5">If disabled, Category is optional in forms and hidden from menu.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                            <input type="checkbox" name="features_categories" value="1" class="sr-only peer" {{ company_setting('features.categories', true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        </label>
                    </div>

                    <!-- Sale Orders Toggle -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-cart-flatbed text-base"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Sale Orders (Booking)</h4>
                                <p class="text-[11px] text-slate-500 leading-snug mt-0.5">Enable advance sales quotation & booking orders before final invoicing.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                            <input type="checkbox" name="features_sale_orders" value="1" class="sr-only peer" {{ company_setting('features.sale_orders', true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        </label>
                    </div>

                    <!-- Purchase Orders Toggle -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-clipboard-list text-base"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Purchase Orders</h4>
                                <p class="text-[11px] text-slate-500 leading-snug mt-0.5">Vendor purchase ordering workflow and conversion to purchase bill.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                            <input type="checkbox" name="features_purchase_orders" value="1" class="sr-only peer" {{ company_setting('features.purchase_orders', true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        </label>
                    </div>

                    <!-- Warehouses / Locations Toggle -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-warehouse text-base"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Warehouses / Multi-location</h4>
                                <p class="text-[11px] text-slate-500 leading-snug mt-0.5">Enable multiple warehouse tracking and stock distribution.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                            <input type="checkbox" name="features_warehouses" value="1" class="sr-only peer" {{ company_setting('features.warehouses', true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        </label>
                    </div>

                    <!-- Stock Transfers Toggle -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-right-left text-base"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Internal Stock Transfers</h4>
                                <p class="text-[11px] text-slate-500 leading-snug mt-0.5">Inter-warehouse stock transfer entries and tracking.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                            <input type="checkbox" name="features_stock_transfers" value="1" class="sr-only peer" {{ company_setting('features.stock_transfers', true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        </label>
                    </div>

                    <!-- Vouchers (Cash Receipts & Payments) -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-money-bill-transfer text-base"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Cash & Payment Vouchers</h4>
                                <p class="text-[11px] text-slate-500 leading-snug mt-0.5">Standalone receipts and payments vouchers outside of invoices.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                            <input type="checkbox" name="features_vouchers" value="1" class="sr-only peer" {{ company_setting('features.vouchers', true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        </label>
                    </div>

                    <!-- Expenses Toggle -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-receipt text-base"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Operating Expenses</h4>
                                <p class="text-[11px] text-slate-500 leading-snug mt-0.5">Daily store expenses, utilities, bills, and expense categories.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                            <input type="checkbox" name="features_expenses" value="1" class="sr-only peer" {{ company_setting('features.expenses', true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        </label>
                    </div>

                    <!-- Day Book / Daily Cash Book -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-brand-100 text-brand-700 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-cash-register text-base"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Day Book / Cash Register</h4>
                                <p class="text-[11px] text-slate-500 leading-snug mt-0.5">Opening balance, counter cash drawer tally, and daily summaries.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                            <input type="checkbox" name="features_day_book" value="1" class="sr-only peer" {{ company_setting('features.day_book', true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        </label>
                    </div>

                    <!-- Sale Returns Toggle -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-arrow-rotate-left text-base"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Customer Sale Returns</h4>
                                <p class="text-[11px] text-slate-500 leading-snug mt-0.5">Enable sales returns, credit note generation, and restock tracking.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                            <input type="checkbox" name="features_sale_returns" value="1" class="sr-only peer" {{ company_setting('features.sale_returns', true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        </label>
                    </div>

                    <!-- Purchase Returns Toggle -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-orange-100 text-orange-700 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-truck-ramp-box text-base"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Vendor Purchase Returns</h4>
                                <p class="text-[11px] text-slate-500 leading-snug mt-0.5">Return damaged or surplus items back to vendors with ledger debit.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                            <input type="checkbox" name="features_purchase_returns" value="1" class="sr-only peer" {{ company_setting('features.purchase_returns', true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        </label>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Feature Toggles</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- TAB 2: RECEIPT TEMPLATE WITH INTERACTIVE LIVE PREVIEW -->
    <div x-show="activeTab === 'receipt'" x-cloak class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Configuration Form (7 cols) -->
        <div class="lg:col-span-7 bg-white p-6 rounded-2xl shadow-xs border border-slate-200 space-y-6">
            <div>
                <h3 class="text-base font-bold text-slate-800">Thermal Receipt & Print Designer</h3>
                <p class="text-xs text-slate-500 mt-0.5">Configure what prints on POS slips and watch the Live Preview react immediately on the right.</p>
            </div>

            <form action="{{ route('settings.receipt.update') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Paper Size -->
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">Paper Size & Roll Width</label>
                    <select name="paper_size" x-model="receipt.paper_size" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        <option value="80mm">80 mm Standard Thermal Roll (Recommended)</option>
                        <option value="58mm">58 mm Compact Mini Thermal Roll</option>
                        <option value="a4">A4 Full Sheet Standard Invoice</option>
                    </select>
                </div>

                <!-- What to Show Checkboxes -->
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-3">Receipt Header & Elements</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="show_logo" x-model="receipt.show_logo" class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                            <span class="text-xs font-semibold text-slate-700">Print Logo / Icon at top</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="show_customer_name" x-model="receipt.show_customer_name" class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                            <span class="text-xs font-semibold text-slate-700">Print Customer Name + Phone</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="show_cashier_name" x-model="receipt.show_cashier_name" class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                            <span class="text-xs font-semibold text-slate-700">Print Cashier / Staff Name</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="show_tax_breakdown" x-model="receipt.show_tax_breakdown" class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                            <span class="text-xs font-semibold text-slate-700">Print Tax / GST Breakdown</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="show_barcode" x-model="receipt.show_barcode" class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                            <span class="text-xs font-semibold text-slate-700">Print Sale Invoice Barcode</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="show_qr_code" x-model="receipt.show_qr_code" class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                            <span class="text-xs font-semibold text-slate-700">Print Verification QR Code</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="show_sku" x-model="receipt.show_sku" class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                            <span class="text-xs font-semibold text-slate-700">Print SKU / Item Code</span>
                        </label>
                    </div>
                </div>

                <!-- Custom Texts -->
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-1">Receipt Header Greeting Note</label>
                        <input type="text" name="header_text" x-model="receipt.header_text" placeholder="e.g. Welcome to our store!" 
                               class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-1">Footer Message</label>
                        <input type="text" name="footer_text" x-model="receipt.footer_text" placeholder="e.g. Thank you for your business!" 
                               class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-1">Return / Exchange Policy Text</label>
                        <textarea name="return_policy" x-model="receipt.return_policy" rows="2" placeholder="e.g. Goods once sold cannot be refunded without invoice. 7 days exchange." 
                                  class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"></textarea>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Receipt Template</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Real-Time Interactive Live Preview (5 cols) -->
        <div class="lg:col-span-5 flex flex-col items-center">
            <div class="w-full mb-2 flex items-center justify-between px-2 text-xs font-bold text-slate-500 uppercase tracking-wider">
                <span class="flex items-center gap-1.5">
                    <i class="fa-solid fa-eye text-indigo-500"></i> Live Thermal Slip Preview
                </span>
                <span class="font-mono text-[10px] bg-slate-200 text-slate-700 px-2 py-0.5 rounded" x-text="receipt.paper_size"></span>
            </div>

            <!-- Receipt Card Container -->
            <div class="w-full bg-slate-900/90 p-4 rounded-2xl shadow-xl flex justify-center overflow-x-auto min-h-[520px]">
                <div class="bg-white text-slate-900 font-mono text-[11px] p-5 shadow-2xl transition-all duration-300 rounded-sm"
                     :style="receipt.paper_size === '58mm' ? 'width: 260px;' : (receipt.paper_size === 'a4' ? 'width: 440px;' : 'width: 320px;')">
                    
                    <!-- Top Logo -->
                    <div x-show="receipt.show_logo" class="text-center mb-2">
                        <div class="w-10 h-10 mx-auto rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-store"></i>
                        </div>
                    </div>

                    <!-- Store Details -->
                    <div class="text-center mb-2 border-b border-dashed border-slate-400 pb-2">
                        <div class="font-black text-sm uppercase tracking-wider">{{ $company->name ?? 'SMART POS GENERAL TRADING' }}</div>
                        <div class="text-[10px] text-slate-600 leading-tight mt-0.5">{{ $company->address ?? 'Main Commercial Market, Plaza #4, Ground Floor' }}</div>
                        <div class="text-[10px] text-slate-600 mt-0.5">Phone: {{ $company->phone ?? '+92 300 1234567' }}</div>
                        <div x-show="receipt.header_text" class="text-[10px] font-semibold text-slate-700 mt-1 italic" x-text="receipt.header_text"></div>
                    </div>

                    <!-- Receipt Metadata -->
                    <div class="text-[10px] space-y-0.5 mb-2 border-b border-dashed border-slate-400 pb-2">
                        <div class="flex justify-between">
                            <span>INV #: <strong>INV-2026-0089</strong></span>
                            <span>{{ date('d-m-Y H:i') }}</span>
                        </div>
                        <div x-show="receipt.show_customer_name" class="flex justify-between">
                            <span>Customer: <strong>Walk-in Customer</strong></span>
                            <span>0321-7654321</span>
                        </div>
                        <div x-show="receipt.show_cashier_name" class="flex justify-between">
                            <span>Cashier: <strong>{{ auth()->user()->name ?? 'Admin Staff' }}</strong></span>
                            <span>POS #01</span>
                        </div>
                    </div>

                    <!-- Line Items Table -->
                    <div class="mb-2 border-b border-dashed border-slate-400 pb-2">
                        <div class="flex justify-between font-bold text-[10px] uppercase border-b border-slate-300 pb-1 mb-1">
                            <span class="w-1/2">Item Description</span>
                            <span class="w-1/4 text-center">Qty x Rate</span>
                            <span class="w-1/4 text-right">Amount</span>
                        </div>
                        <div class="space-y-1 text-[10px]">
                            <div>
                                <div class="flex justify-between">
                                    <span class="w-1/2 font-semibold">Basmati Rice Super 5kg</span>
                                    <span class="w-1/4 text-center">1 x 1,850</span>
                                    <span class="w-1/4 text-right font-bold">1,850.00</span>
                                </div>
                                <div x-show="receipt.show_sku" class="text-[9px] text-slate-500">SKU: RIC-5KG-01</div>
                            </div>
                            <div>
                                <div class="flex justify-between">
                                    <span class="w-1/2 font-semibold">Cooking Oil 1L Pouch</span>
                                    <span class="w-1/4 text-center">2 x 520</span>
                                    <span class="w-1/4 text-right font-bold">1,040.00</span>
                                </div>
                                <div x-show="receipt.show_sku" class="text-[9px] text-slate-500">SKU: OIL-1L-04</div>
                            </div>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="space-y-1 text-[10px] mb-2 border-b border-dashed border-slate-400 pb-2">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>Rs. 2,890.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Discount:</span>
                            <span>- Rs. 90.00</span>
                        </div>
                        <div x-show="receipt.show_tax_breakdown" class="flex justify-between text-slate-600">
                            <span>Sales Tax (0%):</span>
                            <span>Rs. 0.00</span>
                        </div>
                        <div class="flex justify-between text-xs font-black pt-1 border-t border-slate-200">
                            <span>NET PAYABLE:</span>
                            <span class="text-sm font-black">Rs. 2,800.00</span>
                        </div>
                        <div class="flex justify-between text-[10px]">
                            <span>Paid Amount (Cash):</span>
                            <span class="font-bold">Rs. 3,000.00</span>
                        </div>
                        <div class="flex justify-between text-[10px]">
                            <span>Change Returned:</span>
                            <span class="font-bold">Rs. 200.00</span>
                        </div>
                    </div>

                    <!-- Barcode Display -->
                    <div x-show="receipt.show_barcode" class="text-center my-2">
                        <div class="inline-block py-1 tracking-widest font-mono text-[9px] bg-slate-100 px-3 border border-slate-300 rounded">
                            |||||| | |||||||| |||| | ||||||
                        </div>
                        <div class="text-[8px] text-slate-500 mt-0.5">INV-2026-0089</div>
                    </div>

                    <!-- QR Code Display -->
                    <div x-show="receipt.show_qr_code" class="text-center my-2">
                        <div class="w-16 h-16 mx-auto bg-slate-100 border border-slate-300 flex items-center justify-center text-slate-700 text-xs">
                            <i class="fa-solid fa-qrcode text-3xl"></i>
                        </div>
                        <div class="text-[8px] text-slate-500 mt-0.5">Scan to verify invoice</div>
                    </div>

                    <!-- Footer & Policy -->
                    <div class="text-center text-[9px] text-slate-600 space-y-1 pt-1">
                        <div x-show="receipt.footer_text" class="font-semibold text-slate-800" x-text="receipt.footer_text"></div>
                        <div x-show="receipt.return_policy" class="text-[8px] text-slate-500 italic leading-tight" x-text="receipt.return_policy"></div>
                        <div class="text-[8px] text-slate-400">Powered by SmartPOS</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: POS & DEFAULT VALUES -->
    <div x-show="activeTab === 'defaults'" x-cloak class="space-y-6">
        <form action="{{ route('settings.defaults.update') }}" method="POST">
            @csrf
            <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-200 space-y-6 max-w-4xl">
                <div>
                    <h3 class="text-base font-bold text-slate-800">POS & Default Operational Values</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Set system defaults so checkout and inventory operations require fewer clicks.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Default Customer -->
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">Default Walk-in Customer</label>
                        <select name="default_customer_id" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                            <option value="">None / Require Selection</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}" {{ ($settings['defaults.customer_id'] ?? '') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->phone ?? 'No Phone' }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Pre-selected customer when launching the POS screen.</p>
                    </div>

                    <!-- Default Vendor -->
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">Default Vendor</label>
                        <select name="default_vendor_id" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                            <option value="">None / Manual Selection</option>
                            @foreach ($vendors as $v)
                                <option value="{{ $v->id }}" {{ ($settings['defaults.vendor_id'] ?? '') == $v->id ? 'selected' : '' }}>
                                    {{ $v->name }} ({{ $v->phone ?? 'No Phone' }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Default vendor pre-filled on purchase order and invoice forms.</p>
                    </div>

                    <!-- Default Warehouse -->
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">Default Warehouse / Location</label>
                        <select name="default_warehouse_id" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                            <option value="">Primary Location (Auto)</option>
                            @foreach ($warehouses as $w)
                                <option value="{{ $w->id }}" {{ ($settings['defaults.warehouse_id'] ?? '') == $w->id ? 'selected' : '' }}>
                                    {{ $w->name }} ({{ $w->code ?? 'WH-'.$w->id }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Default location used for sales deductions and stock receiving.</p>
                    </div>

                    <!-- Default Payment Method -->
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">Default Payment</label>
                        <select name="default_payment_method" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                            <option value="cash" {{ ($settings['defaults.payment_method'] ?? 'cash') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="card" {{ ($settings['defaults.payment_method'] ?? '') === 'card' ? 'selected' : '' }}>Debit / Credit Card</option>
                            <option value="bank_transfer" {{ ($settings['defaults.payment_method'] ?? '') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer / Online</option>
                            <option value="credit" {{ ($settings['defaults.payment_method'] ?? '') === 'credit' ? 'selected' : '' }}>Store Credit / Udhaar</option>
                        </select>
                    </div>

                    <!-- Allow Less Sale -->
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">Allow Less Sale *</label>
                        <select name="pos_allow_less_sale" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                            <option value="no" {{ ($settings['pos.allow_less_sale'] ?? 'no') === 'no' ? 'selected' : '' }}>No</option>
                            <option value="yes" {{ ($settings['pos.allow_less_sale'] ?? '') === 'yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <!-- POS Total Payable Type -->
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">POS Total Payable Type *</label>
                        <select name="pos_total_payable_type" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                            <option value="none" {{ ($settings['pos.total_payable_type'] ?? 'none') === 'none' ? 'selected' : '' }}>None</option>
                            <option value="rounded" {{ ($settings['pos.total_payable_type'] ?? '') === 'rounded' ? 'selected' : '' }}>Rounded</option>
                            <option value="exact" {{ ($settings['pos.total_payable_type'] ?? '') === 'exact' ? 'selected' : '' }}>Exact</option>
                        </select>
                    </div>

                    <!-- Default Cursor Position -->
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">Default Cursor Position *</label>
                        <select name="pos_default_cursor" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                            <option value="search_box" {{ ($settings['pos.default_cursor'] ?? 'search_box') === 'search_box' ? 'selected' : '' }}>Search Box</option>
                            <option value="barcode" {{ ($settings['pos.default_cursor'] ?? '') === 'barcode' ? 'selected' : '' }}>Barcode Input</option>
                        </select>
                    </div>

                    <!-- Product Display -->
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">Product Display *</label>
                        <select name="pos_product_display" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                            <option value="image_view" {{ ($settings['pos.product_display'] ?? 'image_view') === 'image_view' ? 'selected' : '' }}>Image View</option>
                            <option value="list_view" {{ ($settings['pos.product_display'] ?? '') === 'list_view' ? 'selected' : '' }}>List View</option>
                        </select>
                    </div>

                    <!-- Onscreen Keyboard Status -->
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">Onscreen Keyboard Status *</label>
                        <select name="pos_onscreen_keyboard" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                            <option value="disable" {{ ($settings['pos.onscreen_keyboard'] ?? 'disable') === 'disable' ? 'selected' : '' }}>Disable</option>
                            <option value="enable" {{ ($settings['pos.onscreen_keyboard'] ?? '') === 'enable' ? 'selected' : '' }}>Enable</option>
                        </select>
                    </div>

                    <!-- Grocery Experience -->
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">Grocery Experience *</label>
                        <select name="pos_grocery_experience" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                            <option value="medicine" {{ ($settings['pos.grocery_experience'] ?? 'medicine') === 'medicine' ? 'selected' : '' }}>Medicine</option>
                            <option value="retail" {{ ($settings['pos.grocery_experience'] ?? '') === 'retail' ? 'selected' : '' }}>Retail</option>
                            <option value="grocery" {{ ($settings['pos.grocery_experience'] ?? '') === 'grocery' ? 'selected' : '' }}>Grocery</option>
                        </select>
                    </div>

                    <!-- SMTP Default Selected in POS -->
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">SMTP Default Selected in POS *</label>
                        <select name="pos_smtp_default" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                            <option value="no" {{ ($settings['pos.smtp_default'] ?? 'no') === 'no' ? 'selected' : '' }}>No</option>
                            <option value="yes" {{ ($settings['pos.smtp_default'] ?? '') === 'yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <!-- SMS Default Selected in POS -->
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">SMS Default Selected in POS *</label>
                        <select name="pos_sms_default" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                            <option value="no" {{ ($settings['pos.sms_default'] ?? 'no') === 'no' ? 'selected' : '' }}>No</option>
                            <option value="yes" {{ ($settings['pos.sms_default'] ?? '') === 'yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <!-- Whatsapp Default Selected in POS -->
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">Whatsapp Default Selected in POS *</label>
                        <select name="pos_whatsapp_default" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                            <option value="no" {{ ($settings['pos.whatsapp_default'] ?? 'no') === 'no' ? 'selected' : '' }}>No</option>
                            <option value="yes" {{ ($settings['pos.whatsapp_default'] ?? '') === 'yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <!-- Direct Cart -->
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-2">Direct Cart *</label>
                        <div class="flex items-center gap-4 mt-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="pos_direct_cart" value="yes" {{ ($settings['pos.direct_cart'] ?? 'yes') === 'yes' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm">Yes</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="pos_direct_cart" value="no" {{ ($settings['pos.direct_cart'] ?? 'yes') === 'no' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm">No</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Defaults</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- TAB 4: GENERAL BUSINESS PROFILE -->
    <div x-show="activeTab === 'general'" x-cloak class="space-y-6">
        <form action="{{ route('settings.general.update') }}" method="POST">
            @csrf
            <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-200 space-y-6 max-w-4xl">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Company & Business Information</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Update legal company name, contact numbers, and store address shown on receipts and tax invoices.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-1">Company / Store Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $company->name ?? '') }}" required
                               class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-1">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $company->phone ?? '') }}"
                               class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $company->email ?? '') }}"
                               class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-1">Currency Code <span class="text-rose-500">*</span></label>
                        <input type="text" name="currency" value="{{ old('currency', $company->currency ?? 'PKR') }}" required
                               class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white uppercase">
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-1">Currency Symbol</label>
                        <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings['general.currency_symbol'] ?? 'Rs.') }}"
                               class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-1">Store / Business Address</label>
                        <textarea name="address" rows="3" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">{{ old('address', $company->address ?? '') }}</textarea>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Business Profile</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- TAB 5: BRANDING & THEME -->
    <div x-show="activeTab === 'branding'" x-cloak class="space-y-6">
        <form action="{{ route('settings.branding.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-200 space-y-6 max-w-4xl">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Branding & Theme</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Make the admin yours — app name, logos, accent color, favicon, and custom styling.</p>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-1">App Name</label>
                        <input type="text" name="app_name" value="{{ old('app_name', $settings['branding.app_name'] ?? '') }}" placeholder="Hyper POS" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        <p class="text-[11px] text-slate-400 mt-1">Shown in the browser title, sidebar, and login page. Independent of the company name on receipts.</p>
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-1">Footer Text</label>
                        <input type="text" name="footer_text" value="{{ old('footer_text', $settings['branding.footer_text'] ?? '') }}" placeholder="© 2026 Hyper POS. All rights reserved." class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                        <p class="text-[11px] text-slate-400 mt-1">A short line shown at the bottom of every admin page.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-1">App Logo (Light Mode)</label>
                            <input type="file" name="logo_light" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" accept="image/*">
                            @if(isset($settings['branding.logo_light']))
                                <div class="mt-2 p-2 border border-slate-200 rounded bg-white inline-block">
                                    <img src="{{ Storage::url($settings['branding.logo_light']) }}" alt="Logo Light" class="h-10">
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-1">App Logo (Dark Mode)</label>
                            <input type="file" name="logo_dark" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" accept="image/*">
                            @if(isset($settings['branding.logo_dark']))
                                <div class="mt-2 p-2 border border-slate-200 rounded bg-slate-800 inline-block">
                                    <img src="{{ Storage::url($settings['branding.logo_dark']) }}" alt="Logo Dark" class="h-10">
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-1">Favicon</label>
                            <input type="file" name="favicon" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white" accept=".png,.jpg,.jpeg,.ico">
                            @if(isset($settings['branding.favicon']))
                                <div class="mt-2 p-2 border border-slate-200 rounded bg-white inline-block">
                                    <img src="{{ Storage::url($settings['branding.favicon']) }}" alt="Favicon" class="h-8 w-8">
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-1">Accent Color</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="accent_color" value="{{ old('accent_color', $settings['branding.accent_color'] ?? '#4f46e5') }}" class="h-10 w-14 rounded cursor-pointer border border-slate-200">
                                <span class="text-sm text-slate-600 font-mono" x-text="$el.previousElementSibling.value"></span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 block mb-1">Custom CSS</label>
                        <textarea name="custom_css" rows="4" placeholder="/* Add your custom styles here */" class="w-full px-4 py-2.5 text-sm font-mono bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white">{{ old('custom_css', $settings['branding.custom_css'] ?? '') }}</textarea>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Branding Settings</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - SmartPOS</title>

    <!-- Tailwind CSS & Font Awesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <!-- SweetAlert2 & jQuery & Chart.js & Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <script>
        if (typeof tailwind !== 'undefined') {
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            brand: {
                                50: '#fef2f2',
                                100: '#fee2e2',
                                500: '#da1705',
                                600: '#da1705',
                                700: '#b81204',
                                800: '#960f03',
                                900: '#780c02',
                            }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        @media print {
            .no-print { display: none !important; }
        }
        /* Fallback Core Layout Styles in case CDN script takes time */
        body { margin: 0; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans">
    <div x-data="{ mobileMenuOpen: false }" class="min-h-screen flex flex-col md:flex-row relative">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="mobileMenuOpen" x-cloak @click="mobileMenuOpen = false" class="fixed inset-0 bg-slate-900/60 z-40 md:hidden backdrop-blur-xs transition-opacity"></div>

        <!-- Sidebar Navigation -->
        <aside :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'" 
               x-data="{
                   openInventory: {{ request()->routeIs('products.*', 'categories.*', 'units.*', 'warehouses.*', 'brands.*', 'stock-transfers.*') ? 'true' : 'false' }},
                   openSales: {{ request()->routeIs('sale-orders.*', 'sales.*', 'sale-returns.*') ? 'true' : 'false' }},
                   openPurchases: {{ request()->routeIs('purchase-orders.*', 'purchases.*', 'purchase-returns.*') ? 'true' : 'false' }},
                   openAccounts: {{ request()->routeIs('day-book.*', 'ledgers.*', 'vouchers.*', 'expenses.*', 'expense-categories.*') ? 'true' : 'false' }},
                   openPeople: {{ request()->routeIs('customers.*', 'vendors.*') ? 'true' : 'false' }},
                   openAccess: {{ request()->routeIs('users.*', 'roles.*', 'permissions.*', 'settings.*') ? 'true' : 'false' }}
               }"
               class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-200 flex-shrink-0 flex flex-col no-print shadow-xl transition-transform duration-300 ease-in-out">
            <!-- Brand -->
            @php
                $userCompany = auth()->user()?->company;
                $sidebarLogo = $userCompany?->logo 
                    ?? company_setting('branding.logo_dark') 
                    ?? company_setting('branding.logo_light');
                $brandTitle = $userCompany?->name ?? company_setting('general.app_name', 'SmartPOS');
                $defaultLogoExists = file_exists(public_path('images/logo.png'));
            @endphp
            <div class="h-16 flex items-center justify-between px-4 bg-slate-950 border-b border-slate-800">
                <a href="{{ auth()->user()?->isOwner() ? route('owner.dashboard') : route('dashboard') }}" class="flex items-center gap-3 min-w-0 overflow-hidden">
                    @if($sidebarLogo)
                        <div class="h-10 max-w-[130px] flex items-center shrink-0">
                            <img src="{{ asset('storage/' . $sidebarLogo) }}" alt="{{ $brandTitle }}" class="max-h-10 max-w-[130px] object-contain">
                        </div>
                        <div class="min-w-0 truncate">
                            <span class="text-xs font-black tracking-tight text-white block truncate" title="{{ $brandTitle }}">{{ $brandTitle }}</span>
                            <span class="block text-[9px] text-[#da1705] font-bold uppercase tracking-wider">POS Portal</span>
                        </div>
                    @elseif($defaultLogoExists)
                        <div class="w-9 h-9 flex items-center justify-center shrink-0">
                            <img src="{{ asset('images/logo.png') }}" alt="{{ $brandTitle }}" class="w-9 h-9 object-contain drop-shadow-md">
                        </div>
                        <div class="min-w-0 truncate">
                            <span class="text-base font-black tracking-wider text-white block truncate">{{ $brandTitle }}</span>
                            <span class="block text-[10px] text-[#da1705] -mt-0.5 font-bold truncate">Retail & Inventory</span>
                        </div>
                    @else
                        <div class="w-9 h-9 rounded-lg bg-[#da1705] flex items-center justify-center text-white font-black shadow-lg shadow-[#da1705]/30 shrink-0">
                            <i class="fa-solid fa-cash-register text-lg"></i>
                        </div>
                        <div class="min-w-0 truncate">
                            <span class="text-base font-black tracking-wider text-white block truncate">{{ $brandTitle }}</span>
                            <span class="block text-[10px] text-slate-400 -mt-0.5 font-medium truncate">Retail & Inventory</span>
                        </div>
                    @endif
                </a>
                <button @click="mobileMenuOpen = false" class="md:hidden text-slate-400 hover:text-white p-1 ml-2 shrink-0">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- POS Terminal Quick Launch (Hidden for Owner) -->
            @if(!auth()->user()?->isOwner() && auth()->user()?->hasPermission('pos.access'))
                <div class="p-4 border-b border-slate-800/80">
                    <a href="{{ route('pos.index') }}" 
                       class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-[#da1705] to-[#b81204] hover:from-[#c21404] hover:to-[#9f0f03] text-white font-bold rounded-xl shadow-lg shadow-[#da1705]/25 transition duration-200 group text-xs">
                        <i class="fa-solid fa-cart-shopping text-sm group-hover:scale-110 transition-transform"></i>
                        <span>Open POS Terminal</span>
                    </a>
                </div>
            @endif

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-2 overflow-y-auto">
                @if(auth()->user()?->isOwner())
                    <!-- Dedicated Owner Platform Menu: Companies & Tenants -->
                    <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-indigo-400 mb-2 flex items-center gap-1.5">
                        <i class="fa-solid fa-crown text-[10px]"></i> Platform Administration
                    </p>

                    <a href="{{ route('owner.dashboard') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition {{ request()->routeIs('owner.*') ? 'bg-gradient-to-r from-indigo-600 to-indigo-700 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-building w-5 text-center text-indigo-400 text-sm"></i>
                        <span>Companies & Tenants</span>
                    </a>
                @else
                    <!-- Tenant Company Operations Menu -->
                    <div class="space-y-1">
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">Main Menu</p>
                        
                        @if(auth()->user()?->hasPermission('dashboard.view'))
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-[#da1705] text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-chart-pie w-5 text-center text-slate-400 {{ request()->routeIs('dashboard') ? 'text-white' : '' }}"></i>
                                <span>Dashboard</span>
                            </a>
                        @endif

                        @if(auth()->user()?->hasPermission('stock.view'))
                            <a href="{{ route('stock.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('stock.*') ? 'bg-[#da1705] text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-boxes-stacked w-5 text-center text-slate-400 {{ request()->routeIs('stock.*') ? 'text-white' : '' }}"></i>
                                <span>Stock Overview</span>
                            </a>
                        @endif
                    </div>

                    <!-- Inventory Dropdown -->
                    @if(auth()->user()?->hasPermission('products.view') || auth()->user()?->hasPermission('categories.view') || auth()->user()?->hasPermission('units.view'))
                        <div class="pt-1">
                            <button @click="openInventory = !openInventory" 
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-box-archive w-5 text-center text-slate-400"></i>
                                    <span>Inventory</span>
                                </div>
                                <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200 text-slate-500" :class="openInventory ? 'rotate-180 text-[#da1705]' : ''"></i>
                            </button>
                            <div x-show="openInventory" x-cloak class="mt-1 pl-4 space-y-1 border-l-2 border-slate-800 ml-5">
                                @if(auth()->user()?->hasPermission('products.view'))
                                    <a href="{{ route('products.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('products.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-box-open text-[11px] w-4 text-center"></i>
                                        <span>Products</span>
                                    </a>
                                @endif

                                @if(company_has_feature('categories') && auth()->user()?->hasPermission('categories.view'))
                                    <a href="{{ route('categories.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('categories.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-tags text-[11px] w-4 text-center"></i>
                                        <span>Categories</span>
                                    </a>
                                @endif

                                @if(auth()->user()?->hasPermission('units.view'))
                                    <a href="{{ route('units.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('units.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-scale-balanced text-[11px] w-4 text-center"></i>
                                        <span>Units & Conversion</span>
                                    </a>
                                @endif

                                @if(company_has_feature('warehouses'))
                                    <a href="{{ route('warehouses.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('warehouses.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-warehouse text-[11px] w-4 text-center"></i>
                                        <span>Warehouses</span>
                                    </a>
                                @endif

                                @if(company_has_feature('brands'))
                                    <a href="{{ route('brands.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('brands.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-copyright text-[11px] w-4 text-center"></i>
                                        <span>Brands</span>
                                    </a>
                                @endif

                                @if(company_has_feature('stock_transfers'))
                                    <a href="{{ route('stock-transfers.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('stock-transfers.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-right-left text-[11px] w-4 text-center"></i>
                                        <span>Stock Transfers</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Sales Dropdown -->
                    @if((company_has_feature('sale_orders') && auth()->user()?->hasPermission('sale_orders.view')) || auth()->user()?->hasPermission('sales.view') || (company_has_feature('sale_returns') && auth()->user()?->hasPermission('sale_returns.view')))
                        <div class="pt-1">
                            <button @click="openSales = !openSales" 
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-cart-shopping w-5 text-center text-slate-400"></i>
                                    <span>Sales</span>
                                </div>
                                <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200 text-slate-500" :class="openSales ? 'rotate-180 text-[#da1705]' : ''"></i>
                            </button>
                            <div x-show="openSales" x-cloak class="mt-1 pl-4 space-y-1 border-l-2 border-slate-800 ml-5">
                                @if(company_has_feature('sale_orders') && auth()->user()?->hasPermission('sale_orders.view'))
                                    <a href="{{ route('sale-orders.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('sale-orders.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-cart-flatbed text-[11px] w-4 text-center"></i>
                                        <span>Sale Orders</span>
                                    </a>
                                @endif

                                @if(auth()->user()?->hasPermission('sales.view'))
                                    <a href="{{ route('sales.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('sales.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-receipt text-[11px] w-4 text-center"></i>
                                        <span>Sale Invoices</span>
                                    </a>
                                @endif

                                @if(company_has_feature('sale_returns') && auth()->user()?->hasPermission('sale_returns.view'))
                                    <a href="{{ route('sale-returns.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('sale-returns.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-arrow-rotate-left text-[11px] w-4 text-center"></i>
                                        <span>Sale Returns</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Purchases Dropdown -->
                    @if((company_has_feature('purchase_orders') && auth()->user()?->hasPermission('purchase_orders.view')) || auth()->user()?->hasPermission('purchases.view') || (company_has_feature('purchase_returns') && auth()->user()?->hasPermission('purchase_returns.view')))
                        <div class="pt-1">
                            <button @click="openPurchases = !openPurchases" 
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-truck-ramp-box w-5 text-center text-slate-400"></i>
                                    <span>Purchases</span>
                                </div>
                                <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200 text-slate-500" :class="openPurchases ? 'rotate-180 text-[#da1705]' : ''"></i>
                            </button>
                            <div x-show="openPurchases" x-cloak class="mt-1 pl-4 space-y-1 border-l-2 border-slate-800 ml-5">
                                @if(company_has_feature('purchase_orders') && auth()->user()?->hasPermission('purchase_orders.view'))
                                    <a href="{{ route('purchase-orders.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('purchase-orders.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-clipboard-list text-[11px] w-4 text-center"></i>
                                        <span>Purchase Orders</span>
                                    </a>
                                @endif

                                @if(auth()->user()?->hasPermission('purchases.view'))
                                    <a href="{{ route('purchases.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('purchases.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-bag-shopping text-[11px] w-4 text-center"></i>
                                        <span>Purchase Invoices</span>
                                    </a>
                                @endif

                                @if(company_has_feature('purchase_returns') && auth()->user()?->hasPermission('purchase_returns.view'))
                                    <a href="{{ route('purchase-returns.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('purchase-returns.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-arrow-rotate-left text-[11px] w-4 text-center"></i>
                                        <span>Purchase Returns</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Chart Of Accounts Dropdown -->
                    @if((company_has_feature('day_book') && (auth()->user()?->hasPermission('day_book.view') || auth()->user()?->isSuperAdmin())) || auth()->user()?->hasPermission('ledgers.customer') || auth()->user()?->hasPermission('ledgers.vendor') || (company_has_feature('vouchers')) || (company_has_feature('expenses') && auth()->user()?->hasPermission('expenses.view')) || auth()->user()?->isSuperAdmin())
                        <div class="pt-1">
                            <button @click="openAccounts = !openAccounts" 
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-book-journal-whills w-5 text-center text-slate-400"></i>
                                    <span>Accounts & Cash</span>
                                </div>
                                <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200 text-slate-500" :class="openAccounts ? 'rotate-180 text-[#da1705]' : ''"></i>
                            </button>
                            <div x-show="openAccounts" x-cloak class="mt-1 pl-4 space-y-1 border-l-2 border-slate-800 ml-5">
                                @if(company_has_feature('day_book') && (auth()->user()?->hasPermission('day_book.view') || auth()->user()?->isSuperAdmin()))
                                    <a href="{{ route('day-book.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('day-book.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-cash-register text-[11px] w-4 text-center"></i>
                                        <span>Day Book / Cash Book</span>
                                    </a>
                                @endif

                                @if(auth()->user()?->hasPermission('ledgers.customer'))
                                    <a href="{{ route('ledgers.customer') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('ledgers.customer') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-book-bookmark text-[11px] w-4 text-center"></i>
                                        <span>Customer Ledgers</span>
                                    </a>
                                @endif

                                @if(auth()->user()?->hasPermission('ledgers.vendor'))
                                    <a href="{{ route('ledgers.vendor') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('ledgers.vendor') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-book text-[11px] w-4 text-center"></i>
                                        <span>Vendor Ledgers</span>
                                    </a>
                                @endif

                                @if(company_has_feature('vouchers'))
                                    <a href="{{ route('vouchers.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('vouchers.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-money-bill-transfer text-[11px] w-4 text-center"></i>
                                        <span>Cash & Vouchers</span>
                                    </a>
                                @endif

                                @if(company_has_feature('expenses') && auth()->user()?->hasPermission('expenses.view'))
                                    <a href="{{ route('expenses.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('expenses.*') || request()->routeIs('expense-categories.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-receipt text-[11px] w-4 text-center"></i>
                                        <span>Operating Expenses</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- People & Contacts Dropdown -->
                    @if(auth()->user()?->hasPermission('customers.view') || auth()->user()?->hasPermission('vendors.view'))
                        <div class="pt-1">
                            <button @click="openPeople = !openPeople" 
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-users w-5 text-center text-slate-400"></i>
                                    <span>People & Contacts</span>
                                </div>
                                <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200 text-slate-500" :class="openPeople ? 'rotate-180 text-[#da1705]' : ''"></i>
                            </button>
                            <div x-show="openPeople" x-cloak class="mt-1 pl-4 space-y-1 border-l-2 border-slate-800 ml-5">
                                @if(auth()->user()?->hasPermission('customers.view'))
                                    <a href="{{ route('customers.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('customers.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-user-tag text-[11px] w-4 text-center"></i>
                                        <span>Customers</span>
                                    </a>
                                @endif

                                @if(auth()->user()?->hasPermission('vendors.view'))
                                    <a href="{{ route('vendors.index') }}" 
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('vendors.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-truck-moving text-[11px] w-4 text-center"></i>
                                        <span>Vendors / Suppliers</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Analytics -->
                    @if(auth()->user()?->hasPermission('reports.view'))
                        <div class="pt-1">
                            <a href="{{ route('reports.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('reports.*') ? 'bg-[#da1705] text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-chart-line w-5 text-center text-slate-400 {{ request()->routeIs('reports.*') ? 'text-white' : '' }}"></i>
                                <span>Reports & Analytics</span>
                            </a>
                        </div>
                    @endif

                    <!-- Laboratory Payments Integration -->
                    <div class="pt-1">
                        <a href="{{ route('lab.payments') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition {{ request()->routeIs('lab.*') ? 'bg-[#da1705] text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-flask-vial w-5 text-center text-slate-400 {{ request()->routeIs('lab.*') ? 'text-white' : '' }}"></i>
                            <span>Laboratory Payments</span>
                        </a>
                    </div>

                    <!-- User & Access Management Dropdown -->
                    @php
                        $user = auth()->user();
                    @endphp
                    @if($user && !$user->isOwner() && ($user->isSuperAdmin() || $user->hasPermission('users.view') || $user->hasPermission('roles.view') || $user->hasPermission('permissions.view') || $user->hasPermission('settings.view')))
                        <div class="pt-1">
                            <button @click="openAccess = !openAccess" 
                                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold text-amber-400 hover:bg-slate-800 hover:text-amber-300 transition">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-shield-halved w-5 text-center"></i>
                                    <span>System & Controls</span>
                                </div>
                                <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200 text-amber-500/70" :class="openAccess ? 'rotate-180 text-amber-400' : ''"></i>
                            </button>
                            <div x-show="openAccess" x-cloak class="mt-1 pl-4 space-y-1 border-l-2 border-slate-800 ml-5">
                                @if($user->isSuperAdmin() || $user->hasPermission('users.view'))
                                    <a href="{{ route('users.index') }}"
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('users.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-user-group text-[11px] w-4 text-center"></i>
                                        <span>Staff Users</span>
                                    </a>
                                @endif

                                @if($user->isSuperAdmin() || $user->hasPermission('roles.view'))
                                    <a href="{{ route('roles.index') }}"
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('roles.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-id-badge text-[11px] w-4 text-center"></i>
                                        <span>Roles & Permissions</span>
                                    </a>
                                @endif

                                @if($user->isSuperAdmin() || $user->hasPermission('permissions.view'))
                                    <a href="{{ route('permissions.index') }}"
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('permissions.*') ? 'bg-[#da1705] text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-key text-[11px] w-4 text-center"></i>
                                        <span>Permissions Manager</span>
                                    </a>
                                @endif

                                @if($user->isSuperAdmin() || $user->hasPermission('settings.view'))
                                    <a href="{{ route('settings.index') }}"
                                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request()->routeIs('settings.*') ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <i class="fa-solid fa-gear text-[11px] w-4 text-center text-indigo-400"></i>
                                        <span>Settings & Controls</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif
            </nav>

            <!-- Footer / User Profile & Logout (Image 4 Style) -->
            @if(auth()->check())
                @php
                    $u = auth()->user();
                    $initial = strtoupper(substr($u->name, 0, 1));
                    $roleName = $u->isOwner() ? 'System Owner' : ($u->roles->first()?->name ?? ($u->isSuperAdmin() ? 'Super Admin' : 'Staff User'));
                @endphp
                <div class="p-3.5 border-t border-slate-800 bg-slate-950/80">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700/90 flex items-center justify-center font-bold text-white text-sm shrink-0 shadow-inner">
                                {{ $initial }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-100 truncate leading-snug" title="{{ $u->name }}">
                                    {{ $u->name }}
                                </p>
                                <p class="text-[11px] text-slate-400 font-medium truncate leading-tight">
                                    {{ $roleName }}
                                </p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                            @csrf
                            <button type="submit" title="Logout" 
                                    class="text-slate-400 hover:text-rose-400 hover:bg-slate-800/80 p-2 rounded-xl transition flex items-center justify-center">
                                <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 no-print shadow-sm">
                <div class="flex items-center gap-3">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-slate-600 hover:text-slate-900 p-2 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none flex items-center justify-center w-9 h-9">
                        <i class="fa-solid fa-bars text-base"></i>
                    </button>
                    <h1 class="text-xl font-bold text-slate-800">{{ $title ?? 'Dashboard' }}</h1>
                </div>
                <div class="flex items-center gap-3">
                    @if(!auth()->user()?->isOwner() && auth()->user()?->hasPermission('pos.access'))
                        <a href="{{ route('pos.index') }}" 
                           class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-[#da1705] bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition">
                            <i class="fa-solid fa-barcode"></i>
                            <span>POS Screen</span>
                        </a>
                    @endif
                    <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>
                    <div class="text-xs text-slate-500">
                        <i class="fa-regular fa-calendar mr-1"></i>
                        {{ date('d M Y') }}
                    </div>
                </div>
            </header>

            <!-- Main Page Body -->
            <main class="flex-1 p-6 overflow-y-auto">
                <!-- Flash Alerts -->
                @if (session('success'))
                    <div class="mb-6 flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl shadow-sm no-print">
                        <i class="fa-solid fa-circle-check text-[#da1705] text-lg"></i>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 flex items-center gap-3 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl shadow-sm no-print">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl shadow-sm no-print">
                        <div class="flex items-center gap-2 font-semibold text-sm mb-2">
                            <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                            <span>Please correct the following errors:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

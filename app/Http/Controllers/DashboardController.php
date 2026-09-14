<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with key performance indicators & Profit/Loss analytics.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (auth()->user()?->isOwner()) {
            return redirect()->route('owner.dashboard');
        }

        $presetFilter = $request->input('preset_filter', 'today');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $now = Carbon::now();
        if ($presetFilter === 'today') {
            $dateFrom = $now->copy()->startOfDay()->toDateString();
            $dateTo = $now->copy()->endOfDay()->toDateString();
        } elseif ($presetFilter === 'yesterday') {
            $dateFrom = $now->copy()->subDay()->startOfDay()->toDateString();
            $dateTo = $now->copy()->subDay()->endOfDay()->toDateString();
        } elseif ($presetFilter === 'this_week') {
            $dateFrom = $now->copy()->startOfWeek()->toDateString();
            $dateTo = $now->copy()->endOfWeek()->toDateString();
        } elseif ($presetFilter === 'this_month') {
            $dateFrom = $now->copy()->startOfMonth()->toDateString();
            $dateTo = $now->copy()->endOfMonth()->toDateString();
        } elseif ($presetFilter === 'all_time') {
            $dateFrom = null;
            $dateTo = null;
        }

        $today = Carbon::today();

        // 1. Today's Core Metrics
        $todaySales = (float) Sale::whereDate('created_at', $today)->sum('total_amount');
        $todayOrders = Sale::whereDate('created_at', $today)->count();
        $todayExpenses = (float) Expense::whereDate('expense_date', $today)->sum('amount');

        // Today's COGS & Profit
        $todayItems = SaleItem::whereHas('sale', fn ($q) => $q->whereDate('created_at', $today))->with('product')->get();
        $todayCogs = 0;
        foreach ($todayItems as $item) {
            $qty = $item->base_quantity ?: $item->quantity;
            $cost = $item->product ? (float) $item->product->purchase_price : 0;
            $todayCogs += ($qty * $cost);
        }
        $todayGrossProfit = $todaySales - $todayCogs;
        $todayNetProfit = $todayGrossProfit - $todayExpenses;

        // 2. Filtered Period Metrics
        $salesQuery = Sale::query();
        $expenseQuery = Expense::query();
        $purchaseQuery = Purchase::query();

        if ($dateFrom) {
            $salesQuery->whereDate('created_at', '>=', $dateFrom);
            $expenseQuery->whereDate('expense_date', '>=', $dateFrom);
            $purchaseQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $salesQuery->whereDate('created_at', '<=', $dateTo);
            $expenseQuery->whereDate('expense_date', '<=', $dateTo);
            $purchaseQuery->whereDate('created_at', '<=', $dateTo);
        }

        $filteredSales = (float) (clone $salesQuery)->sum('total_amount');
        $filteredExpenses = (float) (clone $expenseQuery)->sum('amount');
        $filteredPurchases = (float) (clone $purchaseQuery)->sum('total_amount');

        $filteredItemsQuery = SaleItem::query();
        if ($dateFrom || $dateTo) {
            $filteredItemsQuery->whereHas('sale', function ($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) {
                    $q->whereDate('created_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $q->whereDate('created_at', '<=', $dateTo);
                }
            });
        }
        $filteredItems = $filteredItemsQuery->with('product')->get();

        $filteredCogs = 0;
        foreach ($filteredItems as $item) {
            $qty = $item->base_quantity ?: $item->quantity;
            $cost = $item->product ? (float) $item->product->purchase_price : 0;
            $filteredCogs += ($qty * $cost);
        }

        $filteredGrossProfit = $filteredSales - $filteredCogs;
        $filteredNetProfit = $filteredGrossProfit - $filteredExpenses;
        $filteredProfitMargin = $filteredSales > 0 ? ($filteredNetProfit / $filteredSales) * 100 : 0;

        // 3. System Counters & Stock Summary
        $totalSales = (float) Sale::sum('total_amount');
        $totalExpenses = (float) Expense::sum('amount');
        $totalProducts = Product::count();
        $totalCustomers = Customer::count();
        $totalVendors = Vendor::count();
        $totalCategories = Category::count();
        $lowStockCount = Product::lowStock()->count();
        $totalStockValue = (float) (Product::selectRaw('SUM(quantity * purchase_price) as val')->value('val') ?? 0);

        $recentSales = Sale::with('customer')
            ->latest()
            ->take(5)
            ->get();

        $recentExpenses = Expense::with('category')
            ->latest('expense_date')
            ->take(5)
            ->get();

        $lowStockProducts = Product::with('category')
            ->lowStock()
            ->orderBy('quantity', 'asc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'presetFilter',
            'dateFrom',
            'dateTo',
            'todaySales',
            'todayOrders',
            'todayExpenses',
            'todayGrossProfit',
            'todayNetProfit',
            'filteredSales',
            'filteredExpenses',
            'filteredPurchases',
            'filteredCogs',
            'filteredGrossProfit',
            'filteredNetProfit',
            'filteredProfitMargin',
            'totalSales',
            'totalExpenses',
            'totalProducts',
            'totalCustomers',
            'totalVendors',
            'totalCategories',
            'lowStockCount',
            'totalStockValue',
            'recentSales',
            'recentExpenses',
            'lowStockProducts'
        ));
    }
}

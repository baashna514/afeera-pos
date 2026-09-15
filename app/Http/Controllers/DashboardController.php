<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleOrder;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard matching the requested design layout with exact metrics & tabs.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (auth()->user()?->isOwner()) {
            return redirect()->route('owner.dashboard');
        }

        $now = Carbon::now();
        $today = Carbon::today();

        $presetFilter = $request->input('preset_filter', 'today');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if ($presetFilter === 'today') {
            $dateFrom = $today->copy()->startOfDay()->toDateString();
            $dateTo = $today->copy()->endOfDay()->toDateString();
        } elseif ($presetFilter === 'yesterday') {
            $dateFrom = $now->copy()->subDay()->startOfDay()->toDateString();
            $dateTo = $now->copy()->subDay()->endOfDay()->toDateString();
        } elseif ($presetFilter === 'this_week') {
            $dateFrom = $now->copy()->startOfWeek()->toDateString();
            $dateTo = $now->copy()->endOfWeek()->toDateString();
        } elseif ($presetFilter === 'this_month') {
            $dateFrom = $now->copy()->startOfMonth()->toDateString();
            $dateTo = $now->copy()->endOfMonth()->toDateString();
        }

        // 1. Top Cards Row 1: Financial Totals
        $totalReceivables = (float) Sale::sum('due_amount');
        $totalPayables = (float) Purchase::sum('due_amount');

        $cashIn = (float) Sale::where('payment_method', 'cash')->sum('paid_amount') + (float) Voucher::where('type', 'receipt')->sum('amount');
        $cashOut = (float) Purchase::where('payment_method', 'cash')->sum('paid_amount') + (float) Expense::sum('amount') + (float) Voucher::where('type', 'payment')->sum('amount');
        $cashBalance = $cashIn - $cashOut;

        $bankIn = (float) Sale::where('payment_method', '!=', 'cash')->sum('paid_amount');
        $bankOut = (float) Purchase::where('payment_method', '!=', 'cash')->sum('paid_amount');
        $bankBalance = $bankIn - $bankOut;

        // 2. Top Cards Row 2: Sales Timeframe Breakdowns
        $dailySale = (float) Sale::whereDate('created_at', $today)->sum('total_amount');
        $weeklySale = (float) Sale::whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->sum('total_amount');
        $monthlySale = (float) Sale::whereYear('created_at', $now->year)->whereMonth('created_at', $now->month)->sum('total_amount');
        $yearlySale = (float) Sale::whereYear('created_at', $now->year)->sum('total_amount');

        // 3. Top Cards Row 3: Expense Timeframe Breakdowns
        $dailyExpense = (float) Expense::whereDate('expense_date', $today)->sum('amount');
        $weeklyExpense = (float) Expense::whereBetween('expense_date', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->sum('amount');
        $monthlyExpense = (float) Expense::whereYear('expense_date', $now->year)->whereMonth('expense_date', $now->month)->sum('amount');
        $yearlyExpense = (float) Expense::whereYear('expense_date', $now->year)->sum('amount');

        // 4. Profit & Loss Calculations for Filtered Period
        $todayItems = SaleItem::whereHas('sale', fn ($q) => $q->whereDate('created_at', $today))->with('product')->get();
        $todayCogs = 0;
        foreach ($todayItems as $item) {
            $qty = $item->base_quantity ?: $item->quantity;
            $cost = $item->product ? (float) $item->product->purchase_price : 0;
            $todayCogs += ($qty * $cost);
        }
        $todayGrossProfit = $dailySale - $todayCogs;
        $todayNetProfit = $todayGrossProfit - $dailyExpense;

        $salesQuery = Sale::query();
        $expenseQuery = Expense::query();
        if ($dateFrom) {
            $salesQuery->whereDate('created_at', '>=', $dateFrom);
            $expenseQuery->whereDate('expense_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $salesQuery->whereDate('created_at', '<=', $dateTo);
            $expenseQuery->whereDate('expense_date', '<=', $dateTo);
        }
        $filteredSales = (float) (clone $salesQuery)->sum('total_amount');
        $filteredExpenses = (float) (clone $expenseQuery)->sum('amount');

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

        // Chart Data: Monthly Sales vs Purchases (Last 6 Months)
        $chartLabels = [];
        $salesChartData = [];
        $purchasesChartData = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $now->copy()->subMonths($i);
            $chartLabels[] = $monthDate->format('M Y');
            $salesChartData[] = (float) Sale::whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->sum('total_amount');
            $purchasesChartData[] = (float) Purchase::whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->sum('total_amount');
        }

        // Tab Data
        $recentExpenses = Expense::with('category')->latest('expense_date')->take(5)->get();
        $clientDues = Sale::where('due_amount', '>', 0)->with('customer')->latest()->take(5)->get();
        $amountReceived = Sale::where('paid_amount', '>', 0)->with('customer')->latest()->take(5)->get();

        // Bottom Cards Data
        $unpaidSaleInvoicesCount = Sale::where('due_amount', '>', 0)->count();
        $unpaidPurchaseInvoicesCount = Purchase::where('due_amount', '>', 0)->count();
        $lowStockCount = Product::lowStock()->count();
        $pendingVouchersCount = Voucher::count();

        $quotationsCount = SaleOrder::pending()->count();
        $saleInvoicesCount = Sale::count();
        $purchaseInvoicesCount = Purchase::count();

        return view('dashboard', compact(
            'presetFilter',
            'dateFrom',
            'dateTo',
            'totalReceivables',
            'totalPayables',
            'cashBalance',
            'bankBalance',
            'dailySale',
            'weeklySale',
            'monthlySale',
            'yearlySale',
            'dailyExpense',
            'weeklyExpense',
            'monthlyExpense',
            'yearlyExpense',
            'todayGrossProfit',
            'todayNetProfit',
            'filteredSales',
            'filteredExpenses',
            'filteredNetProfit',
            'filteredProfitMargin',
            'chartLabels',
            'salesChartData',
            'purchasesChartData',
            'recentExpenses',
            'clientDues',
            'amountReceived',
            'unpaidSaleInvoicesCount',
            'unpaidPurchaseInvoicesCount',
            'lowStockCount',
            'pendingVouchersCount',
            'quotationsCount',
            'saleInvoicesCount',
            'purchaseInvoicesCount'
        ));
    }
}

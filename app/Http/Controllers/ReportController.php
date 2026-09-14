<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->query('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::today()->toDateString());

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // 1. Sales Report
        $salesQuery = Sale::whereBetween('created_at', [$startDateTime, $endDateTime]);
        $totalSalesAmount = (float) (clone $salesQuery)->sum('total_amount');
        $totalOrdersCount = (clone $salesQuery)->count();

        // 2. Purchases Report
        $purchasesQuery = Purchase::whereBetween('created_at', [$startDateTime, $endDateTime]);
        $totalPurchasesAmount = (float) (clone $purchasesQuery)->sum('total_amount');
        $totalPurchasesCount = (clone $purchasesQuery)->count();

        // 3. Expenses Report
        $expensesQuery = Expense::whereBetween('expense_date', [$startDate, $endDate]);
        $totalExpensesAmount = (float) (clone $expensesQuery)->sum('amount');
        $expenseCategoryBreakdown = (clone $expensesQuery)
            ->selectRaw('expense_category_id, SUM(amount) as total')
            ->groupBy('expense_category_id')
            ->with('category')
            ->get();

        // 4. Cost of Goods Sold (COGS) & Profit Calculations
        $saleItems = SaleItem::whereHas('sale', fn ($q) => $q->whereBetween('created_at', [$startDateTime, $endDateTime]))
            ->with(['product.category', 'unit'])
            ->get();

        $cogsAmount = 0;
        $productProfits = [];

        foreach ($saleItems as $item) {
            $qty = (float) ($item->base_quantity ?: $item->quantity);
            $revenue = (float) $item->subtotal;
            $unitCost = $item->product ? (float) $item->product->purchase_price : 0;
            $cost = $qty * $unitCost;
            $cogsAmount += $cost;

            $prodId = $item->product_id;
            if (! isset($productProfits[$prodId])) {
                $productProfits[$prodId] = [
                    'product' => $item->product,
                    'units_sold' => 0,
                    'revenue' => 0,
                    'cogs' => 0,
                    'profit' => 0,
                ];
            }
            $productProfits[$prodId]['units_sold'] += $qty;
            $productProfits[$prodId]['revenue'] += $revenue;
            $productProfits[$prodId]['cogs'] += $cost;
            $productProfits[$prodId]['profit'] += ($revenue - $cost);
        }

        // Sort product profits by highest profit
        usort($productProfits, fn ($a, $b) => $b['profit'] <=> $a['profit']);

        $grossProfit = $totalSalesAmount - $cogsAmount;
        $netProfit = $grossProfit - $totalExpensesAmount;
        $overallMargin = $totalSalesAmount > 0 ? ($netProfit / $totalSalesAmount) * 100 : 0;

        // 5. Stock Summary
        $totalStockUnits = Product::sum('quantity');
        $totalStockCost = Product::selectRaw('SUM(quantity * purchase_price) as val')->value('val') ?? 0;
        $totalStockRetail = Product::selectRaw('SUM(quantity * selling_price) as val')->value('val') ?? 0;
        $potentialProfit = $totalStockRetail - $totalStockCost;
        $lowStockCount = Product::lowStock()->count();

        // 6. Payment method breakdown
        $cashSales = (clone $salesQuery)->where('payment_method', 'cash')->sum('total_amount');
        $cardSales = (clone $salesQuery)->where('payment_method', 'card')->sum('total_amount');
        $bankSales = (clone $salesQuery)->where('payment_method', 'bank_transfer')->sum('total_amount');

        return view('reports.index', compact(
            'startDate',
            'endDate',
            'totalSalesAmount',
            'totalOrdersCount',
            'totalPurchasesAmount',
            'totalPurchasesCount',
            'totalExpensesAmount',
            'expenseCategoryBreakdown',
            'cogsAmount',
            'grossProfit',
            'netProfit',
            'overallMargin',
            'productProfits',
            'totalStockUnits',
            'totalStockCost',
            'totalStockRetail',
            'potentialProfit',
            'lowStockCount',
            'cashSales',
            'cardSales',
            'bankSales'
        ));
    }
}

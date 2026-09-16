<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DayBook;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use App\Models\Vendor;
use App\Models\Voucher;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type', 'sales_summary');
        $startDate = $request->query('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::today()->toDateString());

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // Common filter parameters
        $customerId = $request->query('customer_id');
        $vendorId = $request->query('vendor_id');
        $warehouseId = $request->query('warehouse_id');
        $productId = $request->query('product_id');

        // Reference dropdown data for filters
        $customers = Customer::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        $data = [
            'type' => $type,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'customerId' => $customerId,
            'vendorId' => $vendorId,
            'warehouseId' => $warehouseId,
            'productId' => $productId,
            'customers' => $customers,
            'vendors' => $vendors,
            'warehouses' => $warehouses,
            'products' => $products,
        ];

        switch ($type) {
            // 1. Sales Summary
            case 'sales_summary':
                $query = Sale::whereBetween('sale_date', [$startDate, $endDate]);
                if ($warehouseId) {
                    $query->where('warehouse_id', $warehouseId);
                }

                $data['totalSales'] = (float) (clone $query)->sum('total_amount');
                $data['totalPaid'] = (float) (clone $query)->sum('paid_amount');
                $data['totalDue'] = (float) (clone $query)->sum('due_amount');
                $data['totalOrders'] = (clone $query)->count();
                $data['cashSales'] = (float) (clone $query)->where('payment_method', 'cash')->sum('paid_amount');
                $data['cardSales'] = (float) (clone $query)->where('payment_method', 'card')->sum('paid_amount');
                $data['bankSales'] = (float) (clone $query)->where('payment_method', 'bank_transfer')->sum('paid_amount');
                $data['creditSales'] = (float) (clone $query)->where('payment_method', 'credit')->sum('total_amount');

                $data['dailyTrend'] = (clone $query)
                    ->selectRaw('sale_date, COUNT(*) as count, SUM(total_amount) as total, SUM(paid_amount) as paid')
                    ->groupBy('sale_date')
                    ->orderBy('sale_date', 'desc')
                    ->get();
                break;

                // 2. Sales Detail
            case 'sales_detail':
                $query = Sale::with(['customer', 'warehouse', 'items.product'])
                    ->whereBetween('sale_date', [$startDate, $endDate]);
                if ($customerId) {
                    $query->where('customer_id', $customerId);
                }
                if ($warehouseId) {
                    $query->where('warehouse_id', $warehouseId);
                }

                $data['sales'] = $query->latest('sale_date')->paginate(20)->withQueryString();
                break;

                // 3. Sales by Product
            case 'sales_by_product':
                $query = SaleItem::whereHas('sale', fn ($q) => $q->whereBetween('sale_date', [$startDate, $endDate]))
                    ->with('product.category')
                    ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue, AVG(price) as avg_price, COUNT(DISTINCT sale_id) as sales_count')
                    ->groupBy('product_id');

                if ($productId) {
                    $query->where('product_id', $productId);
                }
                $data['productSales'] = $query->orderBy('total_revenue', 'desc')->get();
                break;

                // 4. Sales by Customer
            case 'sales_by_customer':
                $query = Sale::whereBetween('sale_date', [$startDate, $endDate])
                    ->with('customer')
                    ->selectRaw('customer_id, COUNT(*) as order_count, SUM(total_amount) as total_amount, SUM(paid_amount) as paid_amount, SUM(due_amount) as due_amount')
                    ->groupBy('customer_id');

                if ($customerId) {
                    $query->where('customer_id', $customerId);
                }
                $data['customerSales'] = $query->orderBy('total_amount', 'desc')->get();
                break;

                // 5. Sales by Warehouse
            case 'sales_by_warehouse':
                $data['warehouseSales'] = Sale::whereBetween('sale_date', [$startDate, $endDate])
                    ->with('warehouse')
                    ->selectRaw('warehouse_id, COUNT(*) as order_count, SUM(total_amount) as total_amount, SUM(paid_amount) as paid_amount')
                    ->groupBy('warehouse_id')
                    ->get();
                break;

                // 6. Sales Returns
            case 'sales_returns':
                $data['returns'] = SaleReturn::with(['sale', 'customer', 'warehouse'])
                    ->whereBetween('return_date', [$startDate, $endDate])
                    ->latest('return_date')
                    ->get();
                $data['totalRefund'] = $data['returns']->sum('refund_amount');
                break;

                // 7. Purchase Summary
            case 'purchase_summary':
                $query = Purchase::whereBetween('purchase_date', [$startDate, $endDate]);
                if ($warehouseId) {
                    $query->where('warehouse_id', $warehouseId);
                }

                $data['totalPurchases'] = (float) (clone $query)->sum('total_amount');
                $data['totalPaid'] = (float) (clone $query)->sum('paid_amount');
                $data['totalDue'] = (float) (clone $query)->sum('due_amount');
                $data['totalOrders'] = (clone $query)->count();

                $data['dailyTrend'] = (clone $query)
                    ->selectRaw('purchase_date, COUNT(*) as count, SUM(total_amount) as total, SUM(paid_amount) as paid')
                    ->groupBy('purchase_date')
                    ->orderBy('purchase_date', 'desc')
                    ->get();
                break;

                // 8. Purchase by Product
            case 'purchase_by_product':
                $query = PurchaseItem::whereHas('purchase', fn ($q) => $q->whereBetween('purchase_date', [$startDate, $endDate]))
                    ->with('product.category')
                    ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_cost, AVG(purchase_price) as avg_cost')
                    ->groupBy('product_id');

                if ($productId) {
                    $query->where('product_id', $productId);
                }
                $data['productPurchases'] = $query->orderBy('total_cost', 'desc')->get();
                break;

                // 9. Purchase by Vendor
            case 'purchase_by_vendor':
                $query = Purchase::whereBetween('purchase_date', [$startDate, $endDate])
                    ->with('vendor')
                    ->selectRaw('vendor_id, COUNT(*) as order_count, SUM(total_amount) as total_amount, SUM(paid_amount) as paid_amount, SUM(due_amount) as due_amount')
                    ->groupBy('vendor_id');

                if ($vendorId) {
                    $query->where('vendor_id', $vendorId);
                }
                $data['vendorPurchases'] = $query->orderBy('total_amount', 'desc')->get();
                break;

                // 10. Purchase Returns
            case 'purchase_returns':
                $data['returns'] = PurchaseReturn::with(['purchase', 'vendor', 'warehouse'])
                    ->whereBetween('return_date', [$startDate, $endDate])
                    ->latest('return_date')
                    ->get();
                $data['totalRefund'] = $data['returns']->sum('refund_amount');
                break;

                // 11. Current Stock
            case 'current_stock':
                $query = Product::with(['category', 'brand', 'unit', 'secondaryUnits.unit', 'warehouseStocks.warehouse']);
                if ($productId) {
                    $query->where('id', $productId);
                }
                if ($warehouseId) {
                    $query->whereHas('warehouseStocks', fn ($q) => $q->where('warehouse_id', $warehouseId));
                }
                $data['products'] = $query->orderBy('name')->get();
                break;

                // 12. Stock Ledger
            case 'stock_ledger':
                $query = StockMovement::with(['product.unit', 'warehouse'])
                    ->whereBetween('created_at', [$startDateTime, $endDateTime]);

                if ($productId) {
                    $query->where('product_id', $productId);
                }
                if ($warehouseId) {
                    $query->where('warehouse_id', $warehouseId);
                }

                $data['movements'] = $query->latest('created_at')->latest('id')->get();
                break;

                // 13. Stock Movement Aggregate
            case 'stock_movement':
                $data['stockMovements'] = Product::with(['category'])
                    ->get()
                    ->map(function ($prod) use ($startDateTime, $endDateTime, $warehouseId) {
                        $qIn = StockMovement::where('product_id', $prod->id)
                            ->whereIn('type', ['purchase', 'adjustment_add', 'transfer_in', 'return_in'])
                            ->whereBetween('created_at', [$startDateTime, $endDateTime]);
                        if ($warehouseId) {
                            $qIn->where('warehouse_id', $warehouseId);
                        }

                        $qOut = StockMovement::where('product_id', $prod->id)
                            ->whereIn('type', ['sale', 'adjustment_sub', 'transfer_out', 'return_out'])
                            ->whereBetween('created_at', [$startDateTime, $endDateTime]);
                        if ($warehouseId) {
                            $qOut->where('warehouse_id', $warehouseId);
                        }

                        $totalIn = $qIn->sum('quantity');
                        $totalOut = $qOut->sum('quantity');

                        return [
                            'product' => $prod,
                            'total_in' => $totalIn,
                            'total_out' => $totalOut,
                            'net_change' => $totalIn - $totalOut,
                            'current_stock' => $prod->quantity,
                        ];
                    });
                break;

                // 14. Stock Valuation
            case 'stock_valuation':
                $products = Product::with('category')->get();
                $totalUnits = 0;
                $totalCostValuation = 0;
                $totalRetailValuation = 0;

                foreach ($products as $p) {
                    $units = (float) $p->quantity;
                    $cost = (float) ($p->cost_price ?: $p->purchase_price);
                    $retail = (float) ($p->sale_price ?: $p->selling_price);

                    $totalUnits += $units;
                    $totalCostValuation += ($units * $cost);
                    $totalRetailValuation += ($units * $retail);
                }

                $data['totalUnits'] = $totalUnits;
                $data['totalCostValuation'] = $totalCostValuation;
                $data['totalRetailValuation'] = $totalRetailValuation;
                $data['potentialProfit'] = $totalRetailValuation - $totalCostValuation;
                $data['products'] = $products;
                break;

                // 15. Low / Out-of-Stock
            case 'low_stock':
                $data['products'] = Product::with(['category', 'brand', 'unit', 'secondaryUnits.unit', 'warehouseStocks.warehouse'])
                    ->where(function ($q) {
                        $q->whereColumn('quantity', '<=', 'alert_quantity')
                            ->orWhere('quantity', '<=', 0);
                    })
                    ->orderBy('quantity', 'asc')
                    ->get();
                break;

                // 16. Customer Outstanding + Aging
            case 'customer_aging':
                $today = Carbon::today();
                $data['customerAging'] = Customer::whereHas('sales', fn ($q) => $q->where('due_amount', '>', 0))
                    ->get()
                    ->map(function ($c) use ($today) {
                        $unpaidSales = Sale::where('customer_id', $c->id)->where('due_amount', '>', 0)->get();
                        $current = 0;
                        $days30 = 0;
                        $days60 = 0;
                        $days90Plus = 0;
                        $totalDue = 0;

                        foreach ($unpaidSales as $s) {
                            $due = (float) $s->due_amount;
                            $totalDue += $due;
                            $age = $today->diffInDays(Carbon::parse($s->sale_date ?? $s->created_at));

                            if ($age <= 30) {
                                $current += $due;
                            } elseif ($age <= 60) {
                                $days30 += $due;
                            } elseif ($age <= 90) {
                                $days60 += $due;
                            } else {
                                $days90Plus += $due;
                            }
                        }

                        return [
                            'customer' => $c,
                            'total_due' => $totalDue,
                            'current' => $current,
                            'days_30' => $days30,
                            'days_60' => $days60,
                            'days_90_plus' => $days90Plus,
                        ];
                    });
                break;

                // 17. Vendor Outstanding + Aging
            case 'vendor_aging':
                $today = Carbon::today();
                $data['vendorAging'] = Vendor::whereHas('purchases', fn ($q) => $q->where('due_amount', '>', 0))
                    ->get()
                    ->map(function ($v) use ($today) {
                        $unpaidPurchases = Purchase::where('vendor_id', $v->id)->where('due_amount', '>', 0)->get();
                        $current = 0;
                        $days30 = 0;
                        $days60 = 0;
                        $days90Plus = 0;
                        $totalDue = 0;

                        foreach ($unpaidPurchases as $p) {
                            $due = (float) $p->due_amount;
                            $totalDue += $due;
                            $age = $today->diffInDays(Carbon::parse($p->purchase_date ?? $p->created_at));

                            if ($age <= 30) {
                                $current += $due;
                            } elseif ($age <= 60) {
                                $days30 += $due;
                            } elseif ($age <= 90) {
                                $days60 += $due;
                            } else {
                                $days90Plus += $due;
                            }
                        }

                        return [
                            'vendor' => $v,
                            'total_due' => $totalDue,
                            'current' => $current,
                            'days_30' => $days30,
                            'days_60' => $days60,
                            'days_90_plus' => $days90Plus,
                        ];
                    });
                break;

                // 18. Customer/Vendor Ledger
            case 'party_ledger':
                $partyType = $request->query('party_type', 'customer');
                $data['partyType'] = $partyType;

                if ($partyType === 'customer' && $customerId) {
                    $data['selectedParty'] = Customer::find($customerId);
                    $sales = Sale::where('customer_id', $customerId)->whereBetween('sale_date', [$startDate, $endDate])->get()
                        ->map(fn ($s) => ['date' => $s->sale_date, 'ref' => $s->invoice_number, 'desc' => 'Sale Invoice', 'debit' => (float) $s->total_amount, 'credit' => 0]);
                    $vouchers = Voucher::where('customer_id', $customerId)->where('type', 'receipt')->whereBetween('voucher_date', [$startDate, $endDate])->get()
                        ->map(fn ($v) => ['date' => $v->voucher_date, 'ref' => $v->voucher_number, 'desc' => 'Payment Receipt', 'debit' => 0, 'credit' => (float) $v->amount]);

                    $data['ledgerEntries'] = $sales->concat($vouchers)->sortBy('date')->values();
                } elseif ($partyType === 'vendor' && $vendorId) {
                    $data['selectedParty'] = Vendor::find($vendorId);
                    $purchases = Purchase::where('vendor_id', $vendorId)->whereBetween('purchase_date', [$startDate, $endDate])->get()
                        ->map(fn ($p) => ['date' => $p->purchase_date, 'ref' => $p->reference_no, 'desc' => 'Purchase Invoice', 'debit' => 0, 'credit' => (float) $p->total_amount]);
                    $vouchers = Voucher::where('vendor_id', $vendorId)->where('type', 'payment')->whereBetween('voucher_date', [$startDate, $endDate])->get()
                        ->map(fn ($v) => ['date' => $v->voucher_date, 'ref' => $v->voucher_number, 'desc' => 'Vendor Payment', 'debit' => (float) $v->amount, 'credit' => 0]);

                    $data['ledgerEntries'] = $purchases->concat($vouchers)->sortBy('date')->values();
                }
                break;

                // 19. Profit & Loss Statement
            case 'profit_loss':
                $salesQuery = Sale::whereBetween('sale_date', [$startDate, $endDate]);
                $grossSales = (float) (clone $salesQuery)->sum('total_amount');
                $salesReturns = (float) SaleReturn::whereBetween('return_date', [$startDate, $endDate])->sum('refund_amount');
                $netSales = $grossSales - $salesReturns;

                // COGS calculation
                $saleItems = SaleItem::whereHas('sale', fn ($q) => $q->whereBetween('sale_date', [$startDate, $endDate]))->with('product')->get();
                $cogs = 0;
                foreach ($saleItems as $item) {
                    $qty = (float) ($item->base_quantity ?: $item->quantity);
                    $cost = $item->product ? (float) ($item->product->cost_price ?: $item->product->purchase_price) : 0;
                    $cogs += ($qty * $cost);
                }

                $grossProfit = $netSales - $cogs;
                $expenses = (float) Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
                $netProfit = $grossProfit - $expenses;

                $data['grossSales'] = $grossSales;
                $data['salesReturns'] = $salesReturns;
                $data['netSales'] = $netSales;
                $data['cogs'] = $cogs;
                $data['grossProfit'] = $grossProfit;
                $data['expenses'] = $expenses;
                $data['netProfit'] = $netProfit;
                $data['expenseCategories'] = Expense::whereBetween('expense_date', [$startDate, $endDate])
                    ->selectRaw('expense_category_id, SUM(amount) as total')
                    ->groupBy('expense_category_id')
                    ->with('category')
                    ->get();
                break;

                // 20. Cashier Closing / Cash Register Audit
            case 'cashier_closing':
                $date = $request->query('date', Carbon::today()->toDateString());
                $data['selectedDate'] = $date;
                $dayBook = DayBook::whereDate('date', $date)->first();

                $openingBalance = $dayBook ? (float) $dayBook->opening_balance : 0.00;
                $cashSales = (float) Sale::whereDate('sale_date', $date)->where('payment_method', 'cash')->sum('paid_amount');
                $receiptVouchers = (float) Voucher::whereDate('voucher_date', $date)->where('type', 'receipt')->where('payment_method', 'cash')->sum('amount');
                $cashPurchases = (float) Purchase::whereDate('purchase_date', $date)->where('payment_method', 'cash')->sum('paid_amount');
                $paymentVouchers = (float) Voucher::whereDate('voucher_date', $date)->where('type', 'payment')->where('payment_method', 'cash')->sum('amount');
                $cashExpenses = (float) Expense::whereDate('expense_date', $date)->where('payment_method', 'cash')->sum('amount');

                $data['dayBook'] = $dayBook;
                $data['openingBalance'] = $openingBalance;
                $data['cashSales'] = $cashSales;
                $data['receiptVouchers'] = $receiptVouchers;
                $data['cashPurchases'] = $cashPurchases;
                $data['paymentVouchers'] = $paymentVouchers;
                $data['cashExpenses'] = $cashExpenses;
                $data['expectedDrawerCash'] = $openingBalance + $cashSales + $receiptVouchers - $cashPurchases - $paymentVouchers - $cashExpenses;
                break;
        }

        return view('reports.index', $data);
    }
}

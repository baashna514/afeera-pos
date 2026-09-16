<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleOrder;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $paymentStatus = $request->query('payment_status');
        $paymentMethod = $request->query('payment_method');
        $customerId = $request->query('customer_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $customers = Customer::orderBy('name')->get();

        $query = Sale::with(['customer', 'saleOrder', 'warehouse', 'items.product'])
            ->when($search, function ($q, $search) {
                return $q->where(function ($sub) use ($search) {
                    $sub->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($cQuery) use ($search) {
                            $cQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($paymentStatus, function ($q, $status) {
                return $q->where('payment_status', $status);
            })
            ->when($paymentMethod, function ($q, $method) {
                return $q->where('payment_method', $method);
            })
            ->when($customerId, function ($q, $cId) {
                return $q->where('customer_id', $cId);
            })
            ->when($dateFrom, function ($q, $from) {
                return $q->whereDate('sale_date', '>=', $from);
            })
            ->when($dateTo, function ($q, $to) {
                return $q->whereDate('sale_date', '<=', $to);
            });

        $sales = $query->latest()->paginate(15)->withQueryString();

        $totalRevenue = Sale::sum('total_amount');
        $totalPaid = Sale::sum('paid_amount');
        $totalDue = Sale::sum('due_amount');
        $totalOrders = Sale::count();

        return view('sales.index', compact('sales', 'customers', 'search', 'paymentStatus', 'paymentMethod', 'customerId', 'dateFrom', 'dateTo', 'totalRevenue', 'totalPaid', 'totalDue', 'totalOrders'));
    }

    public function create(Request $request): View
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::with(['unit', 'secondaryUnits.unit', 'warehouseStocks'])->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        $pendingOrders = SaleOrder::with(['customer', 'items.product'])
            ->pending()
            ->latest()
            ->get();

        $selectedSo = null;
        if ($request->has('sale_order_id')) {
            $selectedSo = SaleOrder::with(['customer', 'items.product.secondaryUnits.unit'])->find($request->query('sale_order_id'));
        }

        return view('sales.create', compact('customers', 'products', 'warehouses', 'pendingOrders', 'selectedSo'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sale_order_id' => ['nullable', 'exists:sale_orders,id'],
            'customer_id' => ['required', 'exists:customers,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'sale_date' => ['nullable', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'in:cash,card,bank_transfer,cheque,online'],
            'has_overall_discount' => ['nullable', 'boolean'],
            'overall_discount_type' => ['nullable', 'string'],
            'overall_discount_value' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'extra_field_one' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['nullable', 'exists:units,id'],
            'items.*.conversion_rate' => ['nullable', 'numeric', 'min:0.0001'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_percentage' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $companyId = Auth::user()?->company_id;
        $warehouseId = $validated['warehouse_id'] ?? Warehouse::where('company_id', $companyId)->where('is_default', true)->value('id') ?? Warehouse::where('company_id', $companyId)->value('id');

        if (! empty($validated['sale_order_id'])) {
            $linkedSo = SaleOrder::find($validated['sale_order_id']);
            if ($linkedSo && $linkedSo->isConverted()) {
                return back()->withInput()->with('error', 'This Sale Order has already been converted into a Sale Invoice.');
            }
        }

        // Check stock availability in base units (overall and in selected warehouse)
        foreach ($validated['items'] as $item) {
            $conversionRate = isset($item['conversion_rate']) ? (float) $item['conversion_rate'] : 1.0;
            $baseRequired = $item['quantity'] * $conversionRate;
            $product = Product::find($item['product_id']);

            if ($product && $product->quantity < $baseRequired) {
                return back()->withInput()->with('error', "Insufficient stock for '{$product->name}'. Available: {$product->quantity} base units, Requested: {$baseRequired} base units.");
            }

            if ($warehouseId && $product) {
                $whStock = WarehouseStock::where('company_id', $companyId)
                    ->where('warehouse_id', $warehouseId)
                    ->where('product_id', $product->id)
                    ->first();
                $availableInWh = $whStock ? (int) $whStock->quantity : 0;
                if ($availableInWh < $baseRequired) {
                    $whName = Warehouse::find($warehouseId)?->name ?? 'selected warehouse';

                    return back()->withInput()->with('error', "Insufficient stock for '{$product->name}' in {$whName}. Available: {$availableInWh} base units, Requested: {$baseRequired} base units.");
                }
            }
        }

        $sale = DB::transaction(function () use ($validated, $companyId, $warehouseId) {
            $itemsSubtotalSum = 0;
            $processedItems = [];

            foreach ($validated['items'] as $item) {
                $qty = (int) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $grossRowSubtotal = $qty * $unitPrice;

                $discPerc = isset($item['discount_percentage']) ? (float) $item['discount_percentage'] : 0.0;
                $discAmt = isset($item['discount_amount']) ? (float) $item['discount_amount'] : 0.0;

                if ($discAmt <= 0 && $discPerc > 0) {
                    $discAmt = ($grossRowSubtotal * $discPerc) / 100;
                }

                $rowNetSubtotal = max(0, $grossRowSubtotal - $discAmt);
                $itemsSubtotalSum += $rowNetSubtotal;

                $processedItems[] = array_merge($item, [
                    'disc_perc' => $discPerc,
                    'disc_amt' => $discAmt,
                    'net_subtotal' => $rowNetSubtotal,
                ]);
            }

            $hasOverallDisc = ! empty($validated['has_overall_discount']);
            $overallDiscType = $validated['overall_discount_type'] ?? 'percentage';
            $overallDiscVal = isset($validated['overall_discount_value']) ? (float) $validated['overall_discount_value'] : 0.0;
            $overallDiscAmt = 0.0;

            if ($hasOverallDisc && $overallDiscVal > 0) {
                if (in_array($overallDiscType, ['percentage', 'perc', 'percent'])) {
                    $overallDiscAmt = ($itemsSubtotalSum * $overallDiscVal) / 100;
                } else {
                    $overallDiscAmt = min($itemsSubtotalSum, $overallDiscVal);
                }
            }

            $grandTotal = max(0, $itemsSubtotalSum - $overallDiscAmt);

            $paidAmount = isset($validated['paid_amount']) ? (float) $validated['paid_amount'] : 0.0;
            $actualPaid = min($paidAmount, $grandTotal);
            $changeAmount = max(0, $paidAmount - $grandTotal);
            $dueAmount = max(0, $grandTotal - $paidAmount);
            $paymentStatus = Sale::computePaymentStatus($paidAmount, $grandTotal);
            $invoiceNumber = 'SI-'.date('Ymd').'-'.strtoupper(Str::random(4));

            $sale = Sale::create([
                'company_id' => $companyId,
                'sale_order_id' => $validated['sale_order_id'] ?? null,
                'warehouse_id' => $warehouseId,
                'sale_date' => $validated['sale_date'] ?? date('Y-m-d'),
                'invoice_number' => $invoiceNumber,
                'customer_id' => $validated['customer_id'],
                'subtotal' => $itemsSubtotalSum,
                'discount_type' => $overallDiscType,
                'discount_value' => $overallDiscVal,
                'discount_amount' => $overallDiscAmt,
                'has_overall_discount' => $hasOverallDisc,
                'total_amount' => $grandTotal,
                'paid_amount' => $actualPaid,
                'due_amount' => $dueAmount,
                'change_amount' => $changeAmount,
                'payment_status' => $paymentStatus,
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'note' => $validated['note'] ?? null,
                'description' => $validated['description'] ?? null,
                'extra_field_one' => $validated['extra_field_one'] ?? null,
            ]);

            foreach ($processedItems as $pItem) {
                $conversionRate = isset($pItem['conversion_rate']) ? (float) $pItem['conversion_rate'] : 1.0;
                $baseQuantity = $pItem['quantity'] * $conversionRate;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $pItem['product_id'],
                    'unit_id' => $pItem['unit_id'] ?? null,
                    'conversion_rate' => $conversionRate,
                    'quantity' => $pItem['quantity'],
                    'base_quantity' => $baseQuantity,
                    'price' => $pItem['unit_price'],
                    'discount_percentage' => $pItem['disc_perc'],
                    'discount_amount' => $pItem['disc_amt'],
                    'subtotal' => $pItem['net_subtotal'],
                ]);

                // Reduce inventory in BASE UNITS
                $product = Product::lockForUpdate()->find($pItem['product_id']);
                if ($product) {
                    $beforeQty = $product->quantity;
                    $product->decrement('quantity', $baseQuantity);
                    $afterQty = $beforeQty - $baseQuantity;

                    if ($warehouseId) {
                        $whStock = WarehouseStock::firstOrCreate(
                            ['company_id' => $companyId, 'warehouse_id' => $warehouseId, 'product_id' => $product->id],
                            ['quantity' => $beforeQty]
                        );
                        $whStock->decrement('quantity', $baseQuantity);
                    }

                    $unitModel = ! empty($item['unit_id']) ? Unit::find($item['unit_id']) : null;
                    $unitLabel = $unitModel ? $unitModel->short_code : ($product->unit ? $product->unit->short_code : 'units');

                    StockMovement::create([
                        'company_id' => $companyId,
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouseId,
                        'type' => 'sale',
                        'quantity' => $baseQuantity,
                        'before_quantity' => $beforeQty,
                        'after_quantity' => $afterQty,
                        'reference' => $invoiceNumber,
                        'notes' => "Stock out: {$item['quantity']} {$unitLabel} ({$baseQuantity} base units) via Sale Invoice",
                    ]);
                }
            }

            // Mark linked Sale Order as converted if applicable
            if (! empty($validated['sale_order_id'])) {
                $so = SaleOrder::find($validated['sale_order_id']);
                if ($so) {
                    $so->update([
                        'status' => 'converted',
                        'converted_sale_id' => $sale->id,
                    ]);
                }
            }

            return $sale;
        });

        // Redirect directly to thermal print receipt
        return redirect()->route('sales.receipt', $sale)
            ->with('success', 'Sale Invoice created successfully and inventory updated.');
    }

    public function fetchFromOrder(SaleOrder $saleOrder): JsonResponse
    {
        $saleOrder->load(['customer', 'items.product.unit', 'items.product.secondaryUnits.unit', 'items.unit']);

        return response()->json([
            'success' => true,
            'order' => $saleOrder,
        ]);
    }

    public function show(Sale $sale): View
    {
        $sale->load(['customer', 'saleOrder', 'items.product.category', 'items.unit']);

        return view('sales.show', compact('sale'));
    }

    public function receipt(Sale $sale): View
    {
        $sale->load(['customer', 'items.product.unit', 'items.unit']);

        return view('sales.receipt', compact('sale'));
    }

    public function printPreview(Sale $sale): View
    {
        return $this->receipt($sale);
    }
}

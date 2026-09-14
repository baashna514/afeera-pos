<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function index(): View
    {
        $transfers = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'creator', 'items.product'])
            ->latest()
            ->paginate(15);

        return view('stock-transfers.index', compact('transfers'));
    }

    public function create(): View
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('quantity', '>', 0)->orderBy('name')->get();

        return view('stock-transfers.create', compact('warehouses', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_warehouse_id' => ['required', 'exists:warehouses,id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id', 'different:from_warehouse_id'],
            'transfer_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $companyId = Auth::user()?->company_id;
        $fromWh = Warehouse::findOrFail($validated['from_warehouse_id']);
        $toWh = Warehouse::findOrFail($validated['to_warehouse_id']);

        DB::transaction(function () use ($validated, $companyId, $fromWh, $toWh) {
            $transferNumber = 'TRF-'.date('Ymd').'-'.rand(1000, 9999);

            $transfer = StockTransfer::create([
                'company_id' => $companyId,
                'transfer_number' => $transferNumber,
                'from_warehouse_id' => $fromWh->id,
                'to_warehouse_id' => $toWh->id,
                'transfer_date' => $validated['transfer_date'],
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $itemData) {
                $productId = $itemData['product_id'];
                $qty = (int) $itemData['quantity'];

                $product = Product::lockForUpdate()->findOrFail($productId);

                // Source warehouse stock check & decrement
                $sourceStock = WarehouseStock::firstOrCreate(
                    ['company_id' => $companyId, 'warehouse_id' => $fromWh->id, 'product_id' => $productId],
                    ['quantity' => $product->quantity] // fallback initial sync if empty
                );

                if ($sourceStock->quantity < $qty) {
                    throw ValidationException::withMessages([
                        'items' => ["Insufficient stock for product '{$product->name}' in {$fromWh->name}. Available: {$sourceStock->quantity}, Requested: {$qty}"],
                    ]);
                }

                $beforeFrom = $sourceStock->quantity;
                $sourceStock->decrement('quantity', $qty);
                $afterFrom = $sourceStock->fresh()->quantity;

                // Destination warehouse stock increment
                $destStock = WarehouseStock::firstOrCreate(
                    ['company_id' => $companyId, 'warehouse_id' => $toWh->id, 'product_id' => $productId],
                    ['quantity' => 0]
                );

                $beforeTo = $destStock->quantity;
                $destStock->increment('quantity', $qty);
                $afterTo = $destStock->fresh()->quantity;

                // Create Transfer Item
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $productId,
                    'quantity' => $qty,
                ]);

                // Log Stock Movements for audit history
                StockMovement::create([
                    'company_id' => $companyId,
                    'product_id' => $productId,
                    'warehouse_id' => $fromWh->id,
                    'type' => 'transfer_out',
                    'quantity' => $qty,
                    'before_quantity' => $beforeFrom,
                    'after_quantity' => $afterFrom,
                    'reference' => $transferNumber,
                    'notes' => "Transfer Out to {$toWh->name}",
                ]);

                StockMovement::create([
                    'company_id' => $companyId,
                    'product_id' => $productId,
                    'warehouse_id' => $toWh->id,
                    'type' => 'transfer_in',
                    'quantity' => $qty,
                    'before_quantity' => $beforeTo,
                    'after_quantity' => $afterTo,
                    'reference' => $transferNumber,
                    'notes' => "Transfer In from {$fromWh->name}",
                ]);
            }
        });

        return redirect()->route('stock-transfers.index')
            ->with('success', 'Stock transferred successfully from '.$fromWh->name.' to '.$toWh->name.'.');
    }

    public function show(StockTransfer $stockTransfer): View
    {
        $stockTransfer->load(['fromWarehouse', 'toWarehouse', 'creator', 'items.product']);

        return view('stock-transfers.show', compact('stockTransfer'));
    }
}

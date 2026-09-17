<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $brandId = $request->query('brand_id');
        $stockFilter = $request->query('stock_filter');

        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        $products = Product::with(['category', 'brand'])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($brandId, function ($query, $brandId) {
                return $query->where('brand_id', $brandId);
            })
            ->when($stockFilter === 'low_stock', function ($query) {
                return $query->lowStock();
            })
            ->when($stockFilter === 'out_of_stock', function ($query) {
                return $query->outOfStock();
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('products.index', compact('products', 'categories', 'brands', 'search', 'categoryId', 'brandId', 'stockFilter'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('products.create', compact('categories', 'brands', 'units', 'warehouses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $isCategoryRequired = company_has_feature('categories');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:255', 'unique:products,barcode'],
            'sku' => ['nullable', 'string', 'max:100'],
            'category_id' => [$isCategoryRequired ? 'required' : 'nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'default_sale_unit_id' => ['nullable', 'exists:units,id'],
            'default_purchase_unit_id' => ['nullable', 'exists:units,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'alert_quantity' => ['required', 'integer', 'min:0'],
            'default_discount_type' => ['nullable', 'string', 'in:percentage,fixed'],
            'default_discount_value' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'secondary_units' => ['nullable', 'array'],
            'secondary_units.*.unit_id' => ['required', 'exists:units,id'],
            'secondary_units.*.operator' => ['required', 'string', 'in:multiply,divide'],
            'secondary_units.*.conversion_rate' => ['required', 'numeric', 'min:0.0001'],
            'secondary_units.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'secondary_units.*.purchase_price' => ['nullable', 'numeric', 'min:0'],
            'warehouse_stocks' => ['nullable', 'array'],
            'warehouse_stocks.*.warehouse_id' => ['required', 'exists:warehouses,id'],
            'warehouse_stocks.*.quantity' => ['required', 'integer', 'min:0'],
        ]);

        $companyId = Auth::user()?->company_id;

        if (! empty($validated['warehouse_stocks'])) {
            $totalWhQty = array_sum(array_column($validated['warehouse_stocks'], 'quantity'));
            $validated['quantity'] = $totalWhQty;
        }

        $product = Product::create($validated);

        if (! empty($validated['secondary_units'])) {
            $seenUnits = [];
            foreach ($validated['secondary_units'] as $su) {
                if ($su['unit_id'] == $product->unit_id || in_array($su['unit_id'], $seenUnits)) {
                    continue;
                }
                $seenUnits[] = $su['unit_id'];

                $product->secondaryUnits()->create([
                    'unit_id' => $su['unit_id'],
                    'operator' => $su['operator'],
                    'conversion_rate' => $su['conversion_rate'],
                    'sale_price' => ! empty($su['sale_price']) ? $su['sale_price'] : null,
                    'purchase_price' => ! empty($su['purchase_price']) ? $su['purchase_price'] : null,
                ]);
            }
        }

        if (! empty($validated['warehouse_stocks'])) {
            foreach ($validated['warehouse_stocks'] as $ws) {
                WarehouseStock::updateOrCreate(
                    ['company_id' => $companyId, 'warehouse_id' => $ws['warehouse_id'], 'product_id' => $product->id],
                    ['quantity' => $ws['quantity']]
                );
            }
        } else {
            $defaultWh = Warehouse::where('company_id', $companyId)->where('is_default', true)->first()
                ?? Warehouse::where('company_id', $companyId)->first();

            if ($defaultWh) {
                WarehouseStock::updateOrCreate(
                    ['company_id' => $companyId, 'warehouse_id' => $defaultWh->id, 'product_id' => $product->id],
                    ['quantity' => $product->quantity]
                );
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'unit', 'secondaryUnits.unit']);
        $companyId = Auth::user()?->company_id;

        $warehouses = Warehouse::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();

        $warehouseStocks = collect();
        foreach ($warehouses as $wh) {
            $ws = WarehouseStock::firstOrNew(
                ['company_id' => $companyId, 'warehouse_id' => $wh->id, 'product_id' => $product->id],
                ['quantity' => 0]
            );
            $ws->setRelation('warehouse', $wh);
            $warehouseStocks->push($ws);
        }

        return view('products.show', compact('product', 'warehouseStocks'));
    }

    public function edit(Product $product): View
    {
        $product->load(['secondaryUnits.unit', 'warehouseStocks.warehouse']);
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'brands', 'units', 'warehouses'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $isCategoryRequired = company_has_feature('categories');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:255', 'unique:products,barcode,'.$product->id],
            'sku' => ['nullable', 'string', 'max:100'],
            'category_id' => [$isCategoryRequired ? 'required' : 'nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'default_sale_unit_id' => ['nullable', 'exists:units,id'],
            'default_purchase_unit_id' => ['nullable', 'exists:units,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'alert_quantity' => ['required', 'integer', 'min:0'],
            'default_discount_type' => ['nullable', 'string', 'in:percentage,fixed'],
            'default_discount_value' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'secondary_units' => ['nullable', 'array'],
            'secondary_units.*.unit_id' => ['required', 'exists:units,id'],
            'secondary_units.*.operator' => ['required', 'string', 'in:multiply,divide'],
            'secondary_units.*.conversion_rate' => ['required', 'numeric', 'min:0.0001'],
            'secondary_units.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'secondary_units.*.purchase_price' => ['nullable', 'numeric', 'min:0'],
            'warehouse_stocks' => ['nullable', 'array'],
            'warehouse_stocks.*.warehouse_id' => ['required', 'exists:warehouses,id'],
            'warehouse_stocks.*.quantity' => ['required', 'integer', 'min:0'],
        ]);

        $companyId = Auth::user()?->company_id;

        if (! empty($validated['warehouse_stocks'])) {
            $totalWhQty = array_sum(array_column($validated['warehouse_stocks'], 'quantity'));
            $validated['quantity'] = $totalWhQty;
        }

        $product->update($validated);

        // Sync secondary units
        $product->secondaryUnits()->delete();

        if (! empty($validated['secondary_units'])) {
            $seenUnits = [];
            foreach ($validated['secondary_units'] as $su) {
                if ($su['unit_id'] == $product->unit_id || in_array($su['unit_id'], $seenUnits)) {
                    continue;
                }
                $seenUnits[] = $su['unit_id'];

                $product->secondaryUnits()->create([
                    'unit_id' => $su['unit_id'],
                    'operator' => $su['operator'],
                    'conversion_rate' => $su['conversion_rate'],
                    'sale_price' => ! empty($su['sale_price']) ? $su['sale_price'] : null,
                    'purchase_price' => ! empty($su['purchase_price']) ? $su['purchase_price'] : null,
                ]);
            }
        }

        if (! empty($validated['warehouse_stocks'])) {
            foreach ($validated['warehouse_stocks'] as $ws) {
                WarehouseStock::updateOrCreate(
                    ['company_id' => $companyId, 'warehouse_id' => $ws['warehouse_id'], 'product_id' => $product->id],
                    ['quantity' => $ws['quantity']]
                );
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function printLabels(Product $product): View
    {
        $product->load(['category', 'brand']);

        return view('products.print_labels', compact('product'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->saleItems()->count() > 0 || $product->purchaseItems()->count() > 0) {
            return redirect()->route('products.index')
                ->with('error', 'Cannot delete product with existing purchase or sales records.');
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}

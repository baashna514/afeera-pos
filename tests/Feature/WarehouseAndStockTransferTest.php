<?php

use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Database\Seeders\OwnerSeeder;

beforeEach(function () {
    $this->seed(OwnerSeeder::class);

    $this->company = Company::where('code', 'COMP-001')->first();
    $this->admin = User::where('email', 'superadmin@gmail.com')->first();
    $this->warehouseMain = Warehouse::where('company_id', $this->company->id)->where('is_default', true)->first();

    $this->category = Category::create([
        'company_id' => $this->company->id,
        'name' => 'Hardware',
    ]);

    $this->product = Product::create([
        'company_id' => $this->company->id,
        'category_id' => $this->category->id,
        'name' => 'Power Drill 500W',
        'code' => 'DRL-500',
        'purchase_price' => 2000,
        'selling_price' => 3500,
        'quantity' => 100,
        'alert_quantity' => 10,
    ]);

    // Initial stock in Main Warehouse
    WarehouseStock::create([
        'company_id' => $this->company->id,
        'warehouse_id' => $this->warehouseMain->id,
        'product_id' => $this->product->id,
        'quantity' => 100,
    ]);

    $this->warehouseNorth = Warehouse::create([
        'company_id' => $this->company->id,
        'name' => 'North Branch Warehouse',
        'code' => 'WH-NORTH',
        'is_active' => true,
    ]);
});

test('user can create and manage warehouses', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('warehouses.store'), [
            'name' => 'South Depot',
            'code' => 'WH-SOUTH',
            'phone' => '03009998877',
            'address' => 'Industrial Area Gate 4',
            'is_active' => 1,
        ]);

    $response->assertRedirect(route('warehouses.index'))
        ->assertSessionHas('success');

    $wh = Warehouse::where('code', 'WH-SOUTH')->first();
    expect($wh)->not->toBeNull()
        ->and($wh->name)->toBe('South Depot');
});

test('purchase receives stock into specified warehouse', function () {
    $vendor = Vendor::create([
        'company_id' => $this->company->id,
        'name' => 'Tool Suppliers Ltd',
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('purchases.store'), [
            'vendor_id' => $vendor->id,
            'warehouse_id' => $this->warehouseNorth->id,
            'purchase_date' => date('Y-m-d'),
            'paid_amount' => 10000,
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 25,
                    'purchase_price' => 2000,
                ],
            ],
        ]);

    $response->assertRedirect(route('purchases.receipt', 1))
        ->assertSessionHas('success');

    $northStock = WarehouseStock::where('warehouse_id', $this->warehouseNorth->id)
        ->where('product_id', $this->product->id)
        ->first();

    expect($northStock)->not->toBeNull()
        ->and($northStock->quantity)->toBe(25);
});

test('sale deducts stock from specified warehouse', function () {
    $customer = Customer::create([
        'company_id' => $this->company->id,
        'name' => 'Construct Co',
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'warehouse_id' => $this->warehouseMain->id,
            'paid_amount' => 7000,
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'unit_price' => 3500,
                ],
            ],
        ]);

    $response->assertRedirect(route('sales.receipt', 1))
        ->assertSessionHas('success');

    $mainStock = WarehouseStock::where('warehouse_id', $this->warehouseMain->id)
        ->where('product_id', $this->product->id)
        ->first();

    expect($mainStock->quantity)->toBe(90);
});

test('internal stock transfer moves inventory from main warehouse to north warehouse', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('stock-transfers.store'), [
            'from_warehouse_id' => $this->warehouseMain->id,
            'to_warehouse_id' => $this->warehouseNorth->id,
            'transfer_date' => date('Y-m-d'),
            'notes' => 'Branch stock allocation',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 30,
                ],
            ],
        ]);

    $response->assertRedirect(route('stock-transfers.index'))
        ->assertSessionHas('success');

    $mainStock = WarehouseStock::where('warehouse_id', $this->warehouseMain->id)
        ->where('product_id', $this->product->id)
        ->first();

    $northStock = WarehouseStock::where('warehouse_id', $this->warehouseNorth->id)
        ->where('product_id', $this->product->id)
        ->first();

    expect($mainStock->quantity)->toBe(70)
        ->and($northStock->quantity)->toBe(30);

    $transfer = StockTransfer::first();
    expect($transfer)->not->toBeNull()
        ->and($transfer->from_warehouse_id)->toBe($this->warehouseMain->id)
        ->and($transfer->to_warehouse_id)->toBe($this->warehouseNorth->id);
});

test('stock transfer fails if source warehouse quantity is insufficient', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('stock-transfers.store'), [
            'from_warehouse_id' => $this->warehouseMain->id,
            'to_warehouse_id' => $this->warehouseNorth->id,
            'transfer_date' => date('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 500, // exceeds 100
                ],
            ],
        ]);

    $response->assertSessionHasErrors(['items']);

    $mainStock = WarehouseStock::where('warehouse_id', $this->warehouseMain->id)
        ->where('product_id', $this->product->id)
        ->first();

    expect($mainStock->quantity)->toBe(100);
});

test('product show page displays detail and warehouse stock tabs', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('products.show', $this->product));

    $response->assertOk()
        ->assertSee('Detail')
        ->assertSee('Stock')
        ->assertSee($this->warehouseMain->name)
        ->assertSee('100');
});

test('product can be created with multi warehouse stocks repeater', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('products.store'), [
            'name' => 'Cordless Screwdriver',
            'barcode' => '998877665544',
            'category_id' => $this->category->id,
            'purchase_price' => 1200,
            'selling_price' => 2000,
            'quantity' => 0,
            'alert_quantity' => 5,
            'warehouse_stocks' => [
                ['warehouse_id' => $this->warehouseMain->id, 'quantity' => 30],
                ['warehouse_id' => $this->warehouseNorth->id, 'quantity' => 20],
            ],
        ]);

    $response->assertRedirect(route('products.index'))
        ->assertSessionHas('success');

    $newProd = Product::where('barcode', '998877665544')->first();
    expect($newProd)->not->toBeNull()
        ->and($newProd->quantity)->toBe(50);

    $wsMain = WarehouseStock::where('product_id', $newProd->id)->where('warehouse_id', $this->warehouseMain->id)->first();
    $wsNorth = WarehouseStock::where('product_id', $newProd->id)->where('warehouse_id', $this->warehouseNorth->id)->first();

    expect($wsMain?->quantity)->toBe(30)
        ->and($wsNorth?->quantity)->toBe(20);
});

test('sale fails if product has insufficient stock in selected warehouse', function () {
    $customer = Customer::create([
        'company_id' => $this->company->id,
        'name' => 'Retail Buyer',
    ]);

    // Product has 0 stock in warehouseNorth
    $response = $this->actingAs($this->admin)
        ->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'warehouse_id' => $this->warehouseNorth->id,
            'paid_amount' => 3500,
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'unit_price' => 3500,
                ],
            ],
        ]);

    $response->assertSessionHas('error');
});

test('pos checkout fails if product has insufficient stock in selected warehouse', function () {
    // Product has 0 stock in warehouseNorth
    $response = $this->actingAs($this->admin)
        ->postJson(route('pos.checkout'), [
            'warehouse_id' => $this->warehouseNorth->id,
            'payment_method' => 'cash',
            'paid_amount' => 3500,
            'items' => [
                [
                    'id' => $this->product->id,
                    'quantity' => 5,
                    'price' => 3500,
                ],
            ],
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['items']);
});

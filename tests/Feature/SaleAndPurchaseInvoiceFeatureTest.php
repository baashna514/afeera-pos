<?php

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\Unit;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it provides fetch from sale order endpoint returning json with customer and items', function () {
    $customer = Customer::create(['name' => 'Sara Ali', 'phone' => '0300-1112223']);
    $unit = Unit::create(['name' => 'Piece', 'short_code' => 'pc', 'conversion_factor' => 1]);
    $category = Category::create(['name' => 'Gadgets', 'slug' => 'gadgets']);
    $product = Product::create([
        'name' => 'Smart Watch',
        'sku' => 'SW-01',
        'barcode' => 'BC-SW-01',
        'purchase_price' => 2000,
        'selling_price' => 3500,
        'quantity' => 20,
        'alert_quantity' => 2,
        'category_id' => $category->id,
        'unit_id' => $unit->id,
    ]);

    $so = SaleOrder::create([
        'so_number' => 'SO-FETCH-001',
        'customer_id' => $customer->id,
        'total_amount' => 7000,
        'status' => 'pending',
    ]);

    SaleOrderItem::create([
        'sale_order_id' => $so->id,
        'product_id' => $product->id,
        'unit_id' => $unit->id,
        'conversion_rate' => 1.0,
        'quantity' => 2,
        'unit_price' => 3500,
        'subtotal' => 7000,
    ]);

    $response = $this->getJson(route('sales.fetchFromOrder', $so));
    $response->assertOk()
        ->assertJson([
            'success' => true,
            'order' => [
                'id' => $so->id,
                'so_number' => 'SO-FETCH-001',
                'customer_id' => $customer->id,
            ],
        ]);
});

test('it creates a sale invoice with description, extra field one, and redirects to thermal receipt', function () {
    $customer = Customer::create(['name' => 'Usman Tariq', 'phone' => '0345-5432109']);
    $unit = Unit::create(['name' => 'Piece', 'short_code' => 'pc', 'conversion_factor' => 1]);
    $category = Category::create(['name' => 'General', 'slug' => 'gen']);
    $product = Product::create([
        'name' => 'Wireless Mouse',
        'sku' => 'WM-01',
        'barcode' => 'BC-WM-01',
        'purchase_price' => 800,
        'selling_price' => 1200,
        'quantity' => 25,
        'alert_quantity' => 5,
        'category_id' => $category->id,
        'unit_id' => $unit->id,
    ]);

    $payload = [
        'customer_id' => $customer->id,
        'sale_date' => date('Y-m-d'),
        'paid_amount' => 1000, // partial payment out of 2400
        'payment_method' => 'cash',
        'description' => 'Tested warranty 1 year',
        'extra_field_one' => 'REF-DOC-9988',
        'items' => [
            [
                'product_id' => $product->id,
                'unit_id' => $unit->id,
                'conversion_rate' => 1.0,
                'quantity' => 2,
                'unit_price' => 1200,
            ],
        ],
    ];

    $response = $this->post(route('sales.store'), $payload);

    $sale = Sale::latest()->first();
    expect($sale)->not->toBeNull();
    expect($sale->total_amount)->toBe('2400.00');
    expect($sale->paid_amount)->toBe('1000.00');
    expect($sale->due_amount)->toBe('1400.00');
    expect($sale->payment_status)->toBe('partially_paid');
    expect($sale->description)->toBe('Tested warranty 1 year');
    expect($sale->extra_field_one)->toBe('REF-DOC-9988');

    $response->assertRedirect(route('sales.receipt', $sale));

    // Check thermal receipt view rendered successfully
    $receiptResponse = $this->get(route('sales.receipt', $sale));
    $receiptResponse->assertOk()
        ->assertSee('SMART POS SYSTEM')
        ->assertSee('Return & Exchange Policy', false)
        ->assertSee('REF-DOC-9988')
        ->assertSee('Tested warranty 1 year');
});

test('it updates customer ledger when partial sale invoice is created', function () {
    $customer = Customer::create(['name' => 'Babar Azam', 'phone' => '0321-0000000']);
    $unit = Unit::create(['name' => 'Piece', 'short_code' => 'pc', 'conversion_factor' => 1]);
    $category = Category::create(['name' => 'Cricket', 'slug' => 'cric']);
    $product = Product::create([
        'name' => 'Bat Grip',
        'sku' => 'BG-01',
        'barcode' => 'BC-BG-01',
        'purchase_price' => 100,
        'selling_price' => 250,
        'quantity' => 50,
        'alert_quantity' => 5,
        'category_id' => $category->id,
        'unit_id' => $unit->id,
    ]);

    // Create invoice for 500 total, 200 paid, 300 due
    $this->post(route('sales.store'), [
        'customer_id' => $customer->id,
        'paid_amount' => 200,
        'payment_method' => 'cash',
        'items' => [
            [
                'product_id' => $product->id,
                'unit_id' => $unit->id,
                'conversion_rate' => 1.0,
                'quantity' => 2,
                'unit_price' => 250,
            ],
        ],
    ]);

    $ledgerResponse = $this->get(route('ledgers.customer', ['customer_id' => $customer->id]));
    $ledgerResponse->assertOk()
        ->assertSee('Sale Invoice')
        ->assertSee('Payment Received')
        ->assertSee('500')
        ->assertSee('200');
});

test('sales index displays separate total, paid, and due amount columns', function () {
    $customer = Customer::create(['name' => 'Rashid Khan', 'phone' => '0312-9988776']);
    $unit = Unit::create(['name' => 'Piece', 'short_code' => 'pc', 'conversion_factor' => 1]);
    $category = Category::create(['name' => 'General', 'slug' => 'gen-item']);
    $product = Product::create([
        'name' => 'Test Item',
        'sku' => 'TI-01',
        'barcode' => 'BC-TI-01',
        'purchase_price' => 10000,
        'selling_price' => 26000,
        'quantity' => 10,
        'alert_quantity' => 2,
        'category_id' => $category->id,
        'unit_id' => $unit->id,
    ]);

    // Create invoice for 52,000 total, 25,000 paid, 27,000 due
    $this->post(route('sales.store'), [
        'customer_id' => $customer->id,
        'paid_amount' => 25000,
        'payment_method' => 'cash',
        'items' => [
            [
                'product_id' => $product->id,
                'unit_id' => $unit->id,
                'conversion_rate' => 1.0,
                'quantity' => 2,
                'unit_price' => 26000,
            ],
        ],
    ]);

    $response = $this->get(route('sales.index'));
    $response->assertOk()
        ->assertSee('Total (Rs.)')
        ->assertSee('Paid (Rs.)')
        ->assertSee('Due (Rs.)')
        ->assertSee('52,000.00')
        ->assertSee('25,000.00')
        ->assertSee('27,000.00');
});

test('customer ledger sorts entries strictly in ascending chronological order', function () {
    $customer = Customer::create(['name' => 'Zaman Khan', 'phone' => '0300-7766554']);
    $unit = Unit::create(['name' => 'Piece', 'short_code' => 'pc', 'conversion_factor' => 1]);
    $category = Category::create(['name' => 'Sport', 'slug' => 'sport']);
    $product = Product::create([
        'name' => 'Ball',
        'sku' => 'BALL-01',
        'barcode' => 'BC-BALL-01',
        'purchase_price' => 500,
        'selling_price' => 1000,
        'quantity' => 100,
        'alert_quantity' => 10,
        'category_id' => $category->id,
        'unit_id' => $unit->id,
    ]);

    // Transaction 1 (Earlier): Sale of 51,500 total with 20,000 paid
    $sale1 = Sale::create([
        'customer_id' => $customer->id,
        'invoice_number' => 'SI-EARLY-01',
        'total_amount' => 51500,
        'paid_amount' => 20000,
        'due_amount' => 31500,
        'payment_status' => 'partially_paid',
        'payment_method' => 'cash',
        'created_at' => now()->subHours(5),
    ]);

    // Transaction 2 (Middle): Sale of 52,000 total with 25,000 paid
    $sale2 = Sale::create([
        'customer_id' => $customer->id,
        'invoice_number' => 'SI-MID-02',
        'total_amount' => 52000,
        'paid_amount' => 25000,
        'due_amount' => 27000,
        'payment_status' => 'partially_paid',
        'payment_method' => 'cash',
        'created_at' => now()->subHours(2),
    ]);

    // Transaction 3 (Latest): Voucher payment of 5,000 recorded last
    Voucher::create([
        'customer_id' => $customer->id,
        'voucher_number' => 'VCH-LATEST-03',
        'type' => 'receipt',
        'amount' => 5000,
        'payment_method' => 'cash',
        'voucher_date' => date('Y-m-d'),
        'created_at' => now()->subMinutes(10),
    ]);

    $response = $this->get(route('ledgers.customer', ['customer_id' => $customer->id]));
    $response->assertOk();

    // Verify SI-EARLY-01 appears before SI-MID-02, and VCH-LATEST-03 appears at the bottom
    $content = $response->getContent();
    $pos1 = strpos($content, 'SI-EARLY-01');
    $pos2 = strpos($content, 'SI-MID-02');
    $pos3 = strpos($content, 'VCH-LATEST-03');

    expect($pos1)->toBeLessThan($pos2);
    expect($pos2)->toBeLessThan($pos3);
});

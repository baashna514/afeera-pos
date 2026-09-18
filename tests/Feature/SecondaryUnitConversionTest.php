<?php

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;

test('product secondary unit divide operator calculates correct unit prices and available stock', function () {
    $company = Company::factory()->create();
    $boxUnit = Unit::create(['company_id' => $company->id, 'name' => 'Box', 'short_code' => 'box']);
    $stripUnit = Unit::create(['company_id' => $company->id, 'name' => 'Strip', 'short_code' => 'str']);
    $tabletUnit = Unit::create(['company_id' => $company->id, 'name' => 'Tablet', 'short_code' => 'tab']);

    $product = Product::create([
        'company_id' => $company->id,
        'name' => 'Panadol 500mg',
        'unit_id' => $boxUnit->id,
        'purchase_price' => 500.00,
        'selling_price' => 1000.00,
        'quantity' => 5, // 5 Boxes
        'alert_quantity' => 1,
    ]);

    // 1 Box = 10 Strips (divide operator)
    ProductUnit::create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'unit_id' => $stripUnit->id,
        'operator' => 'divide',
        'conversion_rate' => 10,
        'sale_price' => null, // Should calculate 1000 / 10 = 100
    ]);

    // 1 Box = 200 Tablets (divide operator)
    ProductUnit::create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'unit_id' => $tabletUnit->id,
        'operator' => 'divide',
        'conversion_rate' => 200,
        'sale_price' => null, // Should calculate 1000 / 200 = 5
    ]);

    $product->load(['unit', 'secondaryUnits.unit']);
    $availableUnits = $product->available_units;

    expect($availableUnits)->toHaveCount(3);

    // Base unit Box
    expect($availableUnits[0]['unit_id'])->toBe($boxUnit->id);
    expect($availableUnits[0]['sale_price'])->toBe(1000.00);

    // Strip secondary unit
    expect($availableUnits[1]['unit_id'])->toBe($stripUnit->id);
    expect($availableUnits[1]['sale_price'])->toBe(100.00);
    expect($availableUnits[1]['conversion_rate'])->toBe(0.1); // Effective multiplier: 1/10

    // Tablet secondary unit
    expect($availableUnits[2]['unit_id'])->toBe($tabletUnit->id);
    expect($availableUnits[2]['sale_price'])->toBe(5.00);
    expect($availableUnits[2]['conversion_rate'])->toBe(0.005); // Effective multiplier: 1/200
});

test('selling secondary unit with divide operator deducts stock accurately in base units', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $warehouse = Warehouse::create(['company_id' => $company->id, 'name' => 'Main Warehouse', 'is_default' => true]);

    $boxUnit = Unit::create(['company_id' => $company->id, 'name' => 'Box', 'short_code' => 'box']);
    $stripUnit = Unit::create(['company_id' => $company->id, 'name' => 'Strip', 'short_code' => 'str']);

    $product = Product::create([
        'company_id' => $company->id,
        'name' => 'Panadol 500mg',
        'unit_id' => $boxUnit->id,
        'purchase_price' => 500.00,
        'selling_price' => 1000.00,
        'quantity' => 10, // 10 Boxes
        'alert_quantity' => 1,
    ]);

    // 1 Box = 10 Strips
    ProductUnit::create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'unit_id' => $stripUnit->id,
        'operator' => 'divide',
        'conversion_rate' => 10,
    ]);

    // Sell 10 Strips (which equals 1 Box, conversion_rate = 0.1)
    $response = $this->actingAs($user)->post(route('sales.store'), [
        'customer_id' => \App\Models\Customer::factory()->create(['company_id' => $company->id])->id,
        'warehouse_id' => $warehouse->id,
        'sale_date' => date('Y-m-d'),
        'paid_amount' => 1000.00,
        'items' => [
            [
                'product_id' => $product->id,
                'unit_id' => $stripUnit->id,
                'conversion_rate' => 0.1, // 1/10
                'quantity' => 10, // 10 strips = 1 box
                'unit_price' => 100.00,
            ],
        ],
    ]);

    $response->assertRedirect();
    
    // Check product stock: initial 10 Boxes - 1 Box (10 strips) = 9 Boxes remaining!
    $product->refresh();
    expect((float) $product->quantity)->toBe(9.0);
});

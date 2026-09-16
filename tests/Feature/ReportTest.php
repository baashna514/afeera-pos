<?php

use App\Models\Company;
use App\Models\User;

beforeEach(function () {
    $this->company = Company::firstOrCreate(
        ['code' => 'COMP-TEST'],
        ['name' => 'Report Test Company', 'currency' => 'PKR']
    );

    $this->user = User::first() ?? User::factory()->create([
        'company_id' => $this->company->id,
    ]);

    $this->user->update(['company_id' => $this->company->id]);
});

test('user can view all 20 report types without errors', function ($type) {
    $response = $this->actingAs($this->user)
        ->get(route('reports.index', ['type' => $type]));

    $response->assertOk();
})->with([
    'sales_summary',
    'sales_detail',
    'sales_by_product',
    'sales_by_customer',
    'sales_by_warehouse',
    'sales_returns',
    'purchase_summary',
    'purchase_by_product',
    'purchase_by_vendor',
    'purchase_returns',
    'current_stock',
    'stock_ledger',
    'stock_movement',
    'stock_valuation',
    'low_stock',
    'customer_aging',
    'vendor_aging',
    'party_ledger',
    'profit_loss',
    'cashier_closing',
]);

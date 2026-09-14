<?php

use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\DayBook;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\OwnerSeeder;

beforeEach(function () {
    $this->seed(OwnerSeeder::class);

    $this->company = Company::where('code', 'COMP-001')->first();
    $this->admin = User::where('email', 'admin@smartpos.com')->first();

    $this->category = Category::create([
        'company_id' => $this->company->id,
        'name' => 'General Items',
    ]);

    $this->customer = Customer::create([
        'company_id' => $this->company->id,
        'name' => 'Walk-in Customer',
        'phone' => '03001234567',
        'is_active' => true,
    ]);

    $this->product = Product::create([
        'company_id' => $this->company->id,
        'category_id' => $this->category->id,
        'name' => 'Sample Product',
        'code' => 'P-101',
        'cost_price' => 500,
        'sale_price' => 800,
        'stock_quantity' => 50,
        'min_stock_alert' => 5,
        'unit' => 'pcs',
    ]);
});

test('user can set daily opening cash balance for day book', function () {
    $today = date('Y-m-d');

    $response = $this->actingAs($this->admin)
        ->post(route('day-book.store-opening'), [
            'date' => $today,
            'opening_balance' => 500.00,
            'notes' => 'Opening counter drawer cash',
        ]);

    $response->assertRedirect()
        ->assertSessionHas('success');

    $dayBook = DayBook::where('company_id', $this->company->id)
        ->whereDate('date', $today)
        ->first();

    expect($dayBook)->not->toBeNull()
        ->and((float) $dayBook->opening_balance)->toBe(500.00);
});

test('day book index aggregates opening cash, cash sales, expenses, and net drawer balance', function () {
    $today = date('Y-m-d');

    // 1. Set Opening Cash = 1000
    DayBook::create([
        'company_id' => $this->company->id,
        'date' => $today,
        'opening_balance' => 1000.00,
        'notes' => 'Initial balance',
    ]);

    // 2. Add Cash Sale = 800
    $sale = Sale::create([
        'company_id' => $this->company->id,
        'customer_id' => $this->customer->id,
        'user_id' => $this->admin->id,
        'invoice_number' => 'INV-TEST-01',
        'subtotal' => 800,
        'tax_amount' => 0,
        'discount_amount' => 0,
        'total_amount' => 800,
        'paid_amount' => 800,
        'due_amount' => 0,
        'payment_method' => 'cash',
        'status' => 'completed',
        'sale_date' => $today,
    ]);

    // 3. Add Cash Expense = 200
    $expCat = ExpenseCategory::create([
        'company_id' => $this->company->id,
        'name' => 'Tea & Refreshment',
        'is_active' => true,
    ]);

    Expense::create([
        'company_id' => $this->company->id,
        'expense_category_id' => $expCat->id,
        'user_id' => $this->admin->id,
        'amount' => 200,
        'expense_date' => $today,
        'payment_method' => 'cash',
        'reference_no' => 'EXP-101',
    ]);

    // 4. Request Day Book index
    $response = $this->actingAs($this->admin)
        ->get(route('day-book.index'));

    $response->assertOk()
        ->assertSee('1,000.00') // Opening balance
        ->assertSee('800.00')   // Cash in
        ->assertSee('200.00')   // Cash out
        ->assertSee('1,600.00'); // Net drawer expected cash (1000 + 800 - 200)
});

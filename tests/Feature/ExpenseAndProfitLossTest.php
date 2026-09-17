<?php

use App\Models\Category;
use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Database\Seeders\OwnerSeeder;

beforeEach(function () {
    $this->seed(OwnerSeeder::class);

    $this->company = Company::where('code', 'COMP-001')->first();
    $this->admin = User::where('email', 'superadmin@gmail.com')->first();
    $this->cat = Category::create([
        'company_id' => $this->company->id,
        'name' => 'Electronics',
    ]);
});

test('super admin can create and manage expense categories', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('expense-categories.store'), [
            'name' => 'Electricity & Power',
            'description' => 'Monthly store power bill',
            'is_active' => 1,
        ]);

    $response->assertRedirect(route('expense-categories.index'))
        ->assertSessionHas('success');

    $category = ExpenseCategory::where('name', 'Electricity & Power')->first();
    expect($category)->not->toBeNull()
        ->and($category->company_id)->toBe($this->company->id);
});

test('super admin can record business expenses', function () {
    $category = ExpenseCategory::create([
        'company_id' => $this->company->id,
        'name' => 'Store Maintenance',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('expenses.store'), [
            'expense_category_id' => $category->id,
            'amount' => 1500.00,
            'expense_date' => date('Y-m-d'),
            'payment_method' => 'cash',
        'sale_date' => date('Y-m-d'),
            'reference_no' => 'BILL-1001',
            'note' => 'Replaced shop lights',
        ]);

    $response->assertRedirect(route('expenses.index'))
        ->assertSessionHas('success');

    $expense = Expense::where('reference_no', 'BILL-1001')->first();
    expect($expense)->not->toBeNull()
        ->and((float) $expense->amount)->toBe(1500.00)
        ->and($expense->company_id)->toBe($this->company->id);
});

test('dashboard accurately calculates profit and loss with date filters', function () {
    // 1. Create a product bought for Rs 100, selling price Rs 200
    $product = Product::create([
        'company_id' => $this->company->id,
        'category_id' => $this->cat->id,
        'name' => 'Super Widget',
        'code' => 'WIDGET-01',
        'purchase_price' => 100.00,
        'selling_price' => 200.00,
        'quantity' => 50,
        'min_stock' => 5,
        'is_active' => true,
    ]);

    // 2. Record a Sale of 2 widgets (Revenue = Rs 400, COGS = Rs 200, Gross Profit = Rs 200)
    $sale = Sale::create([
        'company_id' => $this->company->id,
        'invoice_number' => 'INV-TEST-01',
        'total_amount' => 400.00,
        'paid_amount' => 400.00,
        'due_amount' => 0.00,
        'payment_status' => 'paid',
        'payment_method' => 'cash',
        'sale_date' => date('Y-m-d'),
        'created_by' => $this->admin->id,
    ]);

    SaleItem::create([
        'company_id' => $this->company->id,
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'base_quantity' => 2,
        'price' => 200.00,
        'subtotal' => 400.00,
    ]);

    // 3. Record an Expense of Rs 50 (Net Profit should be Rs 150)
    $category = ExpenseCategory::create([
        'company_id' => $this->company->id,
        'name' => 'Tea',
        'is_active' => true,
    ]);

    Expense::create([
        'company_id' => $this->company->id,
        'expense_category_id' => $category->id,
        'amount' => 50.00,
        'expense_date' => date('Y-m-d'),
        'payment_method' => 'cash',
        'sale_date' => date('Y-m-d'),
        'created_by' => $this->admin->id,
    ]);

    // 4. Test Dashboard Calculation
    $this->actingAs($this->admin)
        ->get(route('dashboard', ['preset_filter' => 'today']))
        ->assertOk()
        ->assertSee('400.00') // Filtered Sales
        ->assertSee('50.00')  // Filtered Expenses
        ->assertSee('150.00'); // Today's & Filtered Net Profit
});

test('reports page displays product-wise profit breakdown and P&L statement', function () {
    $product = Product::create([
        'company_id' => $this->company->id,
        'category_id' => $this->cat->id,
        'name' => 'Profit Gizmo',
        'code' => 'GIZMO-01',
        'purchase_price' => 300.00,
        'selling_price' => 500.00,
        'quantity' => 20,
        'min_stock' => 2,
        'is_active' => true,
    ]);

    $sale = Sale::create([
        'company_id' => $this->company->id,
        'invoice_number' => 'INV-GIZMO-01',
        'total_amount' => 1000.00,
        'paid_amount' => 1000.00,
        'payment_status' => 'paid',
        'payment_method' => 'cash',
        'sale_date' => date('Y-m-d'),
        'created_by' => $this->admin->id,
    ]);

    SaleItem::create([
        'company_id' => $this->company->id,
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'base_quantity' => 2,
        'price' => 500.00,
        'subtotal' => 1000.00,
    ]);

    $this->actingAs($this->admin)
        ->get(route('reports.index', ['type' => 'profit_loss', 
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d'),
        ]))
        ->assertOk()
        ->assertSee('400.00')
        ->assertSee('Profit &amp; Loss Statement', false)
        ->assertSee('Gross Profit');
});

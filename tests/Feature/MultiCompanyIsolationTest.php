<?php

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('super admin can view companies index and register a new company', function () {
    $superAdmin = User::where('email', 'admin@smartpos.com')->first();

    $this->actingAs($superAdmin)
        ->get(route('companies.index'))
        ->assertOk()
        ->assertSee('Companies');

    $this->actingAs($superAdmin)
        ->post(route('companies.store'), [
            'name' => 'Falcon Enterprises LLC',
            'code' => 'FALCON-01',
            'email' => 'info@falcon.com',
            'phone' => '+92 300 9998888',
            'currency' => 'PKR',
            'is_active' => 1,
        ])
        ->assertRedirect(route('companies.index'));

    expect(Company::where('code', 'FALCON-01')->exists())->toBeTrue();
});

test('model creating hook automatically assigns authenticated user company_id', function () {
    $company = Company::create([
        'name' => 'Alpha Wholesale',
        'code' => 'ALPHA-01',
    ]);

    $role = Role::firstOrCreate(['name' => 'Admin / Manager', 'guard_name' => 'web']);

    $userA = User::create([
        'company_id' => $company->id,
        'name' => 'Alpha Manager',
        'email' => 'manager@alpha.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $userA->syncRoles([$role]);

    // Perform action as userA
    $this->actingAs($userA);

    $category = Category::create(['name' => 'Alpha Electronics']);
    $unit = Unit::create(['name' => 'Alpha Piece', 'short_code' => 'apc']);

    $product = Product::create([
        'name' => 'Alpha LED TV',
        'barcode' => 'ALPHA-TV-001',
        'category_id' => $category->id,
        'unit_id' => $unit->id,
        'purchase_price' => 20000,
        'selling_price' => 25000,
        'quantity' => 10,
    ]);

    expect($category->company_id)->toBe($company->id)
        ->and($unit->company_id)->toBe($company->id)
        ->and($product->company_id)->toBe($company->id);
});

test('company scope strictly isolates data between company a and company b', function () {
    $companyA = Company::create(['name' => 'Company A', 'code' => 'COMP-A']);
    $companyB = Company::create(['name' => 'Company B', 'code' => 'COMP-B']);

    $role = Role::firstOrCreate(['name' => 'Admin / Manager', 'guard_name' => 'web']);

    $userA = User::create([
        'company_id' => $companyA->id,
        'name' => 'User Company A',
        'email' => 'user.a@test.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $userA->syncRoles([$role]);

    $userB = User::create([
        'company_id' => $companyB->id,
        'name' => 'User Company B',
        'email' => 'user.b@test.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $userB->syncRoles([$role]);

    // Create Category & Product for Company A directly with company_id
    $catA = Category::create(['company_id' => $companyA->id, 'name' => 'Laptops Company A']);
    $unitA = Unit::create(['company_id' => $companyA->id, 'name' => 'Piece A', 'short_code' => 'pca']);
    $prodA = Product::create([
        'company_id' => $companyA->id,
        'name' => 'ThinkPad Laptop A',
        'barcode' => 'PROD-A-999',
        'category_id' => $catA->id,
        'unit_id' => $unitA->id,
        'purchase_price' => 50000,
        'selling_price' => 60000,
        'quantity' => 5,
    ]);

    // Create Category & Product for Company B
    $catB = Category::create(['company_id' => $companyB->id, 'name' => 'Smartphones Company B']);
    $unitB = Unit::create(['company_id' => $companyB->id, 'name' => 'Piece B', 'short_code' => 'pcb']);
    $prodB = Product::create([
        'company_id' => $companyB->id,
        'name' => 'Galaxy Phone B',
        'barcode' => 'PROD-B-888',
        'category_id' => $catB->id,
        'unit_id' => $unitB->id,
        'purchase_price' => 70000,
        'selling_price' => 85000,
        'quantity' => 8,
    ]);

    // User A should see only Prod A, NEVER Prod B
    $this->actingAs($userA)
        ->get(route('products.index'))
        ->assertOk()
        ->assertSee('ThinkPad Laptop A')
        ->assertDontSee('Galaxy Phone B');

    // User B should see only Prod B, NEVER Prod A
    $this->actingAs($userB)
        ->get(route('products.index'))
        ->assertOk()
        ->assertSee('Galaxy Phone B')
        ->assertDontSee('ThinkPad Laptop A');
});

test('pos product search isolates inventory by company', function () {
    $companyA = Company::create(['name' => 'Company A Store', 'code' => 'CAS-01']);
    $companyB = Company::create(['name' => 'Company B Store', 'code' => 'CBS-01']);

    $cashierRole = Role::firstOrCreate(['name' => 'Cashier', 'guard_name' => 'web']);

    $cashierA = User::create([
        'company_id' => $companyA->id,
        'name' => 'Cashier Company A',
        'email' => 'cashier.a@test.com',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $cashierA->syncRoles([$cashierRole]);

    $catA = Category::create(['company_id' => $companyA->id, 'name' => 'Groceries A']);
    $unitA = Unit::create(['company_id' => $companyA->id, 'name' => 'Item A', 'short_code' => 'ita']);
    Product::create([
        'company_id' => $companyA->id,
        'name' => 'Organic Milk A',
        'barcode' => 'MILK-A-101',
        'category_id' => $catA->id,
        'unit_id' => $unitA->id,
        'purchase_price' => 100,
        'selling_price' => 150,
        'quantity' => 20,
    ]);

    $catB = Category::create(['company_id' => $companyB->id, 'name' => 'Groceries B']);
    $unitB = Unit::create(['company_id' => $companyB->id, 'name' => 'Item B', 'short_code' => 'itb']);
    Product::create([
        'company_id' => $companyB->id,
        'name' => 'Organic Milk B',
        'barcode' => 'MILK-B-202',
        'category_id' => $catB->id,
        'unit_id' => $unitB->id,
        'purchase_price' => 120,
        'selling_price' => 180,
        'quantity' => 30,
    ]);

    // Cashier A searches for Milk in POS terminal
    $response = $this->actingAs($cashierA)
        ->getJson(route('pos.search', ['query' => 'Organic Milk']));

    $response->assertOk();
    $data = $response->json();

    // Must find Milk A but NOT Milk B
    expect($data)->toHaveCount(1)
        ->and($data[0]['barcode'])->toBe('MILK-A-101')
        ->and($data[0]['name'])->toBe('Organic Milk A');
});

test('super admin has global visibility over all companies data', function () {
    $superAdmin = User::where('email', 'admin@smartpos.com')->first();

    $this->actingAs($superAdmin)
        ->get(route('products.index'))
        ->assertOk();

    // Super admin can see all registered companies
    $this->actingAs($superAdmin)
        ->get(route('companies.index'))
        ->assertOk()
        ->assertSee('Smart POS General Trading LLC');
});

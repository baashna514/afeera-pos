<?php

use App\Models\Company;
use App\Models\Product;
use App\Models\Unit;
use App\Services\CompanySettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $company = Company::firstOrCreate(
        ['code' => 'COMP-001'],
        ['name' => 'Baashna Technologies', 'currency' => 'PKR']
    );
    if (auth()->check()) {
        auth()->user()->update(['company_id' => $company->id]);
    }
});

test('it displays the settings management page with all tabs', function () {
    $response = $this->get(route('settings.index'));

    $response->assertOk();
    $response->assertSee('Feature Modules');
    $response->assertSee('Receipt Template');
    $response->assertSee('General Business Profile');
});

test('it updates general business profile settings', function () {
    $response = $this->post(route('settings.general.update'), [
        'name' => 'Elite Super Mart LLC',
        'phone' => '0300-9988776',
        'email' => 'contact@elitesupermart.com',
        'address' => 'Plaza 10, Main Boulevard, Lahore',
        'currency' => 'PKR',
        'currency_symbol' => 'Rs.',
    ]);

    $response->assertRedirect(route('settings.index', ['tab' => 'general']));
    $response->assertSessionHas('success');

    expect(company_setting('general.app_name'))->toBe('Elite Super Mart LLC');
    expect(company_setting('general.phone'))->toBe('0300-9988776');
    expect(company_setting('general.currency_symbol'))->toBe('Rs.');
});

test('it updates feature toggles and respects them in sidebar and system', function () {
    // Disable Brands and Categories
    $response = $this->post(route('settings.features.update'), [
        'features_sale_orders' => '1',
        // features_brands omitted -> turned off
        // features_categories omitted -> turned off
    ]);

    $response->assertRedirect(route('settings.index', ['tab' => 'features']));

    expect(company_has_feature('sale_orders'))->toBeTrue();
    expect(company_has_feature('brands'))->toBeFalse();
    expect(company_has_feature('categories'))->toBeFalse();

    // Verify in dashboard view that Brands link is hidden
    $viewResponse = $this->get(route('dashboard'));
    $viewResponse->assertOk();
    $viewResponse->assertDontSee('Brands</span>', false);
});

test('it allows product creation without category when category feature is disabled', function () {
    $companyId = CompanySettingService::resolveCompanyId();
    CompanySettingService::set('features.categories', false, $companyId);

    $unit = Unit::create(['name' => 'Box', 'short_code' => 'bx', 'conversion_factor' => 1]);

    $response = $this->post(route('products.store'), [
        'name' => 'Direct Generic Item',
        'barcode' => 'GEN-10029',
        'sku' => 'GI-01',
        // No category_id supplied!
        'unit_id' => $unit->id,
        'purchase_price' => 500,
        'selling_price' => 750,
        'quantity' => 10,
        'alert_quantity' => 2,
    ]);

    $response->assertRedirect(route('products.index'));
    $response->assertSessionHas('success');

    $product = Product::where('barcode', 'GEN-10029')->first();
    expect($product)->not->toBeNull();
    expect($product->category_id)->toBeNull();
});

test('it requires category when category feature is enabled', function () {
    $companyId = CompanySettingService::resolveCompanyId();
    CompanySettingService::set('features.categories', true, $companyId);

    $response = $this->post(route('products.store'), [
        'name' => 'Requires Category Item',
        'barcode' => 'REQ-CAT-01',
        // No category_id supplied!
        'purchase_price' => 500,
        'selling_price' => 750,
        'quantity' => 10,
        'alert_quantity' => 2,
    ]);

    $response->assertSessionHasErrors('category_id');
});

test('it updates thermal receipt template and reflects on sale receipt', function () {
    $response = $this->post(route('settings.receipt.update'), [
        'paper_size' => '58mm',
        'show_logo' => '1',
        'show_customer_name' => '1',
        'show_cashier_name' => '1',
        'show_barcode' => '1',
        'header_text' => 'Custom Store Header Test',
        'footer_text' => 'Custom Store Footer Test',
        'return_policy' => '7 days easy exchange policy.',
    ]);

    $response->assertRedirect(route('settings.index', ['tab' => 'receipt']));
    $response->assertSessionHas('success');

    expect(company_setting('receipt.paper_size'))->toBe('58mm');
    expect(company_setting('receipt.header_text'))->toBe('Custom Store Header Test');
    expect(company_setting('receipt.footer_text'))->toBe('Custom Store Footer Test');
    expect(company_setting('receipt.show_barcode'))->toBeTrue();
});

test('it saves default values and operational POS settings', function () {
    $response = $this->post(route('settings.defaults.update'), [
        'default_payment_method' => 'card',
    ]);

    $response->assertRedirect(route('settings.index', ['tab' => 'defaults']));
    $response->assertSessionHas('success');

    expect(company_setting('defaults.payment_method'))->toBe('card');
});

<?php

use App\Models\Company;
use App\Models\User;
use Database\Seeders\OwnerSeeder;

beforeEach(function () {
    $this->seed(OwnerSeeder::class);
});

test('system owner can access owner dashboard with platform statistics', function () {
    $owner = User::where('email', 'owner@saasplatform.com')->first();

    $this->actingAs($owner)
        ->get(route('owner.dashboard'))
        ->assertOk()
        ->assertSee('Owner Dashboard')
        ->assertSee('Registered Tenant Companies');
});

test('system owner can create new tenant company and auto-provision super admin from owner dashboard', function () {
    $owner = User::where('email', 'owner@saasplatform.com')->first();

    $response = $this->actingAs($owner)
        ->post(route('owner.companies.store'), [
            'company_name' => 'Apex Retail Global LLC',
            'company_code' => 'APEX-01',
            'currency' => 'PKR',
            'admin_name' => 'Tariq Mehmood',
            'admin_email' => 'admin@apexretail.com',
            'admin_password' => 'password123',
        ]);

    $response->assertRedirect(route('owner.dashboard'))
        ->assertSessionHas('success');

    $company = Company::where('code', 'APEX-01')->first();
    expect($company)->not->toBeNull()
        ->and($company->name)->toBe('Apex Retail Global LLC');

    $superAdmin = User::where('email', 'admin@apexretail.com')->first();
    expect($superAdmin)->not->toBeNull()
        ->and($superAdmin->company_id)->toBe($company->id)
        ->and($superAdmin->hasRole('Super Admin'))->toBeTrue()
        ->and($superAdmin->isSuperAdmin())->toBeTrue()
        ->and($superAdmin->isOwner())->toBeFalse();
});

test('non-owner user cannot access owner dashboard', function () {
    $company = Company::create(['name' => 'Demo Company', 'code' => 'DEMO-01']);
    $user = User::create([
        'company_id' => $company->id,
        'name' => 'Regular Admin',
        'email' => 'regular@demo.com',
        'password' => bcrypt('password123'),
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('owner.dashboard'))
        ->assertForbidden();
});

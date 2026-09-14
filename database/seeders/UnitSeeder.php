<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = Company::first()?->id ?? 1;

        // 1. Base Units
        $piece = Unit::firstOrCreate(['short_code' => 'pc'], [
            'company_id' => $companyId,
            'name' => 'Piece',
            'base_unit_id' => null,
            'operator' => '*',
            'conversion_factor' => 1,
        ]);

        $kg = Unit::firstOrCreate(['short_code' => 'kg'], [
            'company_id' => $companyId,
            'name' => 'Kilogram',
            'base_unit_id' => null,
            'operator' => '*',
            'conversion_factor' => 1,
        ]);

        $liter = Unit::firstOrCreate(['short_code' => 'ltr'], [
            'company_id' => $companyId,
            'name' => 'Liter',
            'base_unit_id' => null,
            'operator' => '*',
            'conversion_factor' => 1,
        ]);

        $meter = Unit::firstOrCreate(['short_code' => 'm'], [
            'company_id' => $companyId,
            'name' => 'Meter',
            'base_unit_id' => null,
            'operator' => '*',
            'conversion_factor' => 1,
        ]);

        // 2. Derived Units (Conversions)
        Unit::firstOrCreate(['short_code' => 'box'], [
            'company_id' => $companyId,
            'name' => 'Box (10 Pcs)',
            'base_unit_id' => $piece->id,
            'operator' => '*',
            'conversion_factor' => 10,
        ]);

        Unit::firstOrCreate(['short_code' => 'ctn'], [
            'company_id' => $companyId,
            'name' => 'Carton (24 Pcs)',
            'base_unit_id' => $piece->id,
            'operator' => '*',
            'conversion_factor' => 24,
        ]);

        Unit::firstOrCreate(['short_code' => 'dz'], [
            'company_id' => $companyId,
            'name' => 'Dozen (12 Pcs)',
            'base_unit_id' => $piece->id,
            'operator' => '*',
            'conversion_factor' => 12,
        ]);

        Unit::firstOrCreate(['short_code' => 'pack'], [
            'company_id' => $companyId,
            'name' => 'Pack (6 Pcs)',
            'base_unit_id' => $piece->id,
            'operator' => '*',
            'conversion_factor' => 6,
        ]);

        Unit::firstOrCreate(['short_code' => 'g'], [
            'company_id' => $companyId,
            'name' => 'Gram',
            'base_unit_id' => $kg->id,
            'operator' => '/',
            'conversion_factor' => 1000,
        ]);

        Unit::firstOrCreate(['short_code' => 'ml'], [
            'company_id' => $companyId,
            'name' => 'Milliliter',
            'base_unit_id' => $liter->id,
            'operator' => '/',
            'conversion_factor' => 1000,
        ]);
    }
}

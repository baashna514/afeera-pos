<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'users',
        'products',
        'categories',
        'units',
        'customers',
        'vendors',
        'sales',
        'sale_items',
        'sale_orders',
        'sale_order_items',
        'sale_returns',
        'sale_return_items',
        'purchases',
        'purchase_items',
        'purchase_orders',
        'purchase_order_items',
        'purchase_returns',
        'purchase_return_items',
        'stock_movements',
        'product_units',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'company_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('company_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('companies')
                        ->nullOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'company_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('company_id');
                });
            }
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('customer_id');
            }
            if (! Schema::hasColumn('sales', 'discount_type')) {
                $table->string('discount_type', 20)->default('percentage')->after('subtotal');
            }
            if (! Schema::hasColumn('sales', 'discount_value')) {
                $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
            }
            if (! Schema::hasColumn('sales', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_value');
            }
            if (! Schema::hasColumn('sales', 'has_overall_discount')) {
                $table->boolean('has_overall_discount')->default(false)->after('discount_amount');
            }
        });

        Schema::table('sale_items', function (Blueprint $table) {
            if (! Schema::hasColumn('sale_items', 'discount_percentage')) {
                $table->decimal('discount_percentage', 8, 2)->default(0)->after('price');
            }
            if (! Schema::hasColumn('sale_items', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_percentage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'discount_type', 'discount_value', 'discount_amount', 'has_overall_discount']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['discount_percentage', 'discount_amount']);
        });
    }
};

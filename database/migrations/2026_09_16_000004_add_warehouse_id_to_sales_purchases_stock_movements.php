<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('customer_id')->constrained('warehouses')->nullOnDelete();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('vendor_id')->constrained('warehouses')->nullOnDelete();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('product_id')->constrained('warehouses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->string('voucher_number')->unique();
            $table->enum('type', ['receipt', 'payment'])->default('receipt'); // receipt = Customer Cash In, payment = Vendor Cash Out
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->onDelete('cascade');
            $table->date('voucher_date');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'online'])->default('cash');
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};

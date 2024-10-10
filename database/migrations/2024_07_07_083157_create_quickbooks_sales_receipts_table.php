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
        Schema::create('quickbooks_sales_receipts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('quickbooks_id')->unique();
            $table->string('customer_id')->nullable();
            $table->string('salesforce_opportunity_id')->nullable();
            $table->string('salesforce_payment_id')->nullable();
            $table->string('name')->nullable();
            $table->string('string_value')->nullable();
            $table->string('doc_number')->nullable();
            $table->date('txn_date')->nullable();
            $table->string('currency_ref')->nullable();
            $table->string('exchange_rate')->nullable();
            $table->decimal('total_amount')->nullable();
            $table->string('payment_method_ref')->nullable();
            $table->dateTimeTz('create_time')->nullable();
            $table->dateTimeTz('last_updated_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quickbooks_sales_receipts');
    }
};

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
        Schema::create('quickbooks_payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('quickbooks_id')->unique();
            $table->bigInteger('quickbooks_invoice_id');
            $table->foreign('quickbooks_invoice_id')->references('quickbooks_id')->on('quickbooks_invoices')->cascadeOnDelete();
            $table->string('salesforce_payment_id')->nullable();
            $table->date('txn_date')->nullable();
            $table->string('currency_ref')->nullable();
            $table->string('exchange_rate')->nullable();
            $table->decimal('total_amount')->nullable();
            $table->string('payment_method_ref')->nullable();
            $table->dateTime('create_time')->nullable();
            $table->dateTime('last_updated_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quickbooks_payments');
    }
};

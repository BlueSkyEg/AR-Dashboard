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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->nullable()->constrained();
            $table->foreignId('subscription_id')->nullable()->constrained();
            $table->foreignId('donation_form_id')->nullable()->constrained();
            $table->foreignId('payment_method_id')->nullable()->constrained();
            $table->string('stripe_transaction_id')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency')->nullable();
            $table->longText('billing_comment')->nullable();
            $table->dateTime('completed_date')->nullable();
            $table->string('status')->default('pending')->nullable();
            $table->boolean('anonymous_donation')->default(false)->nullable();
            $table->boolean('live_mode')->nullable();
            $table->string('donor_ip')->nullable();
            $table->string('cs_exchange_rate')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};

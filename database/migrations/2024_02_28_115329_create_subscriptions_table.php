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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->nullable()->constrained();
            $table->foreignId('donation_form_id')->nullable()->constrained();
            $table->unsignedBigInteger('parent_donation_id')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->string('period', 20)->nullable();
            $table->decimal('initial_amount', 10, 2)->nullable();
            $table->decimal('recurring_amount', 10, 2)->nullable();
            $table->dateTime('completed_date')->nullable();
            $table->dateTime('expiration_date')->nullable();
            $table->string('status')->default('pending')->nullable();
            $table->boolean('live_mode')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

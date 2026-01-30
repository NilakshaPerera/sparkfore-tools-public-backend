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
        Schema::create('customer_products', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('product_id');
            $table->enum('base_pricing_method', ['increase', 'decrease'])->default('increase');
            $table->string('base_price_increase_yearly');
            $table->string('base_price_per_user_increase_yearly');
            $table->enum('per_user_pricing_method', ['increase', 'decrease'])->default('increase');
            $table->boolean('include_maintenance')->default(true);
            $table->timestamps(); // This creates both created_at and updated_at columns as timestamps.

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_products');
    }
};

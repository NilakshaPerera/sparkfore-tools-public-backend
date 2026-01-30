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
        Schema::create('plugin_available_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plugin_id');
            $table->unsignedBigInteger('customer_id');
            $table->timestamps(); // This creates both created_at and updated_at columns as timestamps.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugin_available_customers');
    }
};

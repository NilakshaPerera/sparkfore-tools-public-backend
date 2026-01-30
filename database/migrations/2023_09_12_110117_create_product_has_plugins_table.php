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
        Schema::create('product_has_plugins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('plugin_id');
            $table->integer('selected_version_type')->nullable();
            $table->string('selected_version')->nullable();
            $table->string('environment')->nullable();
            $table->timestamps(); // This creates both created_at and updated_at columns as timestamps.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_has_plugins');
    }
};

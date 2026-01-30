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
        Schema::create('product_has_plugin_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_has_plugin_id');
            $table->boolean('include');
            $table->string('selected_version');
            $table->string('environment');
            $table->timestamps(); // This creates both created_at and updated_at columns as timestamps.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_has_plugin_versions');
    }
};

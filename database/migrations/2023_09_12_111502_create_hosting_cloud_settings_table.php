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
        Schema::create('hosting_cloud_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hosting_id');
            $table->unsignedBigInteger('base_package_id');
            $table->unsignedBigInteger('hosting_provider_id');
            $table->decimal('backup_price_monthly', 10, 2);
            $table->decimal('staging_price_monthly', 10, 2);
            $table->boolean('active');
            $table->timestamps(); // This creates both created_at and updated_at columns as timestamps.

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosting_cloud_settings');
    }
};

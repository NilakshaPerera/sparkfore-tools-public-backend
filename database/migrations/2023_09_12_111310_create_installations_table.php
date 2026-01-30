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
        Schema::create('installations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('installation_target_type_id');
            $table->unsignedBigInteger('customer_product_id');
            $table->unsignedBigInteger('hosting_provider_id')->nullable();
            $table->unsignedBigInteger('hosting_id');
            $table->unsignedBigInteger('hosting_type_id')->nullable();
            $table->float('available_disk_space')->nullable();
            $table->dateTime('last_build')->nullable();
            $table->enum('domain_type', ['standard','custom']);
            $table->string('url');
            $table->boolean('include_staging_package')->default(false);
            $table->boolean('include_backup')->default(false);
            $table->boolean('general_terms_agreement');
            $table->boolean('billing_terms_agreement');
            $table->dateTime('date_contract_ends')->nullable();
            $table->boolean('date_contract_terminate')->nullable();
            $table->enum('status', ['online', 'offline', 'on-premise'])->default('online');
            $table->enum('state', ['running', 'stopped', 'restarting'])->default('running');
            $table->string('status_code')->default(200);
            $table->timestamps(); // This creates both created_at and updated_at columns as timestamps.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installations');
    }
};

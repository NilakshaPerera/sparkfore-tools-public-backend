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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reseller_id')->nullable();
            $table->unsignedBigInteger('parent_customer_id')->nullable();
            $table->unsignedBigInteger('instance_id')->nullable();
            $table->string('name');
            $table->string('slugified_name');
            $table->string('organization_no');
            $table->enum('invoice_type', ['mail', 'email', 'e-invoice']);
            $table->string('invoice_email')->nullable();
            $table->string('invoice_address')->nullable();
            $table->string('invoice_reference')->nullable();
            $table->string('invoice_annotation')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps(); // This creates both created_at and updated_at columns as timestamps.

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Created By : Nilaksha
     * Created At : Unknown
     * Summary : Creates the structure for the reseller table
     *
     * Updated By : Nilaksha
     * Updated At 22/9/2023
     * Summary : Removed the customer_id column due to customer may or may not become a reseller relation. Based on client discussion on 21/9/2023
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('resellers', function (Blueprint $table) {
            $table->id();
            // $table->unsignedBigInteger('customer_id');
            $table->string('cost_per_user_discount');
            $table->string('product_discount');
            $table->timestamps(); // This creates both created_at and updated_at columns as timestamps.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resellers');
    }
};

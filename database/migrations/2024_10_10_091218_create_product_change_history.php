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
        Schema::create('product_change_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("product_id");
            $table->text("change");
            $table->string("branch");
            $table->bigInteger("created_by");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_change_histories');
    }
};

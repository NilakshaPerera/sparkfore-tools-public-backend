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
        Schema::create('hostings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('production_price_month', 10, 2)->default(0);
            $table->decimal('staging_price_month', 10, 2)->default(0);
            $table->decimal('yearly_price_increase', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->enum('availability', ['public','private'])->default('public'); // private means for selected customers
            $table->text('config');
            $table->unsignedBigInteger('hosting_type_id');
            $table->unsignedBigInteger('hosting_provider_id');
            $table->string('hosting_location')->nullable();
            $table->timestamps(); // This creates both created_at and updated_at columns as timestamps.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostings');
    }
};

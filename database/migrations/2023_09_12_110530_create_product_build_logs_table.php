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
        Schema::create('product_build_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('software_id');
            $table->integer('version');
            $table->timestamp('build_datetime');
            $table->boolean('queued');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed']);
            $table->timestamps(); // This creates both created_at and updated_at columns as timestamps.

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_build_logs');
    }
};

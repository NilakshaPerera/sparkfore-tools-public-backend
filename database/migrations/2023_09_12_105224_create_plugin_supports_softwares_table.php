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
        Schema::create('plugin_supports_softwares', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('software_id');
            $table->unsignedBigInteger('plugin_id');
            $table->timestamps(); // This creates both created_at and updated_at columns as timestamps.

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugin_supports_softwares');
    }
};

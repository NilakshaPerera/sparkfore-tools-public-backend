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
        Schema::create('hosting_types', function (Blueprint $table) {
            $table->id();
            $table->enum('key', ['cloud', 'on-prem']);
            $table->string('name');
            $table->timestamps(); // This creates both created_at and updated_at columns as timestamps automatically.

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosting_types');
    }
};

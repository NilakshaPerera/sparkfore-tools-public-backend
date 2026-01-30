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
        Schema::create('hosting_providers', function (Blueprint $table) {
            $table->id();
            $table->enum('key', ['digitalocean', 'cleura', 'on-prem','digitalocean2']);
            $table->string('name'); //'Digital Ocean', 'Cleura'
            $table->json('config')->nullable();
            $table->timestamps(); // This creates both created_at and updated_at columns as timestamps automatically.

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosting_providers');
    }
};

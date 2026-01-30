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
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('git_version_type_id');
            $table->string('name');
            $table->longText('description')->nullable();
            $table->string('git_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('access_token')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('type', ['free', 'buyable'])->default('free');
            $table->enum('availability', ['public','private'])->default('public'); // private means for selected customers
            $table->enum('accessibility_type', ['public','private'])->default('public'); // private means for selected customers
            $table->timestamps(); // This creates both created_at and updated_at columns as timestamps.
            $table->boolean('is_mirrored')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};

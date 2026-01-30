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
        Schema::create('plugin_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plugin_id');
            $table->string('version_name');
            $table->unsignedBigInteger('version_id');
            $table->string('requires') ->nullable();
            $table->string('component') ->nullable();
            $table->unsignedBigInteger('version_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugin_versions');
    }
};

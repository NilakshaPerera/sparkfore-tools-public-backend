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
        Schema::create('software_versions', function (Blueprint $table) {
            $table->unsignedBigInteger('software_id');
            $table->string('version_name');
            $table->string('version_id');
            $table->unsignedBigInteger('version_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software_version');
    }
};

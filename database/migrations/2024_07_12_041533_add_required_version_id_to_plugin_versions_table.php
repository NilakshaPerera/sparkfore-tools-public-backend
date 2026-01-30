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
        Schema::table('plugin_versions', function (Blueprint $table) {
            $table->text('required_version_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plugin_versions', function (Blueprint $table) {
            $table->dropIfExists('required_version_id');
        });
    }
};

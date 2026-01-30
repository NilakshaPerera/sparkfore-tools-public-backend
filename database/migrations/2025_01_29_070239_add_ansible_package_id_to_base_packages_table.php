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
        Schema::table('base_packages', function (Blueprint $table) {
            $table->unsignedBigInteger('ansible_package_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('base_packages', function (Blueprint $table) {
            $table->dropColumn('ansible_package_id');
        });
    }
};

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
        Schema::table('hostings', function (Blueprint $table) {
            $table->integer('disk_size')->default(0); // in GB
            $table->string('base_package_id')->nullable();
        });

        Schema::table('installations', function (Blueprint $table) {
            $table->integer('disk_size')->default(0); // in GB
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installations', function (Blueprint $table) {
            $table->dropColumn('disk_size');
        });

        Schema::table('hostings', function (Blueprint $table) {
            $table->dropColumn('disk_size');
            $table->dropColumn('base_package_id');
        });
    }
};

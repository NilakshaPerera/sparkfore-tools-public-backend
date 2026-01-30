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
        Schema::table('softwares', function (Blueprint $table) {
            $table->string('slug')->nullable();  // slug is added and based on the name of the friendly name of the softwaer
             $table->string('software_slug')->nullable(); // software slug is the pre-defined slug for a specific software
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('softwares', function (Blueprint $table) {
            $table->string('slug')->nullable();
             $table->string('software_slug')->nullable();
        });
    }
};

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
        Schema::create('hosting_on_prem_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hosting_id');
            $table->string('moodle_url');
            $table->string('moodle_cron_url');
            $table->boolean('reverse_proxy');
            $table->boolean('active');
            $table->timestamps(); // This creates both created_at and updated_at columns as timestamps.

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosting_on_prem_settings');
    }
};

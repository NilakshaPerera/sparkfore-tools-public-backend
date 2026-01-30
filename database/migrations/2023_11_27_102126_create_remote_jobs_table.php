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
        Schema::create('remote_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('remote_job_type_id');
            $table->unsignedInteger('reference_id');
            $table->text('branch')->nullable();
            $table->unsignedInteger('created_by');
            $table->text('response')->nullable();
            $table->text('callback_msg')->nullable();
            $table->text('callback_status')->nullable();
            $table->text('callback_log_uri')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remote_jobs');
    }
};

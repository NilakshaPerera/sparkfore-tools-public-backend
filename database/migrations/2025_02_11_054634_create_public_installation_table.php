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
        Schema::create('public_installations', function (Blueprint $table) {
            $table->id();
            $table->text("site_name");
            $table->text("password");
            $table->text("first_name");
            $table->text("last_name");
            $table->text("email");
            $table->text("phone");
            $table->boolean("terms_accepted");
            $table->bigInteger("remote_job_id")->nullable();
            $table->text("installation_status")->default("RECEIVED");
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_installations');
    }
};

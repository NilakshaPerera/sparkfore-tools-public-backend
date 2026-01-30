<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pipeline_maintainer_id');
            $table->string('pipeline_name');
            $table->string('git_url');
            $table->text('release_notes')->nullable();
            $table->string('pipeline_build_status')->nullable();
            $table->dateTime('last_build')->nullable();
            $table->text('development_scheduled_build')->default(config('sparkfore.package_build.dev_cron'));
            $table->text('staging_scheduled_build')->default(config('sparkfore.package_build.stg_cron'));
            $table->text('production_scheduled_build')->default(config('sparkfore.package_build.prod_cron'));
            $table->text('plugin_changes')->nullable();
            $table->enum('availability', ['public', 'private'])->default('private');
            $table->timestamps(); // This creates both created_at and updated_at columns as timestamps.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

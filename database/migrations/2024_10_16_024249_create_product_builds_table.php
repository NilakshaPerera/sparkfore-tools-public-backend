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
        Schema::create('product_builds', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("remote_job_id");
            $table->text("preparing_build_stage")->nullable();
            $table->text("building_application_stage")->nullable();
            $table->text("performing_tests_stage")->nullable();
            $table->text("analyzing_result_stage")->nullable();
            $table->text("publishing_application_stage")->nullable();
            $table->text("application_url")->nullable();
            $table->integer("restart_instance")->nullable();
            $table->text("restart_tag")->nullable();
            $table->json("changes")->nullable();
            $table->char("git_version", 200)->nullable();
            $table->text("release_note")->nullable();
            $table->text("tag")->nullable();
            $table->timestamp("built_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_builds');
    }
};

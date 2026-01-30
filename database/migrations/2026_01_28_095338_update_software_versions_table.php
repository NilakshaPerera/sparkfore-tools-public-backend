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
        // add software major_version, minor_version, patch_serions, prefix columns
        Schema::table('software_versions', function (Blueprint $table) {
            $table->string('major_version')->default('')->after('version');
            $table->string('minor_version')->default('')->after('major_version');
            $table->string('patch_version')->default('')->after('minor_version');
            $table->string('prefix')->default('')->after('patch_version');
            $table->string('branch_version')->default('')->after('prefix');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop software major_version, minor_version, patch_serions, prefix columns
        Schema::table('software_versions', function (Blueprint $table) {
            $table->dropColumn('major_version');
            $table->dropColumn('minor_version');
            $table->dropColumn('patch_version');
            $table->dropColumn('prefix');
            $table->dropColumn('branch_version');
        });
    }
};

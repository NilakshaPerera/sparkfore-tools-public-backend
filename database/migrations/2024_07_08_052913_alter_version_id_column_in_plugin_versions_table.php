<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE plugin_versions ALTER COLUMN version_id SET DATA TYPE TEXT"); // changing cuz some version id are longer than bigInt
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE plugin_versions ALTER COLUMN version_id SET DATA TYPE BIGINT");
    }
};

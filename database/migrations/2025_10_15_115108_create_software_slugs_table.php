<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('software_slugs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('value')->unique();
            $table->timestamps();
        });

        DB::table('software_slugs')->insert([
            ['name' => 'Moodle', 'value' => 'moodle' , 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Moodle Workplace', 'value' => 'moodle_workplace', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Nextcloud', 'value' => 'nextcloud', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software_slugs');
    }
};

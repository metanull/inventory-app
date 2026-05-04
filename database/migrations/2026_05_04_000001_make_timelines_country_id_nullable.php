<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Makes timelines.country_id nullable so that collection-bound timelines
     * (e.g. THG exhibition timelines) do not require a country reference.
     * Country-based timelines (mwnf3, SH) continue to have country_id set.
     */
    public function up(): void
    {
        Schema::table('timelines', function (Blueprint $table) {
            $table->string('country_id', 3)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timelines', function (Blueprint $table) {
            $table->string('country_id', 3)->nullable(false)->change();
        });
    }
};

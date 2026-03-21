<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drop tables for deprecated entities: Theme, ThemeTranslation,
     * Location, LocationTranslation, Province, ProvinceTranslation,
     * and their legacy junction tables.
     */
    public function up(): void
    {
        Schema::dropIfExists('theme_translations');
        Schema::dropIfExists('themes');
        Schema::dropIfExists('location_translations');
        Schema::dropIfExists('location_language');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('province_translations');
        Schema::dropIfExists('province_language');
        Schema::dropIfExists('provinces');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // These tables are permanently removed as part of the entity deprecation.
        // Re-creating them would require restoring the original migration files.
    }
};

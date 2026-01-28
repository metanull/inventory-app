<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add geocoordinates (latitude, longitude, map_zoom) and country_id to collections table.
     * Also expand the type enum to include new collection types for Explore.
     *
     * Following the Partner model pattern for geocoordinates:
     * - latitude: DECIMAL(10,8) - supports precision to 1.1mm
     * - longitude: DECIMAL(11,8) - supports precision to 1.1mm
     * - map_zoom: INTEGER - map zoom level
     */
    public function up(): void
    {
        // First, modify the type enum to include new values
        // Only needed for MariaDB/MySQL - SQLite doesn't enforce enum constraints
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE collections MODIFY COLUMN type ENUM('collection', 'exhibition', 'gallery', 'theme', 'exhibition trail', 'itinerary', 'location') NOT NULL DEFAULT 'collection'");
        }

        Schema::table('collections', function (Blueprint $table) {
            // GPS Location (following Partner model pattern)
            $table->decimal('latitude', 10, 8)->nullable()->after('backward_compatibility');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->integer('map_zoom')->nullable()->after('longitude');

            // Country reference (optional)
            $table->string('country_id', 3)->nullable()->after('map_zoom');

            // Foreign key constraint
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');

            // Index for country lookups
            $table->index(['country_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['country_id']);

            // Drop columns
            $table->dropColumn([
                'latitude',
                'longitude',
                'map_zoom',
                'country_id',
            ]);
        });

        // Revert the type enum to original values
        // Only needed for MariaDB/MySQL - SQLite doesn't enforce enum constraints
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE collections MODIFY COLUMN type ENUM('collection', 'exhibition', 'gallery') NOT NULL DEFAULT 'collection'");
        }
    }
};

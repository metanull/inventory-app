<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add geocoordinates (latitude, longitude, map_zoom) to items table.
     * This allows storing monument location data from Explore.
     *
     * Following the Partner model pattern for geocoordinates:
     * - latitude: DECIMAL(10,8) - supports precision to 1.1mm
     * - longitude: DECIMAL(11,8) - supports precision to 1.1mm
     * - map_zoom: INTEGER - map zoom level
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // GPS Location (following Partner model pattern)
            // Place after mwnf_reference which is the last non-timestamp column
            $table->decimal('latitude', 10, 8)->nullable()->after('mwnf_reference');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->integer('map_zoom')->nullable()->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude',
                'map_zoom',
            ]);
        });
    }
};

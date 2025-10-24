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
        Schema::table('partners', function (Blueprint $table) {
            // GPS Location
            $table->decimal('latitude', 10, 8)->nullable()->after('type');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->integer('map_zoom')->default(16)->after('longitude');

            // Relationships
            $table->uuid('project_id')->nullable()->default(null)->after('map_zoom');
            $table->uuid('monument_item_id')->nullable()->after('project_id');

            // Visibility
            $table->boolean('visible')->default(false)->after('monument_item_id');

            // Foreign keys
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
            $table->foreign('monument_item_id')->references('id')->on('items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['project_id']);
            $table->dropForeign(['monument_item_id']);

            // Drop columns
            $table->dropColumn([
                'latitude',
                'longitude',
                'map_zoom',
                'project_id',
                'monument_item_id',
                'visible',
            ]);
        });
    }
};

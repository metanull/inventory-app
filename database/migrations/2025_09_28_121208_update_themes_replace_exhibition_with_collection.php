<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            // Add collection_id as optional foreign key only if it doesn't exist
            if (! Schema::hasColumn('themes', 'collection_id')) {
                $table->uuid('collection_id')->nullable()->after('exhibition_id');
                $table->foreign('collection_id')->references('id')->on('collections')->onDelete('set null');
            }
        });

        // Drop all indexes that reference exhibition_id before dropping the column
        DB::statement('DROP INDEX IF EXISTS themes_internal_name_exhibition_id_unique');
        DB::statement('DROP INDEX IF EXISTS themes_exhibition_id_internal_name_unique');
        DB::statement('DROP INDEX IF EXISTS themes_exhibition_id_parent_id_index');

        Schema::table('themes', function (Blueprint $table) {
            // Drop exhibition_id column
            if (Schema::hasColumn('themes', 'exhibition_id')) {
                $table->dropForeign(['exhibition_id']);
                $table->dropColumn('exhibition_id');
            }

            // Create new unique index without exhibition_id
            $table->unique(['internal_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            // Drop the unique index
            $table->dropUnique(['internal_name']);

            // Add back exhibition_id
            $table->uuid('exhibition_id')->nullable()->after('collection_id');
            $table->foreign('exhibition_id')->references('id')->on('exhibitions')->onDelete('set null');

            // Drop collection_id
            $table->dropForeign(['collection_id']);
            $table->dropColumn('collection_id');

            // Recreate the original unique index
            $table->unique(['internal_name', 'exhibition_id']);
        });
    }
};

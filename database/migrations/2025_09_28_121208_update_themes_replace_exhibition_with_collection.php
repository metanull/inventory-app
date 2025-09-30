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
            $table->uuid('collection_id')->nullable()->after('exhibition_id');
            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('set null');
        });

        Schema::table('themes', function (Blueprint $table) {
            $table->dropUnique(['exhibition_id', 'internal_name']);

            // SQLite requires explicit index drop before column drop
            if (DB::connection()->getDriverName() === 'sqlite') {
                $table->dropIndex(['exhibition_id', 'parent_id']);
            }
        });

        Schema::table('themes', function (Blueprint $table) {
            $table->dropForeign(['exhibition_id']);
            $table->dropColumn('exhibition_id');
            $table->unique(['internal_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropUnique(['internal_name']);
            $table->uuid('exhibition_id')->after('collection_id');
            $table->foreign('exhibition_id')->references('id')->on('exhibitions')->onDelete('cascade');
        });

        Schema::table('themes', function (Blueprint $table) {
            $table->unique(['exhibition_id', 'internal_name']);

            // Recreate index for SQLite (MariaDB handles this automatically via foreign keys)
            if (DB::connection()->getDriverName() === 'sqlite') {
                $table->index(['exhibition_id', 'parent_id']);
            }
        });

        Schema::table('themes', function (Blueprint $table) {
            $table->dropForeign(['collection_id']);
            $table->dropColumn('collection_id');
        });
    }
};

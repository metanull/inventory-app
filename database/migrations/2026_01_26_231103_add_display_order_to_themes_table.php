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
            $table->integer('display_order')->default(0)->after('parent_id');
            $table->index(['collection_id', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            // MariaDB may require an index for the existing foreign key on `collection_id`.
            // Only drop the composite index for SQLite where it was explicitly created.
            if (DB::connection()->getDriverName() === 'sqlite') {
                $table->dropIndex(['collection_id', 'display_order']);
            }

            $table->dropColumn('display_order');
        });
    }
};

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
        Schema::table('item_item_links', function (Blueprint $table) {
            $table->string('backward_compatibility')->nullable()->unique()->after('context_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_item_links', function (Blueprint $table) {
            // drop the unique index first to avoid DB errors during rollback
            $table->dropUnique(['backward_compatibility']);
            $table->dropColumn('backward_compatibility');
        });
    }
};

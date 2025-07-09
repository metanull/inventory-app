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
        Schema::table('items', function (Blueprint $table) {
            $table->uuid('collection_id')->nullable()->after('project_id');

            // Foreign key constraint
            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('set null');

            // Index for performance
            $table->index(['collection_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['collection_id']);
            $table->dropIndex(['collection_id']);
            $table->dropColumn('collection_id');
        });
    }
};

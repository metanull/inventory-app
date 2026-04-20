<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add single-column and composite indexes to the items table for the list-page queries
     * that filter by type, sort by internal_name / created_at / updated_at, and support
     * hierarchy browsing. Also adds the reverse-lookup index on the item_tag pivot.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->index(['internal_name'], 'items_internal_name_index');
            $table->index(['type'], 'items_type_index');
            $table->index(['created_at'], 'items_created_at_index');
            $table->index(['updated_at'], 'items_updated_at_index');
            $table->index(['parent_id', 'type', 'created_at'], 'items_parent_id_type_created_at_index');
        });

        Schema::table('item_tag', function (Blueprint $table) {
            $table->index(['tag_id', 'item_id'], 'item_tag_tag_id_item_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('items_internal_name_index');
            $table->dropIndex('items_type_index');
            $table->dropIndex('items_created_at_index');
            $table->dropIndex('items_updated_at_index');
            $table->dropIndex('items_parent_id_type_created_at_index');
        });

        Schema::table('item_tag', function (Blueprint $table) {
            $table->dropIndex('item_tag_tag_id_item_id_index');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->index('internal_name');
            $table->index('type');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index(['parent_id', 'type', 'created_at']);
        });

        Schema::table('item_tag', function (Blueprint $table) {
            $table->index(['tag_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex(['internal_name']);
            $table->dropIndex(['type']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);
            $table->dropIndex(['parent_id', 'type', 'created_at']);
        });

        Schema::table('item_tag', function (Blueprint $table) {
            $table->dropIndex(['tag_id', 'item_id']);
        });
    }
};

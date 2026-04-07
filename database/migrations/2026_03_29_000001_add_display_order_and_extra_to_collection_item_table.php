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
        Schema::table('collection_item', function (Blueprint $table) {
            $table->integer('display_order')->nullable()->after('item_id');
            $table->json('extra')->nullable()->after('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collection_item', function (Blueprint $table) {
            $table->dropColumn(['display_order', 'extra']);
        });
    }
};

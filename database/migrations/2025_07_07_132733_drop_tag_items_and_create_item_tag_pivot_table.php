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
        // Drop the old tag_items table (if it exists)
        Schema::dropIfExists('tag_items');

        // Create the proper Laravel pivot table with correct naming convention
        Schema::create('item_tag', function (Blueprint $table) {
            $table->uuid('item_id');
            $table->uuid('tag_id');
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');

            // Primary key for the pivot table
            $table->primary(['item_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new pivot table
        Schema::dropIfExists('item_tag');

        // Recreate the old tag_items table
        Schema::create('tag_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tag_id');
            $table->uuid('item_id');
            $table->timestamps();

            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');

            $table->unique(['tag_id', 'item_id']);
        });
    }
};

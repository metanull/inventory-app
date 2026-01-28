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
        Schema::create('item_item_link_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_item_link_id');
            $table->string('language_id', 3);
            $table->text('description')->nullable();
            $table->text('reciprocal_description')->nullable();
            $table->string('backward_compatibility')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('item_item_link_id')
                ->references('id')
                ->on('item_item_links')
                ->onDelete('cascade');
            $table->foreign('language_id')
                ->references('id')
                ->on('languages')
                ->onDelete('cascade');

            // Unique constraint: one translation per link per language
            $table->unique(['item_item_link_id', 'language_id'], 'unique_link_language');

            // Indexes for common queries
            $table->index(['item_item_link_id']);
            $table->index(['language_id']);
            $table->index(['backward_compatibility']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_item_link_translations');
    }
};

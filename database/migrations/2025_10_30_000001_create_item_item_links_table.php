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
        Schema::create('item_item_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('source_id');
            $table->uuid('target_id');
            $table->uuid('context_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('source_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('target_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');

            // Unique constraint to prevent duplicate links in the same context
            $table->unique(['source_id', 'target_id', 'context_id'], 'unique_source_target_context');

            // Indexes for common queries
            $table->index(['source_id', 'context_id']);
            $table->index(['target_id', 'context_id']);
            $table->index(['context_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_item_links');
    }
};

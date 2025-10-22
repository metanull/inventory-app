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
        Schema::create('item_translation_spelling', function (Blueprint $table) {
            $table->uuid('item_translation_id');
            $table->uuid('spelling_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('item_translation_id')->references('id')->on('item_translations')->onDelete('cascade');
            $table->foreign('spelling_id')->references('id')->on('glossary_spellings')->onDelete('cascade');

            // Unique constraint: prevent duplicate links
            $table->unique(['item_translation_id', 'spelling_id']);

            // Index for reverse lookups
            $table->index(['spelling_id', 'item_translation_id']);

            // Primary key
            $table->primary(['item_translation_id', 'spelling_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_translation_spelling');
    }
};

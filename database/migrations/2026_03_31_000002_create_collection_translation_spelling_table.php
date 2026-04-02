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
        Schema::create('collection_translation_spelling', function (Blueprint $table) {
            $table->uuid('collection_translation_id');
            $table->uuid('spelling_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('collection_translation_id', 'coll_trans_spelling_coll_trans_fk')->references('id')->on('collection_translations')->onDelete('cascade');
            $table->foreign('spelling_id', 'coll_trans_spelling_spelling_fk')->references('id')->on('glossary_spellings')->onDelete('cascade');

            // Unique constraint: prevent duplicate links
            $table->unique(['collection_translation_id', 'spelling_id'], 'coll_trans_spelling_unique');

            // Index for reverse lookups
            $table->index(['spelling_id', 'collection_translation_id'], 'spelling_coll_trans_index');

            // Primary key
            $table->primary(['collection_translation_id', 'spelling_id'], 'coll_trans_spelling_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_translation_spelling');
    }
};

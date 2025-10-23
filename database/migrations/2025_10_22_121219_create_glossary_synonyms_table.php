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
        Schema::create('glossary_synonyms', function (Blueprint $table) {
            $table->uuid('glossary_id');
            $table->uuid('synonym_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('glossary_id')->references('id')->on('glossaries')->onDelete('cascade');
            $table->foreign('synonym_id')->references('id')->on('glossaries')->onDelete('cascade');

            // Unique constraint: prevent duplicate synonym relationships
            // Also ensure glossary_id < synonym_id to avoid bidirectional duplicates
            $table->unique(['glossary_id', 'synonym_id']);

            // Index for reverse lookups
            $table->index(['synonym_id', 'glossary_id']);

            // Primary key
            $table->primary(['glossary_id', 'synonym_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('glossary_synonyms');
    }
};

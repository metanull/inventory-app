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
        Schema::create('glossary_spellings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('glossary_id');
            $table->string('language_id', 3);
            $table->string('spelling');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('glossary_id')->references('id')->on('glossaries')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');

            // Unique constraint: avoid duplicate spellings for the same glossary and language
            $table->unique(['glossary_id', 'language_id', 'spelling']);

            // Indexes for performance (searching by spelling and language)
            $table->index(['language_id', 'spelling']);
            $table->index('glossary_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('glossary_spellings');
    }
};

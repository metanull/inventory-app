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
        Schema::create('timeline_event_translation_spelling', function (Blueprint $table) {
            $table->uuid('timeline_event_translation_id');
            $table->uuid('spelling_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('timeline_event_translation_id', 'tet_spelling_tet_fk')->references('id')->on('timeline_event_translations')->onDelete('cascade');
            $table->foreign('spelling_id', 'tet_spelling_spelling_fk')->references('id')->on('glossary_spellings')->onDelete('cascade');

            // Unique constraint: prevent duplicate links
            $table->unique(['timeline_event_translation_id', 'spelling_id'], 'tet_spelling_unique');

            // Index for reverse lookups
            $table->index(['spelling_id', 'timeline_event_translation_id'], 'spelling_tet_index');

            // Primary key
            $table->primary(['timeline_event_translation_id', 'spelling_id'], 'tet_spelling_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_event_translation_spelling');
    }
};

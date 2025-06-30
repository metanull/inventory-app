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
        Schema::create('internationalizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contextualization_id');
            $table->string('language_id', 3);

            // Text content fields
            $table->string('name');
            $table->string('alternate_name')->nullable()->default(null);
            $table->text('description');
            $table->string('type')->nullable()->default(null);
            $table->text('holder')->nullable()->default(null);
            $table->text('owner')->nullable()->default(null);
            $table->text('initial_owner')->nullable()->default(null);
            $table->text('dates')->nullable()->default(null);
            $table->text('location')->nullable()->default(null);
            $table->text('dimensions')->nullable()->default(null);
            $table->text('place_of_production')->nullable()->default(null);
            $table->text('method_for_datation')->nullable()->default(null);
            $table->text('method_for_provenance')->nullable()->default(null);
            $table->text('obtention')->nullable()->default(null);
            $table->text('bibliography')->nullable()->default(null);
            $table->json('extra')->nullable()->default(null);

            // Author references
            $table->uuid('author_id')->nullable()->default(null);
            $table->uuid('text_copy_editor_id')->nullable()->default(null);
            $table->uuid('translator_id')->nullable()->default(null);
            $table->uuid('translation_copy_editor_id')->nullable()->default(null);

            $table->string('backward_compatibility')->nullable()->default(null);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('contextualization_id')->references('id')->on('contextualizations')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('text_copy_editor_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('translator_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('translation_copy_editor_id')->references('id')->on('authors')->onDelete('set null');

            // Indexes for searching
            $table->index('name');
            $table->index('type');
            $table->index(['contextualization_id', 'language_id']);

            // Unique constraint to prevent duplicate internationalization for same contextualization and language
            $table->unique(['contextualization_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internationalizations');
    }
};

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
        Schema::create('item_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->string('language_id', 3);
            $table->uuid('context_id');

            // Translation fields
            $table->string('name');
            $table->string('alternate_name')->nullable();
            $table->text('description');
            $table->string('type')->nullable();
            $table->text('holder')->nullable();
            $table->text('owner')->nullable();
            $table->text('initial_owner')->nullable();
            $table->text('dates')->nullable();
            $table->text('location')->nullable();
            $table->text('dimensions')->nullable();
            $table->text('place_of_production')->nullable();
            $table->text('method_for_datation')->nullable();
            $table->text('method_for_provenance')->nullable();
            $table->text('obtention')->nullable();
            $table->text('bibliography')->nullable();

            // Author relationships
            $table->uuid('author_id')->nullable();
            $table->uuid('text_copy_editor_id')->nullable();
            $table->uuid('translator_id')->nullable();
            $table->uuid('translation_copy_editor_id')->nullable();

            // Standard fields
            $table->string('backward_compatibility')->nullable()->default(null);
            $table->json('extra')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('text_copy_editor_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('translator_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('translation_copy_editor_id')->references('id')->on('authors')->onDelete('set null');

            // Unique constraint
            $table->unique(['item_id', 'language_id', 'context_id']);

            // Indexes for performance
            $table->index(['item_id', 'language_id']);
            $table->index(['item_id', 'context_id']);
            $table->index(['language_id', 'context_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_translations');
    }
};

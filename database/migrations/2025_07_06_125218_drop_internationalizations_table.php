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
        Schema::dropIfExists('internationalizations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-create the internationalizations table if rolled back
        Schema::create('internationalizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contextualization_id');
            $table->string('language_id', 3);
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
            $table->json('extra')->nullable();
            $table->uuid('author_id')->nullable();
            $table->uuid('text_copy_editor_id')->nullable();
            $table->uuid('translator_id')->nullable();
            $table->uuid('translation_copy_editor_id')->nullable();
            $table->string('backward_compatibility')->nullable();
            $table->timestamps();

            $table->foreign('contextualization_id')->references('id')->on('contextualizations')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('text_copy_editor_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('translator_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('translation_copy_editor_id')->references('id')->on('authors')->onDelete('set null');

            $table->unique(['contextualization_id', 'language_id']);
        });
    }
};

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
        Schema::create('picture_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('picture_id');
            $table->string('language_id', 3);
            $table->uuid('context_id');
            $table->text('description');
            $table->string('caption');
            $table->uuid('author_id')->nullable();
            $table->uuid('text_copy_editor_id')->nullable();
            $table->uuid('translator_id')->nullable();
            $table->uuid('translation_copy_editor_id')->nullable();
            $table->string('backward_compatibility')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();

            $table->foreign('picture_id')->references('id')->on('pictures')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('text_copy_editor_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('translator_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('translation_copy_editor_id')->references('id')->on('authors')->onDelete('set null');

            $table->unique(['picture_id', 'language_id', 'context_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('picture_translations');
    }
};

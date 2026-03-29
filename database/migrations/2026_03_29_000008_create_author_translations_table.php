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
        Schema::create('author_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('author_id');
            $table->string('language_id', 3);
            $table->uuid('context_id');

            // Translation fields
            $table->text('curriculum')->nullable();

            // Metadata
            $table->string('backward_compatibility')->nullable()->default(null);
            $table->json('extra')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');

            // Unique constraint
            $table->unique(['author_id', 'language_id', 'context_id'], 'author_translations_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('author_translations');
    }
};

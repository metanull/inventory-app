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
        Schema::create('contributor_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contributor_id')->constrained()->cascadeOnDelete();
            $table->string('language_id', 3);
            $table->foreign('language_id')->references('id')->on('languages')->cascadeOnDelete();
            $table->foreignUuid('context_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('link')->nullable();
            $table->string('alt_text')->nullable();
            $table->json('extra')->nullable();
            $table->string('backward_compatibility')->nullable();
            $table->timestamps();

            $table->unique(['contributor_id', 'language_id', 'context_id'], 'contributor_translations_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributor_translations');
    }
};

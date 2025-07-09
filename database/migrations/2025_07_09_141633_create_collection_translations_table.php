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
        Schema::create('collection_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('collection_id');
            $table->string('language_id', 3);
            $table->uuid('context_id');

            // Translation fields
            $table->string('title');
            $table->text('description');
            $table->string('url')->nullable();

            // Standard fields
            $table->string('backward_compatibility')->nullable()->default(null);
            $table->json('extra')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');

            // Unique constraint
            $table->unique(['collection_id', 'language_id', 'context_id']);

            // Indexes for performance
            $table->index(['collection_id', 'language_id']);
            $table->index(['collection_id', 'context_id']);
            $table->index(['language_id', 'context_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_translations');
    }
};

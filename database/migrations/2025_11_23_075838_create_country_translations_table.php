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
        Schema::create('country_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('country_id', 3);
            $table->string('language_id', 3);

            $table->string('name');

            // Metadata
            $table->string('backward_compatibility')->nullable();
            $table->json('extra')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');

            // Unique constraint
            $table->unique(['country_id', 'language_id'], 'country_translations_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_translations');
    }
};

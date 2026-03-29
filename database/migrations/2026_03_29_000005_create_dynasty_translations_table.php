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
        Schema::create('dynasty_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('dynasty_id');
            $table->string('language_id', 3);

            // Translation fields
            $table->string('name')->nullable();
            $table->string('also_known_as')->nullable();
            $table->text('area')->nullable();
            $table->text('history')->nullable();
            $table->string('date_description_ah')->nullable();
            $table->string('date_description_ad')->nullable();

            // Metadata
            $table->string('backward_compatibility')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('dynasty_id')->references('id')->on('dynasties')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');

            // Unique constraint
            $table->unique(['dynasty_id', 'language_id'], 'dynasty_translations_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynasty_translations');
    }
};

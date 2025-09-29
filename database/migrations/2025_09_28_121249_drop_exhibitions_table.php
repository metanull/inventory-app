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
        // Drop exhibition_translations table first
        Schema::dropIfExists('exhibition_translations');

        // Drop exhibitions table
        Schema::dropIfExists('exhibitions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate exhibitions table
        Schema::create('exhibitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('internal_name');
            $table->string('backward_compatibility')->nullable();
            $table->timestamps();

            $table->unique(['internal_name']);
        });

        // Recreate exhibition_translations table
        Schema::create('exhibition_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exhibition_id');
            $table->string('language_id', 3);
            $table->uuid('context_id');
            $table->string('name');
            $table->text('description');
            $table->timestamps();

            $table->foreign('exhibition_id')->references('id')->on('exhibitions')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');

            $table->unique(['exhibition_id', 'language_id', 'context_id']);
        });
    }
};

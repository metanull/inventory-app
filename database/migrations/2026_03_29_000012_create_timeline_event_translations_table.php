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
        Schema::create('timeline_event_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('timeline_event_id');
            $table->string('language_id', 3);

            // Translation fields
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('date_from_description')->nullable();
            $table->string('date_to_description')->nullable();
            $table->string('date_from_ah_description')->nullable();

            // Metadata
            $table->string('backward_compatibility')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('timeline_event_id')->references('id')->on('timeline_events')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');

            // Unique constraint
            $table->unique(['timeline_event_id', 'language_id'], 'timeline_event_translations_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_event_translations');
    }
};

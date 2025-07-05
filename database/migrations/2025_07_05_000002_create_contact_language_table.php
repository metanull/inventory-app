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
        Schema::create('contact_language', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contact_id');
            $table->string('language_id', 3);
            $table->string('label');
            $table->string('backward_compatibility')->nullable()->default(null);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');

            // Unique constraint to prevent duplicate language entries for a contact
            $table->unique(['contact_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_language');
    }
};

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
        Schema::create('address_language', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('address_id');
            $table->string('language_id', 3);
            $table->text('address');
            $table->text('description')->nullable()->default(null);
            $table->string('backward_compatibility')->nullable()->default(null);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');

            // Unique constraint to prevent duplicate language entries for an address
            $table->unique(['address_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address_language');
    }
};

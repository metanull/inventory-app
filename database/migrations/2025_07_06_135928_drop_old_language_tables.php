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
        Schema::dropIfExists('contact_language');
        Schema::dropIfExists('location_language');
        Schema::dropIfExists('province_language');
        Schema::dropIfExists('address_language');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the contact_language table
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

        // Recreate the location_language table
        Schema::create('location_language', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('location_id');
            $table->string('language_id', 3);
            $table->string('name');
            $table->string('backward_compatibility')->nullable()->default(null);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');

            // Unique constraint to prevent duplicate language entries for a location
            $table->unique(['location_id', 'language_id']);
        });

        // Recreate the province_language table
        Schema::create('province_language', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('province_id');
            $table->string('language_id', 3);
            $table->string('name');
            $table->string('backward_compatibility')->nullable()->default(null);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');

            // Unique constraint to prevent duplicate language entries for a province
            $table->unique(['province_id', 'language_id']);
        });

        // Recreate the address_language table
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
};

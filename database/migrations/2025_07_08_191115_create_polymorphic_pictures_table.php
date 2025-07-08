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
        // Drop existing pictures table if it exists
        Schema::dropIfExists('pictures');

        // Create new polymorphic pictures table
        Schema::create('pictures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('internal_name');
            $table->string('backward_compatibility')->nullable();
            $table->string('copyright_text')->nullable();
            $table->string('copyright_url')->nullable();
            $table->string('path');
            $table->string('upload_name');
            $table->string('upload_extension');
            $table->string('upload_mime_type');
            $table->bigInteger('upload_size');

            // Polymorphic relationship columns (creates pictureable_type and pictureable_id)
            $table->uuidMorphs('pictureable');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the polymorphic pictures table
        Schema::dropIfExists('pictures');

        // Recreate the previous pictures table structure (before polymorphic)
        Schema::create('pictures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('internal_name');
            $table->string('backward_compatibility')->nullable();
            $table->string('copyright_text')->nullable();
            $table->string('copyright_url')->nullable();
            $table->string('path')->nullable();
            $table->string('upload_name')->nullable();
            $table->string('upload_extension')->nullable();
            $table->string('upload_mime_type')->nullable();
            $table->unsignedBigInteger('upload_size')->nullable();
            $table->timestamps();
        });
    }
};

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
        // Remove legacy tables in order (respecting foreign key constraints)

        // 1. Remove polymorphic pivot table first
        Schema::dropIfExists('pictureables');

        // 2. Remove translation tables (depend on main tables)
        Schema::dropIfExists('detail_translations');
        Schema::dropIfExists('picture_translations');

        // 3. Remove main legacy tables
        Schema::dropIfExists('details');
        Schema::dropIfExists('pictures');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate tables in reverse order (this is a complex rollback)
        // WARNING: This rollback is complex and may require manual intervention
        // Consider creating a backup before running the up() migration

        // Main tables
        Schema::create('pictures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('internal_name');
            $table->string('backward_compatibility')->nullable();
            $table->text('copyright_text')->nullable();
            $table->string('copyright_url')->nullable();
            $table->string('path')->nullable();
            $table->string('upload_name')->nullable();
            $table->string('upload_extension')->nullable();
            $table->string('upload_mime_type')->nullable();
            $table->integer('upload_size')->nullable();
            $table->string('pictureable_type')->nullable();
            $table->uuid('pictureable_id')->nullable();
            $table->timestamps();

            $table->index(['pictureable_type', 'pictureable_id']);
        });

        Schema::create('details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->string('internal_name');
            $table->string('backward_compatibility')->nullable();
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });

        // Translation tables
        Schema::create('picture_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('picture_id');
            $table->string('language_id', 3);
            $table->uuid('context_id');
            $table->text('description')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();

            $table->foreign('picture_id')->references('id')->on('pictures')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');
        });

        Schema::create('detail_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('detail_id');
            $table->string('language_id', 3);
            $table->uuid('context_id');
            $table->text('description')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();

            $table->foreign('detail_id')->references('id')->on('details')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');
        });

        // Polymorphic pivot table
        Schema::create('pictureables', function (Blueprint $table) {
            $table->uuid('picture_id');
            $table->uuid('pictureable_id');
            $table->string('pictureable_type');
            $table->integer('order')->default(1);
            $table->timestamps();

            $table->primary(['picture_id', 'pictureable_id', 'pictureable_type'], 'pictureables_primary');
            $table->foreign('picture_id')->references('id')->on('pictures')->onDelete('cascade');
        });
    }
};

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
        // Remove legacy detail tables (pictures and pictureables handled by separate migrations)

        // 1. Remove translation tables (depend on main tables)
        Schema::dropIfExists('detail_translations');
        Schema::dropIfExists('picture_translations');

        // 2. Remove details table
        Schema::dropIfExists('details');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate legacy detail tables (pictures and pictureables handled by separate migrations)

        // Note: pictures table handled by separate migration (2025_07_08_000001_drop_pictures_table)

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

        // Note: pictureables table handled by separate migration (2025_09_28_113107_drop_pictureables_table)
    }
};

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
        // Drop galleryables pivot table first due to foreign key constraints
        Schema::dropIfExists('galleryables');

        // Drop gallery_partner pivot table due to foreign key constraints
        Schema::dropIfExists('gallery_partner');

        // Drop gallery_translations table
        Schema::dropIfExists('gallery_translations');

        // Drop galleries table
        Schema::dropIfExists('galleries');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate galleries table
        Schema::create('galleries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('internal_name');
            $table->string('backward_compatibility')->nullable();
            $table->timestamps();

            $table->unique(['internal_name']);
        });

        // Recreate gallery_translations table
        Schema::create('gallery_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('gallery_id');
            $table->string('language_id', 3);
            $table->uuid('context_id');
            $table->string('name');
            $table->text('description');
            $table->timestamps();

            $table->foreign('gallery_id')->references('id')->on('galleries')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');

            $table->unique(['gallery_id', 'language_id', 'context_id']);
        });

        // Recreate gallery_partner pivot table
        Schema::create('gallery_partner', function (Blueprint $table) {
            $table->uuid('gallery_id');
            $table->uuid('partner_id');
            $table->integer('order')->default(0);
            $table->string('backward_compatibility')->nullable();
            $table->timestamps();

            $table->foreign('gallery_id')->references('id')->on('galleries')->onDelete('cascade');
            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('cascade');

            $table->unique(['gallery_id', 'partner_id']);
            $table->index(['partner_id']);
        });

        // Recreate galleryables pivot table
        Schema::create('galleryables', function (Blueprint $table) {
            $table->uuid('gallery_id');
            $table->uuidMorphs('galleryable');
            $table->integer('order')->default(0);
            $table->string('backward_compatibility')->nullable();
            $table->timestamps();

            $table->foreign('gallery_id')->references('id')->on('galleries')->onDelete('cascade');
            $table->unique(['gallery_id', 'galleryable_id', 'galleryable_type'], 'galleryables_unique');
            $table->index(['galleryable_id', 'galleryable_type']);
        });
    }
};

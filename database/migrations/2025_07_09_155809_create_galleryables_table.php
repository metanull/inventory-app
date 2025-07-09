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
        Schema::create('galleryables', function (Blueprint $table) {
            $table->id();
            $table->uuid('gallery_id');
            $table->uuidMorphs('galleryable'); // Creates galleryable_id and galleryable_type
            $table->integer('order')->default(0); // For ordering items in gallery

            // Standard fields
            $table->string('backward_compatibility')->nullable()->default(null);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('gallery_id')->references('id')->on('galleries')->onDelete('cascade');

            // Unique constraint to prevent duplicate entries
            $table->unique(['gallery_id', 'galleryable_id', 'galleryable_type'], 'gallery_galleryable_unique');

            // Indexes for performance
            $table->index(['gallery_id']);
            $table->index(['galleryable_id', 'galleryable_type']);
            $table->index(['order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galleryables');
    }
};

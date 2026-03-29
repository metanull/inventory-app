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
        Schema::create('collection_image_tag', function (Blueprint $table) {
            $table->uuid('collection_image_id');
            $table->uuid('tag_id');
            $table->timestamps();

            $table->foreign('collection_image_id')->references('id')->on('collection_images')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            $table->primary(['collection_image_id', 'tag_id']);
        });

        Schema::create('item_image_tag', function (Blueprint $table) {
            $table->uuid('item_image_id');
            $table->uuid('tag_id');
            $table->timestamps();

            $table->foreign('item_image_id')->references('id')->on('item_images')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            $table->primary(['item_image_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_image_tag');
        Schema::dropIfExists('collection_image_tag');
    }
};

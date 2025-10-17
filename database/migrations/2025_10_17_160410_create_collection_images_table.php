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
        Schema::create('collection_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('collection_id');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->bigInteger('size');
            $table->string('alt_text')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('cascade');
            $table->index(['collection_id', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_images');
    }
};

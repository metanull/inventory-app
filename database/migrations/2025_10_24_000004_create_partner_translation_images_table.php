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
        Schema::create('partner_translation_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('partner_translation_id');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->bigInteger('size');
            $table->text('alt_text')->nullable();
            $table->integer('display_order');
            $table->timestamps();

            $table->foreign('partner_translation_id')->references('id')->on('partner_translations')->onDelete('cascade');
            $table->index(['partner_translation_id', 'display_order'], 'partner_translation_images_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_translation_images');
    }
};

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
        Schema::create('timeline_event_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('timeline_event_id');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->bigInteger('size');
            $table->string('alt_text')->nullable();
            $table->integer('display_order');
            $table->timestamps();

            $table->foreign('timeline_event_id')->references('id')->on('timeline_events')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_event_images');
    }
};

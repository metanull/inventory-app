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
        Schema::create('collection_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('collection_id');
            $table->string('language_id', 3)->nullable();
            $table->enum('type', ['audio', 'video']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url', 512);
            $table->integer('display_order')->default(0);
            $table->json('extra')->nullable();
            $table->string('backward_compatibility')->nullable();
            $table->timestamps();

            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('set null');
            $table->index(['collection_id', 'type', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_media');
    }
};

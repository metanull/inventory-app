<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('theme_id');
            $table->string('language_id', 3);
            $table->uuid('context_id');
            $table->string('title');
            $table->text('description');
            $table->text('introduction');
            $table->string('backward_compatibility')->nullable()->default(null);
            $table->json('extra')->nullable();
            $table->timestamps();

            $table->foreign('theme_id')->references('id')->on('themes')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');

            $table->unique(['theme_id', 'language_id', 'context_id']);
            $table->index(['theme_id', 'language_id']);
            $table->index(['theme_id', 'context_id']);
            $table->index(['language_id', 'context_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_translations');
    }
};

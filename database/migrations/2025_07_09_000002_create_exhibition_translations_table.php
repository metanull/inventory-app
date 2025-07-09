<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exhibition_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exhibition_id');
            $table->string('language_id', 3);
            $table->uuid('context_id');
            $table->string('title');
            $table->text('description');
            $table->string('url')->nullable();
            $table->string('backward_compatibility')->nullable()->default(null);
            $table->json('extra')->nullable();
            $table->timestamps();

            $table->foreign('exhibition_id')->references('id')->on('exhibitions')->onDelete('cascade');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');

            $table->unique(['exhibition_id', 'language_id', 'context_id']);
            $table->index(['exhibition_id', 'language_id']);
            $table->index(['exhibition_id', 'context_id']);
            $table->index(['language_id', 'context_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exhibition_translations');
    }
};

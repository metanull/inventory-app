<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exhibition_id');
            $table->uuid('parent_id')->nullable();
            $table->string('internal_name');
            $table->string('backward_compatibility')->nullable()->default(null);
            $table->timestamps();

            $table->foreign('exhibition_id')->references('id')->on('exhibitions')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('themes')->onDelete('cascade');
            $table->unique(['exhibition_id', 'internal_name']);
            $table->index(['exhibition_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};

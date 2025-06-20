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
        Schema::create('image_uploads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('path')->nullable(true);
            $table->string('name')->nullable(true);
            $table->string('extension')->nullable(true);
            $table->string('mime_type')->nullable(true);
            $table->unsignedBigInteger('size')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_uploads');
    }
};

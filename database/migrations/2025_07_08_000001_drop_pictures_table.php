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
        Schema::dropIfExists('pictures');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only recreate the table if it doesn't exist
        if (! Schema::hasTable('pictures')) {
            Schema::create('pictures', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('internal_name');
                $table->string('backward_compatibility')->nullable();
                $table->string('copyright_text')->nullable();
                $table->string('copyright_url')->nullable();
                $table->string('path')->nullable();
                $table->string('upload_name')->nullable();
                $table->string('upload_extension')->nullable();
                $table->string('upload_mime_type')->nullable();
                $table->unsignedBigInteger('upload_size')->nullable();
                $table->timestamps();
            });
        }
    }
};

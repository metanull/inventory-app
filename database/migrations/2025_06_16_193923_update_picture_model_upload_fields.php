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
        Schema::table('pictures', function (Blueprint $table) {
            $table->string('upload_name')->nullable(true);
            $table->string('upload_extension')->nullable(true);
            $table->string('upload_mime_type')->nullable(true);
            $table->unsignedBigInteger('upload_size')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pictures', function (Blueprint $table) {
            $table->dropColumn(['upload_name', 'upload_extension', 'upload_mime_type', 'upload_size']);
        });
    }
};

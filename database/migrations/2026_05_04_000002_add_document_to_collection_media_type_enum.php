<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds 'document' to the collection_media.type enum so that related
     * PDF documents and other file references can be stored alongside
     * audio and video URLs.
     */
    public function up(): void
    {
        // SQLite does not support ALTER COLUMN for enum types.
        // Use a DB-agnostic approach via modifyColumn.
        Schema::table('collection_media', function (Blueprint $table) {
            $table->enum('type', ['audio', 'video', 'document'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove rows using the new type before reverting
        DB::table('collection_media')->where('type', 'document')->delete();

        Schema::table('collection_media', function (Blueprint $table) {
            $table->enum('type', ['audio', 'video'])->change();
        });
    }
};

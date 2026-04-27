<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('available_images', function (Blueprint $table) {
            $table->string('original_name')->nullable()->after('path');
            $table->string('mime_type')->nullable()->after('original_name');
            $table->unsignedBigInteger('size')->nullable()->after('mime_type');
        });

        // Backfill existing rows from the stored files where possible.
        // If the file is accessible, read size and MIME type from it.
        // original_name falls back to the stored filename.
        $disk = config('localstorage.available.images.disk', 'public');
        $directory = rtrim(config('localstorage.available.images.directory', 'images'), '/');

        DB::table('available_images')->orderBy('id')->each(function ($row) use ($disk, $directory) {
            $filename = $row->path;
            if (! $filename) {
                return;
            }

            $fullPath = $directory.'/'.$filename;
            $size = null;
            $mimeType = null;

            if (Storage::disk($disk)->exists($fullPath)) {
                try {
                    $size = Storage::disk($disk)->size($fullPath);
                } catch (Exception) {
                    // Size not available – leave null
                }
                try {
                    $detected = Storage::disk($disk)->mimeType($fullPath);
                    $mimeType = $detected ?: null;
                } catch (Exception) {
                    // MIME detection not available – leave null
                }
            }

            DB::table('available_images')
                ->where('id', $row->id)
                ->update([
                    'original_name' => $filename,
                    'mime_type' => $mimeType,
                    'size' => $size,
                ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('available_images', function (Blueprint $table) {
            $table->dropColumn(['original_name', 'mime_type', 'size']);
        });
    }
};

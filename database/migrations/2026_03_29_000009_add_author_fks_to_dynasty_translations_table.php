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
        Schema::table('dynasty_translations', function (Blueprint $table) {
            $table->uuid('author_id')->nullable()->after('date_description_ad');
            $table->uuid('text_copy_editor_id')->nullable()->after('author_id');
            $table->uuid('translator_id')->nullable()->after('text_copy_editor_id');
            $table->uuid('translation_copy_editor_id')->nullable()->after('translator_id');

            $table->foreign('author_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('text_copy_editor_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('translator_id')->references('id')->on('authors')->onDelete('set null');
            $table->foreign('translation_copy_editor_id')->references('id')->on('authors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dynasty_translations', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->dropForeign(['text_copy_editor_id']);
            $table->dropForeign(['translator_id']);
            $table->dropForeign(['translation_copy_editor_id']);

            $table->dropColumn(['author_id', 'text_copy_editor_id', 'translator_id', 'translation_copy_editor_id']);
        });
    }
};

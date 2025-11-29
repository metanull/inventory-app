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
        Schema::table('tags', function (Blueprint $table) {
            // Add category field (keyword, material, artist, dynasty)
            $table->string('category', 50)->nullable()->after('internal_name');

            // Add language_id foreign key (tags can be language-specific)
            $table->char('language_id', 3)->nullable()->after('category');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('set null');

            // Drop the unique constraint on internal_name alone
            $table->dropUnique(['internal_name']);

            // Add composite unique constraint: internal_name must be unique per (category, language)
            $table->unique(['internal_name', 'category', 'language_id'], 'tags_name_category_lang_unique');

            // Add index for category filtering
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('tags_name_category_lang_unique');

            // Re-add the simple unique constraint
            $table->unique('internal_name');

            // Drop indices and foreign key
            $table->dropIndex(['category']);
            $table->dropForeign(['language_id']);

            // Drop columns
            $table->dropColumn(['category', 'language_id']);
        });
    }
};

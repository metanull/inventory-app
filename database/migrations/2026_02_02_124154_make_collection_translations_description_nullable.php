<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Make description nullable in collection_translations table.
     * This is needed because SH projects often don't have descriptions.
     */
    public function up(): void
    {
        Schema::table('collection_translations', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collection_translations', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
        });
    }
};

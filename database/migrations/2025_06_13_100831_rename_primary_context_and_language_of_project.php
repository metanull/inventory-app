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
        Schema::table('projects', function (Blueprint $table) {
            // $table->dropForeign(['primary_context_id']); // Remove the foreign key constraint
            $table->dropColumn('primary_context_id'); // Rollback the column
            // $table->dropForeign(['primary_language_id']); // Remove the foreign key constraint
            $table->dropColumn('primary_language_id'); // Rollback the column
            $table->uuid('context_id')->nullable(true);
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('set null');   // Specify a primary context for the project, when the default context is not suitable. If not specified, the default context is used.
            $table->string('language_id', 3)->nullable(true);
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('set null'); // Specify a primary language for the project, when the default language is not suitable. If not specified, the default language is used.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['context_id']); // Remove the foreign key constraint
            $table->dropColumn('context_id'); // Rollback the column
            $table->dropForeign(['language_id']); // Remove the foreign key constraint
            $table->dropColumn('language_id'); // Rollback the column
            $table->uuid('primary_context_id')->nullable(true);   // Specify a primary context for the project, when the default context is not suitable. If not specified, the default context is used.
            $table->foreign('primary_context_id')->references('id')->on('contexts')->onDelete('set null');
            $table->string('primary_language_id', 3)->nullable(true); // Specify a primary language for the project, when the default language is not suitable. If not specified, the default language is used.
            $table->foreign('primary_language_id')->references('id')->on('languages')->onDelete('set null');
        });
    }
};

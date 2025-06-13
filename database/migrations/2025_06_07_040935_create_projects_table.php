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
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('internal_name');
            $table->string('backward_compatibility')->nullable(true);
            $table->date('launch_date')->nullable(true);    // Launch date of the project (for information purposes only; not used for any logic; for the logic, is_launched is used)
            $table->boolean('is_launched')->default(false); // Indicates if the project is active - when false, the project is not considered for display in the frontend. In principle is_launched is toggled when the launch_date is reached.
            $table->boolean('is_enabled')->default(true);   // Indicates if the project is enabled - when false, the project is not considered for display in the frontend. A project can be enabled or disabled at any time, regardless of its launch date or is_active status.
            $table->uuid('primary_context_id')->references('id')->on('contexts')->nullable(true);   // Specify a primary context for the project, when the default context is not suitable. If not specified, the default context is used.
            $table->string('primary_language_id', 3)->references('id')->on('languages')->nullable(true); // Specify a primary language for the project, when the default language is not suitable. If not specified, the default language is used.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

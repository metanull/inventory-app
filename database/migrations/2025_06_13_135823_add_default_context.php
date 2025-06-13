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
        Schema::table('contexts', function (Blueprint $table) {
            $table->boolean('is_default')->default(false); // Add a boolean column to indicate if this is the default context
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contexts', function (Blueprint $table) {
            $table->dropColumn('is_default'); // Remove the default context column
        });
    }
};

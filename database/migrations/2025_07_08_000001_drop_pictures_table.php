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
        // Optionally, you could recreate the table here if needed
        // Schema::create('pictures', function (Blueprint $table) { ... });
        Schema::dropIfExists('pictures');

        // Recreate the old pictures table structure for rollback
        Schema::create('pictures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('internal_name');
            $table->string('backward_compatibility')->nullable();
            $table->string('copyright_text')->nullable();
            $table->string('copyright_url')->nullable();
            $table->string('path');
        });
    }
};

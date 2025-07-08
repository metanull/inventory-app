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
    }
};

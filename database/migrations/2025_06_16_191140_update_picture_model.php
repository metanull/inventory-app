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
        Schema::create('pictures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('internal_name');
            $table->string('backward_compatibility')->nullable(true);
            $table->string('copyright_text')->nullable(true);
            $table->string('copyright_url')->nullable(true);
            $table->string('path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pictures');
        Schema::create('pictures', function (Blueprint $table) {
            $table->id();
            $table->string('path')->nullable();
            $table->timestamps();
        });
    }
};

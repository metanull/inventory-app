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
        Schema::create('dynasties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('from_ah')->nullable();
            $table->integer('to_ah')->nullable();
            $table->integer('from_ad')->nullable();
            $table->integer('to_ad')->nullable();
            $table->string('backward_compatibility')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynasties');
    }
};

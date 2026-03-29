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
        Schema::create('item_dynasty', function (Blueprint $table) {
            $table->uuid('item_id');
            $table->uuid('dynasty_id');
            $table->timestamps();

            // Primary key
            $table->primary(['item_id', 'dynasty_id']);

            // Foreign keys
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('dynasty_id')->references('id')->on('dynasties')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_dynasty');
    }
};

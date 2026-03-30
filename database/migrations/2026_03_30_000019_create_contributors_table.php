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
        Schema::create('contributors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('collection_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->integer('display_order')->default(0);
            $table->boolean('visible')->default(true);
            $table->string('backward_compatibility')->nullable();
            $table->string('internal_name');
            $table->timestamps();

            $table->index(['collection_id', 'category', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributors');
    }
};

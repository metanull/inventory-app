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
        Schema::create('contextualizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('context_id');
            $table->uuid('item_id')->nullable();
            $table->uuid('detail_id')->nullable();
            $table->json('extra')->nullable();
            $table->string('internal_name')->unique();
            $table->string('backward_compatibility')->nullable()->default(null);
            $table->timestamps();

            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('detail_id')->references('id')->on('details')->onDelete('cascade');

            // Note: Database-level check constraint for exactly one of item_id or detail_id
            // is handled by application logic in the model and controller validations
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contextualizations');
    }
};

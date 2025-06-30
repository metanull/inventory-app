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
        Schema::create('artists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('place_of_birth')->nullable()->default(null);
            $table->string('place_of_death')->nullable()->default(null);
            $table->string('date_of_birth')->nullable()->default(null);
            $table->string('date_of_death')->nullable()->default(null);
            $table->string('period_of_activity')->nullable()->default(null);
            $table->string('internal_name')->unique();
            $table->string('backward_compatibility')->nullable()->default(null);
            $table->timestamps();

            // Indexes for searching
            $table->index('name');
            $table->index('internal_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artists');
    }
};

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
        Schema::create('gallery_partner', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('gallery_id');
            $table->uuid('partner_id');
            $table->string('level'); // Using the PartnerLevel enum

            // Standard fields
            $table->string('backward_compatibility')->nullable()->default(null);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('gallery_id')->references('id')->on('galleries')->onDelete('cascade');
            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('cascade');

            // Unique constraint
            $table->unique(['gallery_id', 'partner_id']);

            // Indexes for performance
            $table->index(['gallery_id']);
            $table->index(['partner_id']);
            $table->index(['level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_partner');
    }
};

<?php

use App\Enums\PartnerLevel;
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
        Schema::create('collection_partner', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('collection_id');
            $table->uuid('partner_id');
            $table->enum('level', [
                PartnerLevel::PARTNER->value,
                PartnerLevel::ASSOCIATED_PARTNER->value,
                PartnerLevel::MINOR_CONTRIBUTOR->value,
            ])->default(PartnerLevel::PARTNER->value);

            // Standard fields
            $table->string('backward_compatibility')->nullable()->default(null);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('cascade');
            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('cascade');

            // Unique constraint
            $table->unique(['collection_id', 'partner_id']);

            // Indexes for performance
            $table->index(['collection_id']);
            $table->index(['partner_id']);
            $table->index(['level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_partner');
    }
};

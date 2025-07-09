<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop if exists (for SQLite compatibility in tests)
        Schema::dropIfExists('collection_partner');
        Schema::create('collection_partner', function (Blueprint $table) {
            $table->uuid('collection_id');
            $table->string('collection_type');
            $table->uuid('partner_id');
            $table->string('level')->nullable();
            $table->timestamps();

            $table->primary(['collection_id', 'collection_type', 'partner_id'], 'collection_partner_pk');
            $table->index('collection_type');
            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('cascade');
            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_partner');
    }
};

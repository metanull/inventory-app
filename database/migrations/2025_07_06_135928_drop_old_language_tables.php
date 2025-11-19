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
        Schema::dropIfExists('contact_language');
        Schema::dropIfExists('location_language');
        Schema::dropIfExists('province_language');
        Schema::dropIfExists('address_language');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot recreate these tables as their parent tables (contacts, addresses, locations, provinces)
        // were dropped in migration 2025_10_24_203917_drop_legacy_address_contact_tables.
        // These were legacy tables that are no longer needed in the current schema.
    }
};

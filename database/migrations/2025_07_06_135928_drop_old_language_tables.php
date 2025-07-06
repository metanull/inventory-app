<?php

use Illuminate\Database\Migrations\Migration;
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
        // Note: We don't recreate these tables as they are being replaced
        // by the new translation tables. If needed for rollback, refer to
        // the original migrations that created these tables.
    }
};

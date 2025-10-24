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
        // Drop legacy Address and Contact related tables
        // These have been replaced by integrated fields in Partner and PartnerTranslation
        Schema::dropIfExists('address_translations');
        Schema::dropIfExists('contact_translations');
        Schema::dropIfExists('address_language');
        Schema::dropIfExists('contact_language');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('contacts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We do not recreate these tables - they are legacy
        // If rollback is needed, the original migration files still exist in git history
    }
};

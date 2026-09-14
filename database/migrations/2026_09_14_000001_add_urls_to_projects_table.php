<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Project-level external links. Populated by the data importer at import
     * time from the legacy project metadata; editable in Filament so they can
     * be reviewed and corrected, but never hand-seeded here — a re-import
     * would otherwise wipe hand-entered values.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('site_url')->nullable()->after('is_enabled');                    // Public URL of the project's website, if any.
            $table->string('related_database_url')->nullable()->after('site_url');           // URL of a related external database, if any.
            $table->string('artistic_introduction_url')->nullable()->after('related_database_url'); // URL of the project's artistic introduction page, if any.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['site_url', 'related_database_url', 'artistic_introduction_url']);
        });
    }
};

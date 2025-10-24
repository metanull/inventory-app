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
        Schema::table('collection_partner', function (Blueprint $table) {
            // Add new fields
            $table->boolean('visible')->default(true)->after('level');
            $table->string('relationship_type')->nullable()->after('visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collection_partner', function (Blueprint $table) {
            $table->dropColumn(['visible', 'relationship_type']);
        });
    }
};

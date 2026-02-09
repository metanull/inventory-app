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
        Schema::table('item_translations', function (Blueprint $table) {
            $table->text('provenance')->nullable()->after('method_for_provenance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_translations', function (Blueprint $table) {
            $table->dropColumn('provenance');
        });
    }
};

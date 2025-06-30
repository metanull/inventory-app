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
        Schema::table('items', function (Blueprint $table) {
            $table->string('owner_reference')->nullable()->default(null)->index();
            $table->string('mwnf_reference')->nullable()->default(null)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex(['owner_reference']);
            $table->dropIndex(['mwnf_reference']);
            $table->dropColumn(['owner_reference', 'mwnf_reference']);
        });
    }
};

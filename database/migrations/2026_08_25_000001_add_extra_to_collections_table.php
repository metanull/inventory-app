<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Collection-level structured attributes. Translations and the
     * collection_item pivot already carry an `extra` payload; collections
     * themselves had nowhere to hold attributes that belong to the collection
     * rather than to one of its languages — for instance a DXA thematic
     * gallery's source project code, URL slug and canonical host, which were
     * being duplicated into every per-language translation row.
     */
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->json('extra')->nullable()->after('backward_compatibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn('extra');
        });
    }
};

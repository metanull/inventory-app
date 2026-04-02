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
        Schema::table('authors', function (Blueprint $table) {
            $table->string('firstname', 100)->nullable()->after('name');
            $table->string('lastname', 100)->nullable()->after('firstname');
            $table->string('givenname', 100)->nullable()->after('lastname');
            $table->string('originalname', 255)->nullable()->after('givenname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            $table->dropColumn(['firstname', 'lastname', 'givenname', 'originalname']);
        });
    }
};

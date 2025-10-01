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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('email_2fa_enabled')->default(false)->after('two_factor_confirmed_at');
            $table->enum('preferred_2fa_method', ['totp', 'email', 'both'])->default('totp')->after('email_2fa_enabled');

            // Add indexes for performance
            $table->index(['email_2fa_enabled']);
            $table->index(['preferred_2fa_method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email_2fa_enabled']);
            $table->dropIndex(['preferred_2fa_method']);
            $table->dropColumn(['email_2fa_enabled', 'preferred_2fa_method']);
        });
    }
};

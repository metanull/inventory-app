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
        // Drop the email_two_factor_codes table
        Schema::dropIfExists('email_two_factor_codes');

        // Remove email 2FA columns from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email_2fa_enabled']);
            $table->dropIndex(['preferred_2fa_method']);
            $table->dropColumn(['email_2fa_enabled', 'preferred_2fa_method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the email_two_factor_codes table
        Schema::create('email_two_factor_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('code', 6);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'code']);
            $table->index(['expires_at']);
            $table->index(['used_at']);
        });

        // Recreate email 2FA columns in users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('email_2fa_enabled')->default(false)->after('two_factor_confirmed_at');
            $table->enum('preferred_2fa_method', ['totp', 'email', 'both'])->default('totp')->after('email_2fa_enabled');

            $table->index(['email_2fa_enabled']);
            $table->index(['preferred_2fa_method']);
        });
    }
};

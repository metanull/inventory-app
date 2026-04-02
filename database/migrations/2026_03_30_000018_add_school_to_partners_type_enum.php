<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only needed for MariaDB/MySQL - SQLite doesn't enforce enum constraints
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE partners MODIFY COLUMN type ENUM('museum', 'institution', 'individual', 'school') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE partners MODIFY COLUMN type ENUM('museum', 'institution', 'individual') NOT NULL");
        }
    }
};

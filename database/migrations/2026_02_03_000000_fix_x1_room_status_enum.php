<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Fix: Add 'pending_payment' back to ENUM, along with 'finished'
        DB::statement("ALTER TABLE `x1_rooms` MODIFY `status` ENUM('pending_payment','open','in_progress','closed','cancelled','finished') NOT NULL DEFAULT 'pending_payment'");
    }

    public function down(): void
    {
        // This is tricky to reverse perfectly if we have pending_payment data, but we revert to the previous state (which was missing pending_payment but had finished)
        // or we just keep it as is.
        DB::statement("ALTER TABLE `x1_rooms` MODIFY `status` ENUM('open','in_progress','closed','cancelled','finished') DEFAULT 'open'");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE `x1_rooms` MODIFY `status` ENUM('pending_payment','open','in_progress','closed','cancelled') NOT NULL DEFAULT 'pending_payment'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `x1_rooms` MODIFY `status` ENUM('open','in_progress','closed','cancelled') NOT NULL DEFAULT 'open'");
    }
};

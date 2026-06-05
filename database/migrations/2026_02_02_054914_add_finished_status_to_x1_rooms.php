<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alterar o ENUM do status para incluir 'finished'
        DB::statement("ALTER TABLE x1_rooms MODIFY COLUMN status ENUM('open','in_progress','closed','cancelled','finished') DEFAULT 'open'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Voltar ao ENUM original (remover 'finished')
        DB::statement("ALTER TABLE x1_rooms MODIFY COLUMN status ENUM('open','in_progress','closed','cancelled') DEFAULT 'open'");
    }
};

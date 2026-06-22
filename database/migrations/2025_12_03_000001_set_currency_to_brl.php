<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('general_settings')) {
            return;
        }

        DB::table('general_settings')
            ->where('id', 1)
            ->update([
                'cur_text'   => 'BRL',
                'cur_sym'    => 'R$',
                'updated_at' => now(),
            ]);
    }

    public function down(): void {
        if (!Schema::hasTable('general_settings')) {
            return;
        }

        DB::table('general_settings')
            ->where('id', 1)
            ->update([
                'cur_text'   => 'USD',
                'cur_sym'    => '$',
                'updated_at' => now(),
            ]);
    }
};

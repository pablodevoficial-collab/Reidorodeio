<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rodeios', function (Blueprint $table) {
            if (!Schema::hasColumn('rodeios', 'divisao_atual')) {
                $table->string('divisao_atual', 100)->nullable()->after('modalidade_atual');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rodeios', function (Blueprint $table) {
            if (Schema::hasColumn('rodeios', 'divisao_atual')) {
                $table->dropColumn('divisao_atual');
            }
        });
    }
};

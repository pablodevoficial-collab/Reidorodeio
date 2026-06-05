<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            // Some installations already have these columns; add only if missing.
            if (!Schema::hasColumn('users', 'cpf')) {
                $table->string('cpf', 20)->nullable();
            }

            if (!Schema::hasColumn('users', 'birthdate')) {
                $table->date('birthdate')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'birthdate')) {
                $table->dropColumn('birthdate');
            }

            if (Schema::hasColumn('users', 'cpf')) {
                $table->dropColumn('cpf');
            }
        });
    }
};

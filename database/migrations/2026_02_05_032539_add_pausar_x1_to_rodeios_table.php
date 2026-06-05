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
        Schema::table('rodeios', function (Blueprint $table) {
            $table->boolean('pausar_x1')->default(false)->after('status')->comment('Pausar criação de salas X1 para esta modalidade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rodeios', function (Blueprint $table) {
            $table->dropColumn('pausar_x1');
        });
    }
};

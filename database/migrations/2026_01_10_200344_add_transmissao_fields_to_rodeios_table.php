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
            $table->unsignedBigInteger('modalidade_atual')->nullable()->after('id');
            $table->string('status_transmissao')->nullable()->after('modalidade_atual');
            $table->text('stream_url')->nullable()->after('status_transmissao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rodeios', function (Blueprint $table) {
            $table->dropColumn(['modalidade_atual', 'status_transmissao', 'stream_url']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ranking_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rodeio_id')->nullable()->index();
            $table->unsignedBigInteger('modalidade_id')->nullable()->index();
            $table->json('payload');
            $table->timestamp('generated_at')->index();
            $table->timestamps();

            $table->index(['modalidade_id', 'generated_at']);
            $table->index(['rodeio_id', 'modalidade_id', 'generated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranking_snapshots');
    }
};

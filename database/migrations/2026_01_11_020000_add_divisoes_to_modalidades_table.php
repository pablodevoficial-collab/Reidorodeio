<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modalidades', function (Blueprint $table) {
            if (!Schema::hasColumn('modalidades', 'tem_divisoes')) {
                $table->boolean('tem_divisoes')->default(false)->after('status');
            }
            if (!Schema::hasColumn('modalidades', 'divisoes')) {
                $table->json('divisoes')->nullable()->after('tem_divisoes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('modalidades', function (Blueprint $table) {
            if (Schema::hasColumn('modalidades', 'divisoes')) {
                $table->dropColumn('divisoes');
            }
            if (Schema::hasColumn('modalidades', 'tem_divisoes')) {
                $table->dropColumn('tem_divisoes');
            }
        });
    }
};

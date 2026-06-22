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
        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'mp_preapproval_id')) {
                $table->string('mp_preapproval_id')->nullable()->after('transaction_id');
                $table->index('mp_preapproval_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'mp_preapproval_id')) {
                $table->dropIndex(['mp_preapproval_id']);
                $table->dropColumn('mp_preapproval_id');
            }
        });
    }
};

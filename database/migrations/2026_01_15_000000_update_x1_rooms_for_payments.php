<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('x1_rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('x1_rooms', 'host_user_id')) {
                $table->unsignedBigInteger('host_user_id')->nullable();
            }
            if (!Schema::hasColumn('x1_rooms', 'rodeio_id')) {
                $table->unsignedBigInteger('rodeio_id')->nullable();
            }
            if (!Schema::hasColumn('x1_rooms', 'competitor_id')) {
                $table->unsignedBigInteger('competitor_id')->nullable();
            }
            if (!Schema::hasColumn('x1_rooms', 'is_private')) {
                $table->boolean('is_private')->default(false);
            }
            if (!Schema::hasColumn('x1_rooms', 'access_code')) {
                $table->string('access_code', 64)->nullable();
            }
            if (!Schema::hasColumn('x1_rooms', 'fee_percent')) {
                $table->decimal('fee_percent', 5, 2)->default(20);
            }
            if (!Schema::hasColumn('x1_rooms', 'prize_total')) {
                $table->decimal('prize_total', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('x1_rooms', 'currency')) {
                $table->string('currency', 8)->default('BRL');
            }
            if (!Schema::hasColumn('x1_rooms', 'host_paid_at')) {
                $table->timestamp('host_paid_at')->nullable();
            }
        });

        Schema::table('x1_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('x1_participants', 'competitor_id')) {
                $table->unsignedBigInteger('competitor_id')->nullable();
            }
            if (!Schema::hasColumn('x1_participants', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('x1_participants', 'payment_status')) {
                $table->string('payment_status', 24)->default('pending');
            }
            if (!Schema::hasColumn('x1_participants', 'paid_at')) {
                $table->timestamp('paid_at')->nullable();
            }
            if (!Schema::hasColumn('x1_participants', 'is_host')) {
                $table->boolean('is_host')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('x1_participants', function (Blueprint $table) {
            if (Schema::hasColumn('x1_participants', 'is_host')) {
                $table->dropColumn('is_host');
            }
            if (Schema::hasColumn('x1_participants', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
            if (Schema::hasColumn('x1_participants', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('x1_participants', 'amount')) {
                $table->dropColumn('amount');
            }
            if (Schema::hasColumn('x1_participants', 'competitor_id')) {
                $table->dropColumn('competitor_id');
            }
        });

        Schema::table('x1_rooms', function (Blueprint $table) {
            if (Schema::hasColumn('x1_rooms', 'host_paid_at')) {
                $table->dropColumn('host_paid_at');
            }
            if (Schema::hasColumn('x1_rooms', 'currency')) {
                $table->dropColumn('currency');
            }
            if (Schema::hasColumn('x1_rooms', 'prize_total')) {
                $table->dropColumn('prize_total');
            }
            if (Schema::hasColumn('x1_rooms', 'fee_percent')) {
                $table->dropColumn('fee_percent');
            }
            if (Schema::hasColumn('x1_rooms', 'access_code')) {
                $table->dropColumn('access_code');
            }
            if (Schema::hasColumn('x1_rooms', 'is_private')) {
                $table->dropColumn('is_private');
            }
            if (Schema::hasColumn('x1_rooms', 'competitor_id')) {
                $table->dropColumn('competitor_id');
            }
            if (Schema::hasColumn('x1_rooms', 'rodeio_id')) {
                $table->dropColumn('rodeio_id');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('x1_payments')) {
            Schema::create('x1_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('x1_room_id');
                $table->unsignedBigInteger('user_id');
                $table->string('role', 16); // host|opponent
                $table->decimal('amount', 10, 2);
                $table->decimal('fee_percent', 5, 2)->default(20);
                $table->string('provider', 32)->default('mercadopago');
                $table->string('external_reference', 128)->nullable();
                $table->string('provider_payment_id', 128)->nullable();
                $table->string('provider_preference_id', 128)->nullable();
                $table->string('status', 24)->default('pending');
                $table->json('payload')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();

                $table->foreign('x1_room_id')->references('id')->on('x1_rooms')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('x1_payments');
    }
};

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
        Schema::create('bot_users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname', 50);
            $table->string('lastname', 50);
            $table->string('username', 100)->unique();
            $table->string('email', 100)->unique();
            $table->string('mobile', 20)->nullable();
            $table->string('cpf', 14)->nullable();
            $table->boolean('is_premium')->default(false);
            $table->timestamp('premium_until')->nullable();
            $table->timestamps();
            
            $table->index('username');
            $table->index('is_premium');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_users');
    }
};

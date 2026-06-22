<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rodeio_email_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rodeio_id')->constrained('rodeios')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->timestamp('confirmation_sent_at')->nullable();
            $table->timestamp('live_notification_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['rodeio_id', 'email']);
            $table->index(['rodeio_id', 'live_notification_sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rodeio_email_reminders');
    }
};

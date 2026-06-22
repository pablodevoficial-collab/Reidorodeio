<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fantasy_league_opening_reminders', function (Blueprint $table) {
            $table->id();
            $table->string('slot_key', 20);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->timestamp('opened_notification_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['slot_key', 'email']);
            $table->index(['slot_key', 'opened_notification_sent_at'], 'fantasy_opening_slot_sent_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fantasy_league_opening_reminders');
    }
};
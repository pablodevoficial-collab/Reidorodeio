<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FantasyLeagueOpeningReminder extends Model
{
    protected $fillable = [
        'slot_key',
        'user_id',
        'email',
        'name',
        'opened_notification_sent_at',
    ];

    protected $casts = [
        'opened_notification_sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
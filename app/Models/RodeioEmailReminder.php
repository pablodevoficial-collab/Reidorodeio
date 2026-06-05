<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RodeioEmailReminder extends Model
{
    protected $fillable = [
        'rodeio_id',
        'user_id',
        'email',
        'name',
        'confirmation_sent_at',
        'live_notification_sent_at',
    ];

    protected $casts = [
        'confirmation_sent_at' => 'datetime',
        'live_notification_sent_at' => 'datetime',
    ];

    public function rodeio(): BelongsTo
    {
        return $this->belongsTo(Rodeio::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

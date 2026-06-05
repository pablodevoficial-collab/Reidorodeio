<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class X1Result extends Model
{
    protected $table = 'x1_results';

    protected $fillable = [
        'x1_room_id',
        'winner_user_id',
        'payload',
        'processed_at',
        'prize_paid_at',
        'prize_paid_by',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
        'prize_paid_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(X1RoomInstance::class, 'x1_room_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }
}

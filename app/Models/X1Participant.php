<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class X1Participant extends Model
{
    protected $table = 'x1_participants';

    protected $fillable = [
        'x1_room_id',
        'user_id',
        'competitor_id',
        'competitor_group_id',
        'amount',
        'slot',
        'result',
        'payment_status',
        'paid_at',
        'is_host',
    ];

    protected $casts = [
        'result' => 'array',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'is_host' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(X1RoomInstance::class, 'x1_room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function competitor()
    {
        return $this->belongsTo(Competitor::class, 'competitor_id');
    }

    public function competitorGroup()
    {
        return $this->belongsTo(ModalidadeCompetitorGroup::class, 'competitor_group_id');
    }
}

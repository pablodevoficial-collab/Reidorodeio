<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class X1Payment extends Model
{
    protected $table = 'x1_payments';

    protected $fillable = [
        'x1_room_id',
        'user_id',
        'role',
        'amount',
        'fee_percent',
        'provider',
        'external_reference',
        'provider_payment_id',
        'provider_preference_id',
        'status',
        'payload',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_percent' => 'decimal:2',
        'payload' => 'array',
        'paid_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(X1RoomInstance::class, 'x1_room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

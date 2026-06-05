<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralUser extends Model
{
    protected $fillable = [
        'affiliate_id',
        'referred_user_id',
        'status',
        'first_purchase_at',
    ];

    protected $casts = [
        'first_purchase_at' => 'datetime',
    ];

    /**
     * Get the affiliate that referred this user
     */
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * Get the referred user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}

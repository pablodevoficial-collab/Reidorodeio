<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppUserVoucher extends Model
{
    protected $fillable = [
        'user_id',
        'app_store_product_id',
        'app_store_purchase_id',
        'voucher_type',
        'status',
        'title',
        'description',
        'credit_amount',
        'remaining_uses',
        'fantasy_league_id',
        'activated_at',
        'expires_at',
        'used_at',
        'metadata',
    ];

    protected $casts = [
        'credit_amount' => 'decimal:2',
        'remaining_uses' => 'integer',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(AppStoreProduct::class, 'app_store_product_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(AppStorePurchase::class, 'app_store_purchase_id');
    }

    public function fantasyLeague(): BelongsTo
    {
        return $this->belongsTo(FantasyLeague::class);
    }

    public function isUsableForAmount(float $amount): bool
    {
        $normalizedAmount = round($amount, 2);

        return $this->status === 'active'
            && (int) $this->remaining_uses > 0
            && in_array($normalizedAmount, [20.00, 50.00, 100.00], true)
            && round((float) $this->credit_amount, 2) === $normalizedAmount
            && (!$this->expires_at || $this->expires_at->isFuture());
    }
}

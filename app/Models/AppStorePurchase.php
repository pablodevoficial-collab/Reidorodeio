<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppStorePurchase extends Model
{
    protected $fillable = [
        'user_id',
        'app_store_product_id',
        'purchase_kind',
        'status',
        'payment_method',
        'provider',
        'amount',
        'wallet_credit_amount',
        'external_reference',
        'provider_payment_id',
        'provider_preference_id',
        'description',
        'payload',
        'expires_at',
        'paid_at',
        'fulfilled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'wallet_credit_amount' => 'decimal:2',
        'payload' => 'array',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'fulfilled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(AppStoreProduct::class, 'app_store_product_id');
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(AppWalletTransaction::class, 'app_store_purchase_id');
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(AppUserVoucher::class, 'app_store_purchase_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && (!$this->expires_at || $this->expires_at->isFuture());
    }
}

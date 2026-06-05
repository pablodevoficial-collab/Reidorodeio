<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppStoreProduct extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'subtitle',
        'description',
        'product_type',
        'price',
        'currency',
        'payment_methods',
        'badge',
        'badge_color',
        'is_featured',
        'is_active',
        'sort_order',
        'android_product_id',
        'ios_product_id',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'payment_methods' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(AppStorePurchase::class, 'app_store_product_id');
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(AppUserVoucher::class, 'app_store_product_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function supportsPaymentMethod(string $method): bool
    {
        $methods = $this->payment_methods ?? [];
        return in_array($method, $methods, true);
    }
}

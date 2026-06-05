<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class StoreProductSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'commission_percent',
        'photos',
        'status',
        'admin_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'photos' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPhotoUrlsAttribute(): array
    {
        return collect($this->photos ?? [])
            ->filter()
            ->map(fn ($path) => Storage::disk('public')->url($path))
            ->values()
            ->all();
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'R$ ' . number_format((float) $this->price, 2, ',', '.');
    }
}

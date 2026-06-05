<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppCommunityPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'subtype',
        'user_id',
        'emoji',
        'title',
        'body',
        'metadata',
        'dedupe_key',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

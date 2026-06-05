<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model{
    protected $fillable = [
        'user_id',
        'is_app',
        'token',
    ];

    protected $casts = [
        'is_app' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'title','slug','excerpt','content','image_path','status','published_at'
    ];

    protected $casts = [
        'status' => 'boolean',
        'published_at' => 'datetime',
    ];
}

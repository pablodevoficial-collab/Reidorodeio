<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title','slug','excerpt','content','image_path','status','published_at','start_date','end_date','location'
    ];

    protected $casts = [
        'status' => 'boolean',
        'published_at' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];
}

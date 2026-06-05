<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TouristPlace extends Model
{
    protected $fillable = [
        'title','slug','excerpt','content','image_path','city','location','latitude','longitude','status','published_at'
    ];

    protected $casts = [
        'status' => 'boolean',
        'published_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}

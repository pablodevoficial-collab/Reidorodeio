<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'link',
        'image',
        'image_web',
        'image_mobile',
        'position',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/banners/' . $this->image) : null;
    }

    public function getImageWebUrlAttribute()
    {
        return $this->image_web ? asset('storage/banners/' . $this->image_web) : null;
    }

    public function getImageMobileUrlAttribute()
    {
        return $this->image_mobile ? asset('storage/banners/' . $this->image_mobile) : null;
    }

    public function getPositionLabelAttribute()
    {
        $positions = [
            'home_top' => 'Home • Topo',
            'home_middle' => 'Home • Meio',
            'home_bottom' => 'Home • Rodapé',
        ];

        return $positions[$this->position] ?? $this->position;
    }
}

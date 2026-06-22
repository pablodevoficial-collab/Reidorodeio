<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model {

    protected $guarded = ['id'];

    public function category() {
        return $this->belongsTo(Rodeio::class, 'rodeio_id');
    }

    public function teamImage() {
        return getImage(getFilePath('team') . '/' . $this->image, $this->name[0]);
    }
}

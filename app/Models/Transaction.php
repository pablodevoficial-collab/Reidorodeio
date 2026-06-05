<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'charge',
        'post_balance',
        'trx_type',
        'details',
        'trx',
        'remark'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

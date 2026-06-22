<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rodeio extends Model
{
    use GlobalStatus;
    
    protected $table = 'rodeios';
    protected $guarded = [];
    protected $casts = [
        'info' => 'array',
        'logo' => 'string',
        'start' => 'datetime',
        'end' => 'datetime',
        'status_transmissao' => 'string',
        'divisao_atual' => 'string',
        'pausar_x1' => 'boolean',
    ];

    public function modalidades()
    {
        return $this->hasMany(Modalidade::class, 'rodeio_id');
    }

    public function emailReminders(): HasMany
    {
        return $this->hasMany(RodeioEmailReminder::class, 'rodeio_id');
    }

    public function modalidadeAtual()
    {
        return $this->belongsTo(Modalidade::class, 'modalidade_atual');
    }
}

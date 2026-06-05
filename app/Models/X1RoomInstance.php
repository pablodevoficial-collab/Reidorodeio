<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class X1RoomInstance extends Model
{
    protected $table = 'x1_rooms';

    protected $fillable = [
        'criador_id', // Coluna antiga
        'oponente_id', // Coluna antiga
        'bot_criador_id', // Bot criador
        'bot_oponente_id', // Bot oponente
        'competitor_escolhido_criador', // Coluna antiga
        'competitor_escolhido_oponente', // Coluna antiga
        'host_user_id',
        'rodeio_id',
        'name',
        'description',
        'modalidade_id',
        'competitor_id',
        'competitor_group_id',
        'valor_entrada',
        'is_private',
        'access_code',
        'fee_percent',
        'is_premium_room',
        'is_bot_room',
        'prize_total',
        'currency',
        'status',
        'metadata',
        'closed_at',
        'host_paid_at',
        'finished_at',
        'expires_at',
        'divisao',
    ];

    protected $casts = [
        'metadata' => 'array',
        'valor_entrada' => 'decimal:2',
        'fee_percent' => 'decimal:2',
        'prize_total' => 'decimal:2',
        'is_private' => 'boolean',
        'is_premium_room' => 'boolean',
        'is_bot_room' => 'boolean',
        'host_paid_at' => 'datetime',
        'closed_at' => 'datetime',
        'finished_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function host()
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function hostBot()
    {
        return $this->belongsTo(\App\Models\BotUser::class, 'bot_criador_id');
    }

    public function opponentBot()
    {
        return $this->belongsTo(\App\Models\BotUser::class, 'bot_oponente_id');
    }

    public function participants()
    {
        return $this->hasMany(X1Participant::class, 'x1_room_id');
    }

    public function payments()
    {
        return $this->hasMany(X1Payment::class, 'x1_room_id');
    }

    public function modalidade()
    {
        return $this->belongsTo(Modalidade::class, 'modalidade_id');
    }

    public function rodeio()
    {
        return $this->belongsTo(Rodeio::class, 'rodeio_id');
    }

    public function competitor()
    {
        return $this->belongsTo(Competitor::class, 'competitor_id');
    }

    public function competitorGroup()
    {
        return $this->belongsTo(ModalidadeCompetitorGroup::class, 'competitor_group_id');
    }

    public function result()
    {
        return $this->hasOne(X1Result::class, 'x1_room_id');
    }
}

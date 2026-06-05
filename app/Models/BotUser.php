<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BotUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'firstname',
        'lastname',
        'username',
        'email',
        'mobile',
        'cpf',
        'referred_by_id',
        'is_premium',
        'premium_until',
    ];

    protected $casts = [
        'is_premium' => 'boolean',
        'premium_until' => 'datetime',
    ];

    /**
     * Verificar se o bot é premium
     */
    public function isPremium()
    {
        if (!$this->is_premium) {
            return false;
        }
        
        if ($this->premium_until && $this->premium_until->isPast()) {
            // Premium expirado
            $this->update(['is_premium' => false, 'premium_until' => null]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Retorna username mascarado (bots sempre ocultos com **)
     */
    public function getPublicUsername(): string
    {
        return \App\Models\User::maskUsername($this->username ?? 'Bot');
    }

    /**
     * Nome completo
     */
    public function getFullnameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }
}

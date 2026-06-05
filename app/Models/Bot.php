<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Bot extends User
{
    protected $table = 'users';

    /**
     * O "booted" method é executado sempre que este Model é chamado.
     * Aqui aplicamos o filtro global para trazer APENAS bots.
     */
    protected static function booted()
    {
        static::addGlobalScope('bot', function (Builder $builder) {
            $builder->where('is_bot', true);
        });
    }

    /**
     * Ao criar um novo Bot via código (Bot::create([...])), 
     * já força o is_bot = true automaticamente.
     */
    public function save(array $options = [])
    {
        $this->is_bot = true;
        parent::save($options);
    }
}

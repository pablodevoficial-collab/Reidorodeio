<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Modalidade extends Model
{
    use GlobalStatus;
    protected $table = 'modalidades';
    protected $fillable = [
        'rodeio_id', 'nome', 'inicio', 'tipo_premio', 'valor_premio', 'descricao_premio',
        'status',
        'tipo_participacao',
        'tamanho_equipe',
        'tem_divisoes',
        'divisoes',
        'pausar_x1'
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'tamanho_equipe' => 'integer',
        'tem_divisoes' => 'boolean',
        'divisoes' => 'array',
        'pausar_x1' => 'boolean',
    ];

    /**
     * Extrai apenas os nomes das divisões (compatível com formato antigo e novo)
     */
    public function getDivisoesNomesAttribute(): array
    {
        $divisoes = $this->divisoes ?? [];
        if (empty($divisoes)) {
            return [];
        }
        
        $nomes = [];
        foreach ($divisoes as $v) {
            if (is_array($v) && isset($v['nome'])) {
                $nomes[] = $v['nome'];
            } elseif (is_string($v)) {
                $nomes[] = $v;
            }
        }
        
        return $nomes;
    }

    /**
     * Busca os dados completos de uma divisão pelo nome
     */
    public function getDivisaoByNome(string $nome): ?array
    {
        $divisoes = $this->divisoes ?? [];
        foreach ($divisoes as $v) {
            if (is_array($v) && isset($v['nome']) && mb_strtolower($v['nome']) === mb_strtolower($nome)) {
                return $v;
            } elseif (is_string($v) && mb_strtolower($v) === mb_strtolower($nome)) {
                return ['nome' => $v, 'tipo_premio' => 'dinheiro', 'valor_premio' => null, 'descricao_premio' => null];
            }
        }
        return null;
    }

    public function rodeio(): BelongsTo
    {
        return $this->belongsTo(Rodeio::class, 'rodeio_id');
    }

    public function competitors(): BelongsToMany
    {
    return $this->belongsToMany(Competitor::class, 'competitor_modalidade', 'modalidade_id', 'competitor_id')
            ->withPivot(['divisao', 'status', 'numero_participacao', 'multiplicador_atual', 'disponivel_participacao', 'dados_especificos', 'observacoes'])
            ->withTimestamps();
    }

    public function competitorGroups(): HasMany
    {
        return $this->hasMany(ModalidadeCompetitorGroup::class, 'modalidade_id');
    }

    public function oddsSetting(): HasOne
    {
        return $this->hasOne(ModalidadeOddsSetting::class, 'modalidade_id');
    }
}

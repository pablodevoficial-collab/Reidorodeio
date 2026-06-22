<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Competitor extends Model
{
    use GlobalStatus;

    private const DEFAULT_FOTO = 'assets/images/logo_icon/favicon.png';

    protected $fillable = [
        'nome',
        'biografia',
        'foto',
        'status',
        'nivel',
        'profile_claimed',
        'claimed_user_id',
    ];


    protected $casts = [
        'status' => 'string',
        'nivel' => 'string',
        'profile_claimed' => 'boolean',
    ];

    /**
     * Get the competitor's statistics.
     */
    public function stats(): HasOne
    {
        return $this->hasOne(CompetitorStat::class);
    }

    /**
     * Get the modalidades that the competitor is participating in.
     */
    public function modalidades(): BelongsToMany
    {
        return $this->belongsToMany(Modalidade::class, 'competitor_modalidade', 'competitor_id', 'modalidade_id')
					->withPivot(['divisao', 'status', 'numero_participacao', 'multiplicador_atual', 'disponivel_participacao', 'observacoes'])
                    ->withTimestamps();
    }

    /**
     * Get the competitor's scoring logs.
     */
    public function scoringLogs(): HasMany
    {
        return $this->hasMany(CompetitorScoringLog::class);
    }

    public function followers(): HasMany
    {
        return $this->hasMany(CompetitorFollower::class);
    }

    public function followerUsers(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            CompetitorFollower::class,
            'competitor_id',
            'id',
            'id',
            'user_id'
        );
    }

    public function followEvents(): HasMany
    {
        return $this->hasMany(CompetitorFollowEvent::class)->latest();
    }

    public function claimedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_user_id');
    }

    /**
     * Get the competitor's approval rate.
     */
    public function getAproveitamentoAttribute()
    {
        if (!$this->stats) {
            return 0;
        }

        $boas = (int) ($this->stats->count_boa ?? 0);
        $negativas = (int) ($this->stats->count_negativas_total ?? 0);
        $total = $boas + $negativas;

        if ($total > 0) {
            return round(($boas / $total) * 100, 2);
        }

        return (float) ($this->stats->aproveitamento ?? 0);
    }

    /**
     * Resolve a URL publica da foto em qualquer formato legacy/salvo pelo admin.
     */
    public function getFotoUrlAttribute(): string
    {
        if ($this->relationLoaded('claimedUser') && $this->claimedUser?->image) {
            return asset(getFilePath('userProfile') . '/' . $this->claimedUser->image);
        }

        if (!$this->relationLoaded('claimedUser') && $this->claimed_user_id) {
            $claimedUserImage = $this->claimedUser()->value('image');
            if (!empty($claimedUserImage)) {
                return asset(getFilePath('userProfile') . '/' . $claimedUserImage);
            }
        }

        $resolved = publicStorageUrl($this->foto);
        if ($resolved === '') {
            return asset(self::DEFAULT_FOTO);
        }

        return $resolved;
    }
}

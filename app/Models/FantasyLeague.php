<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FantasyLeague extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'name',
        'category',
        'image',
        'price',
        'house_cut_percent',
        'is_premium',
        'reward_mode',
        'manual_prize_pool',
        'prize_type',
        'prize_description',
        'prize_items',
        'total_prize',
        'prize_distribution',
        'is_active',
        'is_bot_league',
        'max_users',
        'paid_positions_override',
        'season_id',
        'rodeio_id',
        'modalidade_id',
        'organizer_sponsor_id',
        'divisao',
        'closes_at',
        'registration_deadline',
        'allow_late_registration',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'house_cut_percent' => 'decimal:2',
        'is_premium' => 'boolean',
        'image' => 'string',
        'reward_mode' => 'string',
        'manual_prize_pool' => 'decimal:2',
        'prize_type' => 'string',
        'prize_description' => 'string',
        'prize_items' => 'array',
        'total_prize' => 'decimal:2',
        'prize_distribution' => 'array',
        'is_active' => 'boolean',
        'max_users' => 'integer',
        'paid_positions_override' => 'integer',
        'season_id' => 'integer',
        'rodeio_id' => 'integer',
        'modalidade_id' => 'integer',
        'organizer_sponsor_id' => 'integer',
        'divisao' => 'string',
        'closes_at' => 'datetime',
        'registration_deadline' => 'datetime',
        'finalized_at' => 'datetime',
        'allow_late_registration' => 'boolean',
    ];

    public function getImageUrlAttribute(): string
    {
        $v = trim((string) (optional($this->rodeio)->logo ?? $this->image ?? ''));
        if ($v === '') {
            return $this->withImageVersion(asset('assets/images/logo_icon/logo.png'));
        }

        if (filter_var($v, FILTER_VALIDATE_URL)) {
            return $this->withImageVersion($v);
        }

        $v = str_replace('\\', '/', $v);
        $v = ltrim($v, '/');
        $lower = strtolower($v);

        if (str_starts_with($lower, 'public/')) {
            $v = substr($v, strlen('public/'));
            $lower = strtolower($v);
        }

        if (str_starts_with($lower, 'storage/')) {
            return $this->withImageVersion(asset($v));
        }

        if (str_starts_with($lower, 'assets/')) {
            return $this->withImageVersion(asset($v));
        }

        return $this->withImageVersion(asset('storage/' . $v));
    }

    private function withImageVersion(string $url): string
    {
        $version = (string) (($this->updated_at?->timestamp) ?: time());
        $joiner = str_contains($url, '?') ? '&' : '?';
        return $url . $joiner . 'v=' . $version;
    }

    /**
     * Verifica se inscrições estão abertas
     */
    public function isRegistrationOpen(): bool
    {
        // Se permitir inscrições tardias, sempre aberto
        if ($this->allow_late_registration) {
            return true;
        }
        
        // Se não tem deadline, está aberto
        if (!$this->registration_deadline) {
            return true;
        }
        
        // Verifica se ainda não passou o deadline
        return now()->lt($this->registration_deadline);
    }

    /**
     * Retorna tempo restante para inscrições em segundos
     */
    public function getRegistrationTimeLeftAttribute(): ?int
    {
        if (!$this->registration_deadline) {
            return null;
        }
        
        $diff = now()->diffInSeconds($this->registration_deadline, false);
        return $diff > 0 ? $diff : 0;
    }

    /**
     * Retorna status das inscrições
     */
    public function getRegistrationStatusAttribute(): string
    {
        if ($this->allow_late_registration) {
            return 'always_open';
        }
        
        if (!$this->registration_deadline) {
            return 'open';
        }
        
        return $this->isRegistrationOpen() ? 'open' : 'closed';
    }

    public function rodeio(): BelongsTo
    {
        return $this->belongsTo(Rodeio::class, 'rodeio_id');
    }

    public function modalidade(): BelongsTo
    {
        return $this->belongsTo(Modalidade::class, 'modalidade_id');
    }

    public function organizerSponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class, 'organizer_sponsor_id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(FantasyTeam::class, 'fantasy_league_id');
    }
}

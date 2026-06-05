<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\UserNotify;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable {
    use HasApiTokens, UserNotify;

    /**
     * Escopo para filtrar apenas usuários REAIS (humanos)
     * Utilitário crucial para exportações de marketing para não gastar com bots.
     * Exemplo: User::real()->select('email')->get();
     */
    public function scopeReal(Builder $query)
    {
        return $query->withoutGlobalScope('bot')->where(function($q) {
            $q->where('is_bot', false)
              ->orWhereNull('is_bot');
        });
    }

    /**
     * Escopo para filtrar apenas BOTS
     * Exemplo de uso: User::bots()->get();
     */
    public function scopeBots(Builder $query)
    {
        return $query->where('is_bot', true);
    }
    
    /**
     * Booted: garante que ao CRIAR um usuário, se ele não for marcado,
     * é considerado humano (is_bot = 0).
     */
    protected static function booted()
    {
        static::creating(function ($user) {
            if (is_null($user->is_bot)) {
                $user->is_bot = false;
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'username',
        'email',
        'mobile',
        'password',
        'cpf',
        'is_bot',
        'show_in_listings',
        'ev',
        'sv',
        'kv',
        'country_code',
        'country_name',
        'dial_code',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'ver_code',
        'kyc_data',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'kyc_data'          => 'object',
        'ver_code_send_at'  => 'datetime',
        'birthdate'         => 'date',
        'show_in_listings'  => 'boolean',
    ];

    public function loginLogs() {
        return $this->hasMany(UserLogin::class);
    }


    public function refBy() {
        return $this->belongsTo(self::class, 'ref_by');
    }

    public function referrals() {
        return $this->hasMany(self::class, 'ref_by');
    }

    public function allReferrals() {
        return $this->referrals()->with('refBy');
    }

    public function commissions() {
        return $this->hasMany(CommissionLog::class, 'to_id')->orderBy('id', 'desc');
    }

    public function tickets() {
        return $this->hasMany(SupportTicket::class);
    }

    public function fullname(): Attribute {
        return new Attribute(
            get: fn() => $this->firstname . ' ' . $this->lastname,
        );
    }

    public function mobileNumber(): Attribute {
        return new Attribute(
            get: fn() => $this->dial_code . $this->mobile,
        );
    }

    // SCOPES
    public function scopeActive($query) {
        return $query->where('status', Status::USER_ACTIVE)->where('ev', Status::VERIFIED)->where('sv', Status::VERIFIED);
    }

    public function scopeBanned($query) {
        return $query->where('status', Status::USER_BAN);
    }

    public function scopeEmailUnverified($query) {
        return $query->where('ev', Status::UNVERIFIED);
    }

    public function scopeMobileUnverified($query) {
        return $query->where('sv', Status::UNVERIFIED);
    }

    public function scopeKycUnverified($query) {
        return $query->where('kv', Status::KYC_UNVERIFIED);
    }

    public function scopeKycPending($query) {
        return $query->where('kv', Status::KYC_PENDING);
    }

    public function scopeEmailVerified($query) {
        return $query->where('ev', Status::VERIFIED);
    }

    public function scopeMobileVerified($query) {
        return $query->where('sv', Status::VERIFIED);
    }


    public function deviceTokens() {
        return $this->hasMany(DeviceToken::class);
    }

    public function pushSubscriptions() {
        return $this->hasMany(PushSubscription::class);
    }

    public function socialAccounts() {
        return $this->hasMany(UserSocialAccount::class);
    }

    public function communityPosts() {
        return $this->hasMany(AppCommunityPost::class);
    }

    public function sentFriendRequests() {
        return $this->hasMany(AppFriendRequest::class, 'sender_user_id');
    }

    public function receivedFriendRequests() {
        return $this->hasMany(AppFriendRequest::class, 'receiver_user_id');
    }

    public function sentDirectMessages() {
        return $this->hasMany(AppDirectMessage::class, 'sender_user_id');
    }

    public function receivedDirectMessages() {
        return $this->hasMany(AppDirectMessage::class, 'receiver_user_id');
    }

    public function blockedUsers() {
        return $this->hasMany(AppUserBlock::class, 'user_id');
    }

    public function rewardUnlocks() {
        return $this->hasMany(AppUserRewardUnlock::class, 'user_id');
    }

    public function storePurchases() {
        return $this->hasMany(AppStorePurchase::class, 'user_id');
    }

    public function walletTransactions() {
        return $this->hasMany(AppWalletTransaction::class, 'user_id');
    }

    public function vouchers() {
        return $this->hasMany(AppUserVoucher::class, 'user_id');
    }

    public function storeProductSubmissions() {
        return $this->hasMany(StoreProductSubmission::class, 'user_id');
    }

    public function subscriptions() {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription() {
        return $this->hasOne(Subscription::class)
            ->where(function ($query) {
                // Assinatura paga ativa
                $query->where('status', 'ativa')
                      ->where('data_fim', '>=', now()->toDateString());
            })
            ->orWhere(function ($query) {
                // Trial ativo
                $query->where('user_id', $this->id)
                      ->where('is_trial', true)
                      ->where('trial_ends_at', '>=', now());
            })
            ->latest();
    }

    /**
     * Mascara username: ex "JoaoSilva" → "Jo**va"
     */
    public static function maskUsername(?string $name): string
    {
        if (!$name || mb_strlen($name) <= 3) return $name ?? '';
        if (mb_strlen($name) === 4) return mb_substr($name, 0, 1) . '**' . mb_substr($name, -1);
        return mb_substr($name, 0, 2) . '**' . mb_substr($name, -2);
    }

    /**
     * Retorna username público (mascarado se show_in_listings=false)
     */
    public function getPublicUsername(): string
    {
        if ($this->show_in_listings) {
            return $this->username ?? 'Usuário';
        }
        return static::maskUsername($this->username ?? 'Usuário');
    }

    /**
     * Verifica se usuário é premium (pago ou trial)
     */
    public function isPremium(): bool {
        return $this->subscriptions()
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('status', 'ativa')
                      ->where('data_fim', '>=', now()->toDateString());
                })->orWhere(function ($q) {
                    $q->where('is_trial', true)
                      ->where('trial_ends_at', '>=', now());
                });
            })
            ->exists();
    }

    /**
     * Verifica se usuário já teve trial
     */
    public function hasHadTrial(): bool {
        return $this->subscriptions()->where('is_trial', true)->exists();
    }

    /**
     * Verifica se está em período de trial
     */
    public function isOnTrial(): bool {
        return $this->subscriptions()
            ->where('is_trial', true)
            ->where('trial_ends_at', '>=', now())
            ->exists();
    }

    /**
     * Retorna assinatura ativa atual
     */
    public function getCurrentSubscription(): ?Subscription {
        return $this->subscriptions()
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('status', 'ativa')
                      ->where('data_fim', '>=', now()->toDateString());
                })->orWhere(function ($q) {
                    $q->where('is_trial', true)
                      ->where('trial_ends_at', '>=', now());
                });
            })
            ->with('plan')
            ->latest()
            ->first();
    }

    /**
     * Retorna status da assinatura
     */
    public function subscriptionStatus(): array {
        $subscription = $this->getCurrentSubscription();
        
        if (!$subscription) {
            return [
                'is_premium' => false,
                'status' => 'free',
                'can_trial' => !$this->hasHadTrial(),
            ];
        }

        return [
            'is_premium' => true,
            'status' => $subscription->isOnTrial() ? 'trial' : 'active',
            'is_trial' => $subscription->is_trial,
            'days_remaining' => $subscription->remaining_days,
            'plan' => $subscription->plan?->name,
            'expires_at' => $subscription->data_fim,
            'can_trial' => false,
        ];
    }

    public function isAdmin() {
        // Ajuste conforme sua lógica de admin (pode ser role, is_admin, etc)
        return $this->role === 'admin' || $this->email === env('ADMIN_EMAIL');
    }

    public function fantasyRooms() {
        return $this->hasMany(FantasyRoom::class);
    }

    public function x1Rooms() {
        return $this->hasMany(X1Room::class, 'criador_id');
    }

    public static function getUniqueReferralCode() {
        do {
            $newReferralCode = getTrx();
            $exists = self::where('referral_code', $newReferralCode)->exists();
        } while ($exists);

        return $newReferralCode;
    }

    /**
     * Check if user profile is complete (required fields filled)
     * Required: firstname, lastname, cpf, birthdate, mobile
     */
    public function isProfileComplete(): bool {
        return !empty($this->firstname) 
            && !empty($this->lastname) 
            && !empty($this->cpf) 
            && !empty($this->birthdate) 
            && !empty($this->mobile);
    }

    public function hasRealEmail(): bool
    {
        $email = strtolower(trim((string) ($this->email ?? '')));

        return $email !== '' && !str_ends_with($email, '@cadastro.local');
    }

    /**
     * Get missing profile fields
     */
    public function getMissingProfileFields(): array {
        $missing = [];
        
        if (empty($this->firstname)) $missing[] = 'Nome';
        if (empty($this->lastname)) $missing[] = 'Sobrenome';
        if (empty($this->cpf)) $missing[] = 'CPF';
        if (empty($this->birthdate)) $missing[] = 'Data de Nascimento';
        if (empty($this->mobile)) $missing[] = 'WhatsApp';
        
        return $missing;
    }

    public function hasPrizeHistory(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $hasFantasyPrize = \App\Models\FantasyTeam::query()
            ->where('user_id', $this->id)
            ->where('prize_won', '>', 0)
            ->exists();

        if ($hasFantasyPrize) {
            return true;
        }

        return \App\Models\X1Result::query()
            ->where('winner_user_id', $this->id)
            ->exists();
    }

    public function requiresFullProfileForPrizes(): bool
    {
        return $this->hasPrizeHistory();
    }

    public function isPrizeProfileComplete(): bool
    {
        return !empty($this->firstname)
            && !empty($this->lastname)
            && $this->hasRealEmail()
            && !empty($this->cpf)
            && !empty($this->birthdate)
            && !empty($this->mobile)
            && !empty($this->pix_key_type)
            && !empty($this->pix_key);
    }

    public function getPrizeProfileMissingFields(): array
    {
        $missing = [];

        if (empty($this->firstname)) $missing[] = 'Nome';
        if (empty($this->lastname)) $missing[] = 'Sobrenome';
        if (!$this->hasRealEmail()) $missing[] = 'Email';
        if (empty($this->cpf)) $missing[] = 'CPF';
        if (empty($this->birthdate)) $missing[] = 'Data de Nascimento';
        if (empty($this->mobile)) $missing[] = 'WhatsApp';
        if (empty($this->pix_key_type)) $missing[] = 'Tipo de Chave PIX';
        if (empty($this->pix_key)) $missing[] = 'Chave PIX';

        return $missing;
    }

    // ==========================================
    // AFFILIATE RELATIONSHIPS & METHODS
    // ==========================================
    
    /**
     * Conta de afiliado do usuário (se for afiliado)
     */
    public function affiliate() {
        return $this->hasOne(\App\Models\Affiliate::class);
    }

    /**
     * Afiliado que indicou este usuário
     */
    public function referredByAffiliate() {
        return $this->belongsTo(\App\Models\Affiliate::class, 'referred_by_id');
    }

    /**
     * Verificar se usuário é afiliado
     */
    public function isAffiliate(): bool {
        if ($this->relationLoaded('affiliate')) {
            $affiliate = $this->getRelation('affiliate');

            return !is_null($affiliate) && $affiliate->status === 'active';
        }

        return $this->affiliate()->where('status', 'active')->exists();
    }

    /**
     * Verificar se foi indicado por algum afiliado
     */
    public function wasReferred(): bool {
        return !is_null($this->referred_by_id);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $passwordResetUrl = route('user.password.reset', $token);
        notify($this, 'PASSWORD_RESET', [
            'link' => $passwordResetUrl
        ], ['email']);
    }

    public function claimedCompetitor(): HasOne
    {
        return $this->hasOne(Competitor::class, 'claimed_user_id');
    }

    public function profilePhotoRequests(): HasMany
    {
        return $this->hasMany(ProfilePhotoRequest::class);
    }
}


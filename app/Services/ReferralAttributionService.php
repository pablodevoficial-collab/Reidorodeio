<?php

namespace App\Services;

use App\Models\Affiliate;
use Illuminate\Support\Facades\Crypt;

class ReferralAttributionService
{
    public function createToken(Affiliate $affiliate): string
    {
        return Crypt::encryptString(json_encode([
            'affiliate_id' => (int) $affiliate->id,
            'referral_code' => (string) $affiliate->referral_code,
            'issued_at' => now()->toIso8601String(),
        ], JSON_UNESCAPED_UNICODE));
    }

    public function resolveAffiliate(
        ?string $referralToken = null,
        ?string $referralCode = null,
        ?int $referralAffiliateId = null
    ): ?Affiliate {
        if (!empty($referralToken)) {
            $affiliate = $this->affiliateFromToken($referralToken);
            if ($affiliate) {
                return $affiliate;
            }
        }

        if (!empty($referralAffiliateId)) {
            $affiliate = Affiliate::query()
                ->where('id', $referralAffiliateId)
                ->where('status', 'active')
                ->first();

            if ($affiliate) {
                return $affiliate;
            }
        }

        if (!empty($referralCode)) {
            return Affiliate::query()
                ->where('referral_code', trim((string) $referralCode))
                ->where('status', 'active')
                ->first();
        }

        return null;
    }

    public function affiliateFromToken(string $token): ?Affiliate
    {
        try {
            $payload = json_decode(Crypt::decryptString($token), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return null;
        }

        $affiliateId = (int) ($payload['affiliate_id'] ?? 0);
        $referralCode = trim((string) ($payload['referral_code'] ?? ''));

        if ($affiliateId <= 0 || $referralCode === '') {
            return null;
        }

        return Affiliate::query()
            ->where('id', $affiliateId)
            ->where('referral_code', $referralCode)
            ->where('status', 'active')
            ->first();
    }
}

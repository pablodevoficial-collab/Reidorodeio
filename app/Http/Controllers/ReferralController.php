<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Affiliate;
use Illuminate\Support\Facades\Cookie;
use App\Services\ReferralAttributionService;

/**
 * Controlador para links de referral
 * Rota: /r/{code}
 */
class ReferralController extends Controller
{
    public function __construct(
        private ReferralAttributionService $referralAttributionService
    ) {
    }

    /**
     * Handle referral link click
     * Store affiliate code in cookie for 30 days and redirect to inicial_hub
     */
    public function handleReferral(Request $request, string $code)
    {
        // Buscar afiliado pelo código
        $affiliate = Affiliate::where('referral_code', $code)
            ->where('status', 'active')
            ->first();

        if (!$affiliate) {
            return redirect('/')->with('error', 'Link de indicação inválido.');
        }

        $referralToken = $this->referralAttributionService->createToken($affiliate);

        // Salvar na sessão (dura até fechar navegador)
        $request->session()->put('referral_code', $code);
        $request->session()->put('referral_affiliate_id', $affiliate->id);
        $request->session()->put('referral_token', $referralToken);

        // Salvar em cookie (dura 30 dias) - SEMPRE, independente de aceite
        Cookie::queue('referral_code', $code, 43200); // 30 dias em minutos
        Cookie::queue('referral_affiliate_id', $affiliate->id, 43200);
        Cookie::queue('referral_token', $referralToken, 43200);

        // Log do referral
        \Log::info('🔗 Link de referral acessado', [
            'code' => $code,
            'affiliate_id' => $affiliate->id,
            'has_token' => true,
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
        ]);

        // Redirecionar SEMPRE para inicial_hub
        if (auth()->check()) {
            // Se já estiver logado, vai direto para hub
            return redirect()->route('home')->with('info', 'Você já possui uma conta. Compartilhe seu próprio link de indicação!');
        } else {
            // Se não estiver logado, vai para inicial_hub (página pública)
            return redirect('/')->with('referral_success', 'Bem-vindo! Cadastre-se para ganhar benefícios exclusivos através do link de ' . ($affiliate->user->name ?? 'indicação') . '!');
        }
    }

    /**
     * Get referral code from session or cookie
     */
    public static function getReferralCode(Request $request): ?string
    {
        // Prioridade: Sessão > Cookie
        if ($request->session()->has('referral_code')) {
            return $request->session()->get('referral_code');
        }

        if ($request->hasCookie('referral_code')) {
            return $request->cookie('referral_code');
        }

        return null;
    }

    /**
     * Clear referral code from session and cookie
     */
    public static function clearReferralCode(Request $request): void
    {
        $request->session()->forget('referral_code');
        $request->session()->forget('referral_affiliate_id');
        $request->session()->forget('referral_token');
        Cookie::queue(Cookie::forget('referral_code'));
        Cookie::queue(Cookie::forget('referral_affiliate_id'));
        Cookie::queue(Cookie::forget('referral_token'));
    }
}

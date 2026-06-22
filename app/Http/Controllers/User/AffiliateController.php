<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\User;
use App\Models\AffiliatePayment;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Controlador de Afiliados (área do usuário)
 */
class AffiliateController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard de afiliado (view principal)
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        // Verificar se é afiliado
        if (!$user->isAffiliate()) {
            return redirect('/?tab=pix')
                ->with('info', 'Sua conta de afiliado precisa ser aprovada manualmente pelo admin.');
        }

        $affiliate = $user->affiliate;
        $tierData = $affiliate->tierData();
        $nextTier = $affiliate->nextTier();
        
        // Calcular saldo disponível real (Pendente - Saques Solicitados)
        $pendingWithdrawals = AffiliatePayment::where('affiliate_id', $affiliate->id)
            ->where('status', 'pending')
            ->sum('amount');
            
        $availableBalance = max(0, $affiliate->pending_commission - $pendingWithdrawals);

        // Estatísticas
        $stats = [
            'active_referrals' => $affiliate->active_referrals,
            'total_earned' => $affiliate->total_earned,
            'pending_commission' => $affiliate->pending_commission,
            'available_balance' => $availableBalance, // Novo campo
            'approved_commission' => $affiliate->approved_commission,
            'tier' => $tierData,
            'next_tier' => $nextTier,
            'referral_code' => $affiliate->referral_code,
            'referral_link' => url('/r/' . $affiliate->referral_code),
        ];

        // Últimas comissões
        $recentCommissions = AffiliateCommission::where('affiliate_id', $affiliate->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
            
        // Últimos pagamentos/saques
        $recentPayments = AffiliatePayment::where('affiliate_id', $affiliate->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Usuários indicados (com paginação)
        $referrals = User::where('referred_by_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['username', 'created_at'], 'referrals_page');

        return view('frontend.affiliate.dashboard', compact('stats', 'recentCommissions', 'referrals', 'recentPayments'));
    }

    /**
     * Solicitar Saque
     */
    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'payment_details' => 'required|string|max:255'
        ]);

        $user = auth()->user();
        if (!$user->isAffiliate()) {
            return back()->with('error', 'Apenas afiliados podem solicitar saque.');
        }

        $affiliate = $user->affiliate;
        $amount = $request->amount;

        // Verificar saldo disponível
        $pendingWithdrawals = AffiliatePayment::where('affiliate_id', $affiliate->id)
            ->where('status', 'pending')
            ->sum('amount');
            
        $availableBalance = $affiliate->pending_commission - $pendingWithdrawals;

        if ($amount > $availableBalance) {
            return back()->with('error', 'Saldo insuficiente. Seu saldo disponível é R$ ' . number_format($availableBalance, 2, ',', '.'));
        }

        AffiliatePayment::requestWithdrawal($affiliate->id, $amount, $request->payment_details);

        return back()->with('success', 'Solicitação de saque realizada com sucesso! Aguarde a aprovação.');
    }

    /**
     * Página de ativação
     */
    public function showActivation()
    {
        $user = auth()->user();
        $affiliate = Affiliate::query()->where('user_id', $user->id)->first();

        if ($affiliate && $affiliate->status === 'active') {
            return redirect()->route('user.affiliate.dashboard');
        }

        return redirect('/?tab=pix')
            ->with('info', 'O sistema de afiliado só pode ser ativado manualmente pelo admin.');
    }

    /**
     * Ativar conta de afiliado
     */
    public function activate(Request $request)
    {
        $affiliate = Affiliate::query()->where('user_id', $request->user()->id)->first();

        if ($affiliate && $affiliate->status === 'active') {
            $this->ensureReferralCode($affiliate);

            return response()->json([
                'success' => true,
                'message' => 'A conta de afiliado já está ativa.',
                'referral_link' => url('/r/' . $affiliate->referral_code),
                'status' => 'active',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'O sistema de afiliado só pode ser ativado manualmente pelo admin.',
        ], 403);
    }

    /**
     * Gerar código único de referral
     */
    private function generateUniqueCode(): string
    {
        do {
            // Gera código alfanumérico de 8 caracteres
            $code = strtoupper(Str::random(8));
        } while (Affiliate::where('referral_code', $code)->exists());

        return $code;
    }

    private function isDuplicateAffiliateUserIdError(QueryException $e): bool
    {
        $message = (string) $e->getMessage();

        return str_contains($message, 'affiliates_user_id_unique')
            || str_contains($message, 'Duplicate entry');
    }

    private function ensureReferralCode(Affiliate $affiliate): void
    {
        if (!empty($affiliate->referral_code)) {
            return;
        }

        $affiliate->referral_code = $this->generateUniqueCode();
        $affiliate->save();
    }

    /**
     * API: Obter estatísticas (AJAX)
     */
    public function getStats()
    {
        $user = auth()->user();

        if (!$user->isAffiliate()) {
            return response()->json(['success' => false, 'message' => 'Não é afiliado']);
        }

        $affiliate = $user->affiliate;
        $tierData = $affiliate->tierData();
        $nextTier = $affiliate->nextTier();

        return response()->json([
            'success' => true,
            'stats' => [
                'active_referrals' => $affiliate->active_referrals,
                'total_earned' => number_format($affiliate->total_earned, 2, ',', '.'),
                'pending_commission' => number_format($affiliate->pending_commission, 2, ',', '.'),
                'approved_commission' => number_format($affiliate->approved_commission, 2, ',', '.'),
                'tier' => $tierData->name,
                'tier_emoji' => $tierData->emoji,
                'x1_percent' => $tierData->x1_commission_percent,
                'fantasy_percent' => $tierData->fantasy_commission_percent,
                'next_tier' => $nextTier ? [
                    'name' => $nextTier->name,
                    'min_referrals' => $nextTier->min_referrals,
                    'progress' => round(($affiliate->active_referrals / $nextTier->min_referrals) * 100, 1)
                ] : null,
                'referral_code' => $affiliate->referral_code,
                'referral_link' => url('/r/' . $affiliate->referral_code),
            ]
        ]);
    }

    /**
     * API: Obter lista de comissões (AJAX)
     */
    public function getCommissions(Request $request)
    {
        $user = auth()->user();

        if (!$user->isAffiliate()) {
            return response()->json(['success' => false]);
        }

        $status = $request->get('status', 'all'); // all, pending, approved, paid
        $limit = $request->get('limit', 20);

        $query = AffiliateCommission::where('affiliate_id', $user->affiliate->id);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $commissions = $query->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'type' => $c->source_type === 'x1' ? 'X1' : 'Fantasy',
                    'amount' => 'R$ ' . number_format($c->commission_amount, 2, ',', '.'),
                    'status' => $c->status,
                    'user' => $c->referred_user ? $c->referred_user->username : 'N/A',
                    'date' => $c->created_at->format('d/m/Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'commissions' => $commissions
        ]);
    }

    /**
     * API: Obter lista de indicados (AJAX)
     */
    public function getReferrals(Request $request)
    {
        $user = auth()->user();
        $limit = $request->get('limit', 50);

        $referrals = User::where('referred_by_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get(['id', 'username', 'created_at'])
            ->map(function ($ref) {
                return [
                    'username' => $ref->username,
                    'joined' => $ref->created_at->format('d/m/Y'),
                ];
            });

        return response()->json([
            'success' => true,
            'referrals' => $referrals,
            'total' => $user->isAffiliate() ? $user->affiliate->active_referrals : 0
        ]);
    }
}

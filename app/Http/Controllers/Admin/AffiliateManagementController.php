<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliatePayment;
use Illuminate\Http\Request;

/**
 * Controlador Admin para gerenciar afiliados
 */
class AffiliateManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Lista de afiliados
     */
    public function index(Request $request)
    {
        $query = Affiliate::with('user');

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('tier')) {
            $query->where('tier', $request->tier);
        }

        $affiliates = $query->orderBy('total_earned', 'desc')->paginate(50);

        return view('admin.affiliates.index', compact('affiliates'));
    }

    /**
     * Detalhes de um afiliado
     */
    public function show($id)
    {
        $affiliate = Affiliate::with('user')->findOrFail($id);
        
        $commissions = AffiliateCommission::where('affiliate_id', $id)
            ->with('referredUser')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $payments = AffiliatePayment::where('affiliate_id', $id)
            ->with('admin')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.affiliates.show', compact('affiliate', 'commissions', 'payments'));
    }

    /**
     * Marcar comissão como paga
     */
    public function markAsPaid(Request $request, $id)
    {
        $affiliate = Affiliate::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $payment = AffiliatePayment::createPayment(
            $affiliate->id,
            auth()->id(),
            $request->amount,
            "Método: {$request->payment_method}. " . $request->notes
        );

        return response()->json([
            'success' => true,
            'message' => 'Pagamento registrado com sucesso!',
            'new_pending_commission' => $affiliate->fresh()->pending_commission
        ]);
    }

    /**
     * Suspender/Reativar afiliado
     */
    public function toggleStatus($id)
    {
        $affiliate = Affiliate::findOrFail($id);
        
        $affiliate->status = $affiliate->status === 'active' ? 'suspended' : 'active';
        $affiliate->save();

        return response()->json([
            'success' => true,
            'message' => 'Status atualizado com sucesso!',
            'new_status' => $affiliate->status
        ]);
    }

    /**
     * Aprovar comissão manualmente
     */
    public function approveCommission($commissionId)
    {
        $commission = AffiliateCommission::findOrFail($commissionId);

        if ($commission->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Comissão já foi aprovada ou paga.'
            ]);
        }

        $commission->approve();

        return response()->json([
            'success' => true,
            'message' => 'Comissão aprovada com sucesso!'
        ]);
    }

    /**
     * Dashboard com estatísticas gerais
     */
    public function dashboard()
    {
        $stats = [
            'total_affiliates' => Affiliate::count(),
            'active_affiliates' => Affiliate::where('status', 'active')->count(),
            'total_commissions_pending' => AffiliateCommission::where('status', 'pending')->sum('commission_amount'),
            'total_commissions_approved' => AffiliateCommission::where('status', 'approved')->sum('commission_amount'),
            'total_paid_this_month' => AffiliatePayment::where('status', 'paid')->whereMonth('created_at', now()->month)->sum('amount'),
            'total_paid_all_time' => AffiliatePayment::where('status', 'paid')->sum('amount'),
            'pending_withdrawals_count' => AffiliatePayment::where('status', 'pending')->count(),
            'pending_withdrawals_amount' => AffiliatePayment::where('status', 'pending')->sum('amount'),
        ];

        $topAffiliates = Affiliate::with('user')
            ->where('status', 'active')
            ->orderBy('total_earned', 'desc')
            ->take(10)
            ->get();
            
        $pendingWithdrawals = AffiliatePayment::with(['affiliate.user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.affiliates.dashboard', compact('stats', 'topAffiliates', 'pendingWithdrawals'));
    }
    
    /**
     * Processar solicitação de saque
     */
    public function processWithdrawal(Request $request, $id)
    {
        $payment = AffiliatePayment::findOrFail($id);
        
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Esta solicitação já foi processada.');
        }
        
        $action = $request->action; // approve, reject
        
        if ($action === 'approve') {
            $payment->update([
                'status' => 'paid',
                'paid_by_admin_id' => auth()->id(),
                'notes' => $request->notes ?? 'Aprovado pelo painel'
            ]);
            
            // Debita do saldo
            $payment->affiliate->markCommissionPaid($payment->amount);
            
            return back()->with('success', 'Saque aprovado e saldo debitado!');
        } elseif ($action === 'reject') {
            $payment->update([
                'status' => 'rejected',
                'rejection_reason' => $request->notes ?? 'Motivo não informado',
                'paid_by_admin_id' => auth()->id()
            ]);
            
            // Não debita saldo (pois não foi pago)
            
            return back()->with('success', 'Saque rejeitado.');
        }
        
        return back()->with('error', 'Ação inválida.');
    }
}

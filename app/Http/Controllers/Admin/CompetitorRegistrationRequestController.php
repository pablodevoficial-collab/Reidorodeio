<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competitor;
use App\Models\CompetitorRegistrationRequest;
use App\Models\CompetitorStat;
use App\Services\ClaimedCompetitorProfileSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompetitorRegistrationRequestController extends Controller
{
    public function __construct(
        private ClaimedCompetitorProfileSyncService $claimedCompetitorProfileSyncService
    ) {
    }

    public function index(Request $request): View
    {
        $pageTitle = 'Solicitações de Competidor';
        $status = trim((string) $request->input('status', 'pending'));

        $requests = CompetitorRegistrationRequest::query()
            ->with(['user', 'competitor', 'approvedByAdmin'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending' => CompetitorRegistrationRequest::query()->where('status', 'pending')->count(),
            'approved' => CompetitorRegistrationRequest::query()->where('status', 'approved')->count(),
            'rejected' => CompetitorRegistrationRequest::query()->where('status', 'rejected')->count(),
        ];

        return view('admin.competitors.requests.index', compact('pageTitle', 'requests', 'counts', 'status'));
    }

    public function approve(CompetitorRegistrationRequest $requestModel): RedirectResponse
    {
        if ($requestModel->status === 'approved') {
            return back()->withNotify([['info', 'Essa solicitação já foi aprovada.']]);
        }

        DB::transaction(function () use ($requestModel) {
            $user = $requestModel->user()->lockForUpdate()->firstOrFail();

            $competitor = $requestModel->competitor;
            if (!$competitor) {
                $competitor = $user->claimedCompetitor()->first();
            }

            if (!$competitor) {
                $competitor = Competitor::create([
                    'nome' => trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) ?: ($user->username ?? 'Competidor'),
                    'biografia' => $requestModel->biografia,
                    'foto' => null,
                    'status' => 'ativo',
                    'nivel' => 'competidor',
                    'profile_claimed' => true,
                    'claimed_user_id' => $user->id,
                ]);

                CompetitorStat::create([
                    'competitor_id' => $competitor->id,
                ]);
            } else {
                $competitor->forceFill([
                    'biografia' => $requestModel->biografia ?: $competitor->biografia,
                    'status' => 'ativo',
                    'nivel' => $competitor->nivel ?: 'competidor',
                    'profile_claimed' => true,
                    'claimed_user_id' => $user->id,
                ])->save();
            }

            $requestModel->forceFill([
                'status' => 'approved',
                'competitor_id' => $competitor->id,
                'approved_by_admin_id' => auth('admin')->id(),
                'approved_at' => now(),
                'rejected_at' => null,
            ])->save();

            $this->claimedCompetitorProfileSyncService->syncFromCompetitor($competitor->fresh(['claimedUser']));
        });

        return back()->withNotify([['success', 'Solicitação aprovada e competidor vinculado com sucesso.']]);
    }

    public function reject(Request $request, CompetitorRegistrationRequest $requestModel): RedirectResponse
    {
        if ($requestModel->status === 'approved') {
            return back()->withNotify([['error', 'Uma solicitação já aprovada não pode ser rejeitada.']]);
        }

        $requestModel->forceFill([
            'status' => 'rejected',
            'admin_notes' => $request->input('admin_notes'),
            'approved_by_admin_id' => auth('admin')->id(),
            'rejected_at' => now(),
        ])->save();

        return back()->withNotify([['success', 'Solicitação rejeitada.']]);
    }
}

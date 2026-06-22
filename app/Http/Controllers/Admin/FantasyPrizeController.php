<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCommission;
use App\Models\FantasyTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class FantasyPrizeController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('fantasy_teams')) {
            $pageTitle = 'Pagar Bolao';
            $message = 'Tabela fantasy_teams nao existe neste ambiente.';
            return view('admin.feature_unavailable', compact('pageTitle', 'message'));
        }

        $pageTitle = 'Pagar Bolao';

        $query = FantasyTeam::query()
            ->with(['user', 'fantasyLeague'])
            ->where('prize_won', '>', 0)
            ->whereHas('fantasyLeague', function ($q) {
                $q->where('status', 'finalized');
            });

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            if ($status === 'paid') {
                $query->whereNotNull('prize_paid_at');
            } elseif ($status === 'pending') {
                $query->whereNull('prize_paid_at');
            }
        }

        if ($request->filled('q')) {
            $q = trim($request->string('q')->toString());
            $query->where(function ($builder) use ($q) {
                $builder->where('team_name', 'like', "%{$q}%")
                    ->orWhere('id', $q)
                    ->orWhereHas('user', function ($userQuery) use ($q) {
                        $userQuery->where('username', 'like', "%{$q}%")
                            ->orWhere('firstname', 'like', "%{$q}%")
                            ->orWhere('lastname', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    })
                    ->orWhereHas('fantasyLeague', function ($leagueQuery) use ($q) {
                        $leagueQuery->where('name', 'like', "%{$q}%")
                            ->orWhere('id', $q);
                    });
            });
        }

        $teams = $query
            ->orderByRaw('prize_paid_at is null desc')
            ->orderByDesc('prize_won')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $teamIds = $teams->pluck('id')->all();
        $commissionTotals = [];

        if ($teamIds) {
            $commissionTotals = AffiliateCommission::query()
                ->where('type', 'fantasy_prize')
                ->whereIn('fantasy_team_id', $teamIds)
                ->get()
                ->groupBy('fantasy_team_id')
                ->map(function ($items) {
                    return $items->sum('commission_amount');
                })
                ->toArray();
        }

        return view('admin.fantasy_leagues.payouts', compact('pageTitle', 'teams', 'commissionTotals'));
    }

    public function markPaid(Request $request, FantasyTeam $fantasyTeam)
    {
        if (!$fantasyTeam->prize_won || $fantasyTeam->prize_won <= 0) {
            return redirect()->back()->with('error', 'Este time nao possui premio para pagar.');
        }

        if ($fantasyTeam->prize_paid_at) {
            return redirect()->back()->with('warning', 'Este premio ja foi marcado como pago.');
        }

        $fantasyTeam->prize_paid_at = now();
        $fantasyTeam->save();

        return redirect()->back()->with('success', 'Premio marcado como pago com sucesso!');
    }
}

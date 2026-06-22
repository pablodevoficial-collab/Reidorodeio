<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Deposit;
use App\Lib\CurlRequest;
use App\Constants\Status;
use App\Models\UserLogin;
use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Models\Competitor;
use App\Models\Modalidade;
use App\Models\Rodeio;
use App\Models\FantasyLeague;
use App\Models\FantasyPayment;
use App\Models\FantasyTeam;
use App\Models\Subscription;
use App\Models\AppStorePurchase;
use App\Models\AppUserVoucher;
use App\Models\X1Payment;
use App\Models\X1Room;
use App\Models\CompetitorScoringLog;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Rules\FileTypeValidate;
use App\Models\AdminNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller {

    public function dashboard() {
        $pageTitle = 'Dashboard';

        $hasBotColumn = Schema::hasColumn('users', 'is_bot');
        $hasPixColumn = Schema::hasColumn('users', 'pix_key');
        $hasFantasyTables = Schema::hasTable('fantasy_leagues') && Schema::hasTable('fantasy_teams');
        $hasFantasyPaymentsTable = Schema::hasTable('fantasy_payments');
        $hasX1RoomsTable = Schema::hasTable('x1_rooms');
        $hasX1PaymentsTable = Schema::hasTable('x1_payments');
        $hasStorePurchasesTable = Schema::hasTable('app_store_purchases');
        $hasVouchersTable = Schema::hasTable('app_user_vouchers');
        $hasSubscriptionsTable = Schema::hasTable('subscriptions');
        $hasScoringLogsTable = Schema::hasTable('competitor_scoring_logs');

        $totalUsers = User::count();
        $realUsers = $hasBotColumn ? User::real()->count() : $totalUsers;
        $botUsers = $hasBotColumn ? User::bots()->count() : 0;
        $newUsers7Days = User::query()->where('created_at', '>=', now()->subDays(7))->count();
        $verifiedUsers = User::active()->count();
        $emailUnverifiedUsers = User::emailUnverified()->count();
        $mobileUnverifiedUsers = User::mobileUnverified()->count();
        $pendingKycVerifications = User::kycPending()->count();
        $usersWithPixReady = $hasPixColumn
            ? User::query()->whereNotNull('pix_key')->where('pix_key', '!=', '')->count()
            : 0;
        $pendingTicket = SupportTicket::where('status', Status::TICKET_OPEN)->count();
        $unreadAdminNotifications = AdminNotification::where('is_read', Status::NO)->count();

        $subscriptionsCanCheckActive = $hasSubscriptionsTable
            && Schema::hasColumn('subscriptions', 'status')
            && Schema::hasColumn('subscriptions', 'data_fim')
            && Schema::hasColumn('subscriptions', 'is_trial')
            && Schema::hasColumn('subscriptions', 'trial_ends_at');

        $premiumActiveUsers = $subscriptionsCanCheckActive
            ? Subscription::query()->active()->distinct('user_id')->count('user_id')
            : 0;
        $totalSubscriptions = $hasSubscriptionsTable ? Subscription::count() : 0;

        $totalCompetitors = Competitor::count();
        $totalRodeios = Rodeio::count();
        $activeRodeios = Schema::hasColumn('rodeios', 'status')
            ? Rodeio::query()->where('status', 'ativo')->count()
            : 0;
        $totalModalidades = Modalidade::count();

        $x1OpenRooms = 0;
        $x1LiveRooms = 0;
        $x1ClosedRooms = 0;
        $x1PendingPayments = 0;
        $x1ApprovedVolume = 0.0;
        $x1HouseRevenue = 0.0;
        $totalX1Rooms = 0;

        if ($hasX1RoomsTable) {
            $x1RoomBase = X1Room::query();
            $totalX1Rooms = (clone $x1RoomBase)->count();
            $x1OpenRooms = (clone $x1RoomBase)->where('status', 'open')->count();
            $x1LiveRooms = (clone $x1RoomBase)->where('status', 'in_progress')->count();
            $x1ClosedRooms = (clone $x1RoomBase)->whereIn('status', ['closed', 'cancelled'])->count();
        }

        if ($hasX1PaymentsTable) {
            $x1PendingPayments = X1Payment::query()->where('status', 'pending')->count();
            $x1ApprovedVolume = (float) X1Payment::query()->where('status', 'approved')->sum('amount');
            if (Schema::hasColumn('x1_payments', 'fee_percent')) {
                $x1HouseRevenue = (float) DB::table('x1_payments')
                    ->where('status', 'approved')
                    ->selectRaw('COALESCE(SUM(amount * (fee_percent / 100)), 0) as total')
                    ->value('total');
            }
        }

        $fantasyLeaguesTotal = 0;
        $fantasyActiveLeagues = 0;
        $fantasyOpenLeagues = 0;
        $fantasyTeamsTotal = 0;
        $fantasyEntryVolume = 0.0;
        $fantasyHouseRevenue = 0.0;
        $fantasyPrizePool = 0.0;
        $fantasyPendingPayments = 0;

        if ($hasFantasyTables) {
            $leagueColumns = ['id'];
            foreach (['price', 'house_cut_percent', 'is_active', 'total_prize'] as $column) {
                if (Schema::hasColumn('fantasy_leagues', $column)) {
                    $leagueColumns[] = $column;
                }
            }
            if (Schema::hasColumn('fantasy_leagues', 'registration_deadline')) {
                $leagueColumns[] = 'registration_deadline';
            }
            if (Schema::hasColumn('fantasy_leagues', 'allow_late_registration')) {
                $leagueColumns[] = 'allow_late_registration';
            }

            $fantasyLeagueRows = FantasyLeague::query()
                ->withCount('teams')
                ->get($leagueColumns);

            $fantasyLeaguesTotal = $fantasyLeagueRows->count();
            $fantasyActiveLeagues = Schema::hasColumn('fantasy_leagues', 'is_active')
                ? $fantasyLeagueRows->where('is_active', true)->count()
                : 0;
            $fantasyOpenLeagues = $fantasyLeagueRows->filter(function (FantasyLeague $league) {
                $deadline = $league->registration_deadline ?? null;
                return (bool) $league->is_active
                    && ((bool) ($league->allow_late_registration ?? false) || !$deadline || $deadline->isFuture());
            })->count();
            $fantasyTeamsTotal = (int) $fantasyLeagueRows->sum('teams_count');
            $fantasyEntryVolume = (float) $fantasyLeagueRows->sum(function (FantasyLeague $league) {
                return (float) ($league->price ?? 0) * (int) ($league->teams_count ?? 0);
            });
            $fantasyHouseRevenue = (float) $fantasyLeagueRows->sum(function (FantasyLeague $league) {
                return (float) ($league->price ?? 0)
                    * (int) ($league->teams_count ?? 0)
                    * ((float) ($league->house_cut_percent ?? 0) / 100);
            });
            $fantasyPrizePool = (float) $fantasyLeagueRows->sum(function (FantasyLeague $league) {
                return (float) ($league->total_prize ?? 0);
            });
        }

        if ($hasFantasyPaymentsTable) {
            $fantasyPendingPayments = FantasyPayment::query()->where('status', 'pending')->count();
        }

        $activeVouchers = 0;
        $storePendingPurchases = 0;
        $storeApprovedPurchases = 0;
        $storeApprovedRevenue = 0.0;

        if ($hasVouchersTable) {
            $activeVouchers = AppUserVoucher::query()
                ->where('status', 'active')
                ->where('remaining_uses', '>', 0)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->count();
        }

        if ($hasStorePurchasesTable) {
            $storePendingPurchases = AppStorePurchase::query()->where('status', 'pending')->count();
            $storeApprovedPurchases = AppStorePurchase::query()->where('status', 'approved')->count();
            $storeApprovedRevenue = (float) AppStorePurchase::query()->where('status', 'approved')->sum('amount');
        }

        $houseRevenue = 0.0;
        if (
            Schema::hasTable('transactions')
            && Schema::hasColumn('transactions', 'charge')
            && Schema::hasColumn('transactions', 'remark')
        ) {
            $houseRevenue = (float) Transaction::query()
                ->where('charge', '>', 0)
                ->where(function ($query) {
                    $query->where('remark', 'like', 'fantasy%')
                        ->orWhere('remark', 'like', 'x1%');
                })
                ->sum('charge');
        }

        $depositApprovedAmount = Schema::hasTable('deposits') && Schema::hasColumn('deposits', 'amount')
            ? (float) Deposit::successful()->sum('amount')
            : 0.0;
        $depositPendingCount = Schema::hasTable('deposits')
            ? Deposit::pending()->count()
            : 0;
        $withdrawApprovedAmount = Schema::hasTable('withdrawals') && Schema::hasColumn('withdrawals', 'amount')
            ? (float) Withdrawal::approved()->sum('amount')
            : 0.0;
        $withdrawPendingCount = Schema::hasTable('withdrawals')
            ? Withdrawal::pending()->count()
            : 0;

        $systemNow = [
            $this->dashboardMetric('Usuários novos 7 dias', $newUsers7Days, 'number', 'Entrada recente no frontend', 'blue'),
            $this->dashboardMetric('Premium ativo', $premiumActiveUsers, 'number', 'Usuários com acesso premium agora', 'gold'),
            $this->dashboardMetric('Salas X1 abertas', $x1OpenRooms + $x1LiveRooms, 'number', 'Abertas e em andamento', 'orange'),
            $this->dashboardMetric('Bolões ativos', $fantasyOpenLeagues, 'number', 'Inscrição liberada no frontend', 'violet'),
            $this->dashboardMetric('Vouchers ativos', $activeVouchers, 'number', 'Tickets ainda utilizáveis', 'green'),
            $this->dashboardMetric('Checkouts pendentes', $x1PendingPayments + $fantasyPendingPayments + $storePendingPurchases, 'number', 'PIX e compras aguardando confirmação', 'red'),
            $this->dashboardMetric('Tickets pendentes', $pendingTicket, 'number', 'Suporte aguardando retorno', 'slate'),
            $this->dashboardMetric('KYC pendente', $pendingKycVerifications, 'number', 'Perfis aguardando análise', 'cyan'),
        ];

        $wholePeriodSections = [
            [
                'title' => 'Base do frontend',
                'subtitle' => 'Tamanho atual da operação e qualidade da base.',
                'items' => [
                    $this->dashboardMetric('Usuários totais', $totalUsers, 'number', 'Todas as contas cadastradas', 'blue'),
                    $this->dashboardMetric('Usuários reais', $realUsers, 'number', 'Contas humanas sem bots', 'green'),
                    $this->dashboardMetric('Bots', $botUsers, 'number', 'Perfis automatizados na base', 'slate'),
                    $this->dashboardMetric('Premium ativo', $premiumActiveUsers, 'number', 'Usuários com assinatura/trial vigente', 'gold'),
                    $this->dashboardMetric('Competidores', $totalCompetitors, 'number', 'Base pública do frontend', 'orange'),
                    $this->dashboardMetric('Rodeios', $totalRodeios, 'number', 'Eventos cadastrados', 'violet'),
                    $this->dashboardMetric('Rodeios ativos', $activeRodeios, 'number', 'Eventos com status ativo', 'lime'),
                    $this->dashboardMetric('Modalidades', $totalModalidades, 'number', 'Modalidades disponíveis', 'cyan'),
                ],
            ],
            [
                'title' => 'Movimento do frontend',
                'subtitle' => 'Tudo que o usuário já movimentou nas áreas principais.',
                'items' => [
                    $this->dashboardMetric('Salas X1 criadas', $totalX1Rooms, 'number', 'Histórico total de salas', 'orange'),
                    $this->dashboardMetric('Volume X1 aprovado', $x1ApprovedVolume, 'currency', 'Somatório dos pagamentos aprovados', 'orange'),
                    $this->dashboardMetric('Bolões cadastrados', $fantasyLeaguesTotal, 'number', 'Ligas fantasy no sistema', 'violet'),
                    $this->dashboardMetric('Equipes no bolão', $fantasyTeamsTotal, 'number', 'Entradas confirmadas nas ligas', 'violet'),
                    $this->dashboardMetric('Entradas no bolão', $fantasyEntryVolume, 'currency', 'Preço das ligas multiplicado pelas equipes', 'violet'),
                    $this->dashboardMetric('Premiação prevista', $fantasyPrizePool, 'currency', 'Pool total prevista das ligas', 'green'),
                    $this->dashboardMetric('Compras na loja', $storeApprovedPurchases, 'number', 'Pedidos aprovados', 'cyan'),
                    $this->dashboardMetric('Receita da loja', $storeApprovedRevenue, 'currency', 'Total aprovado na loja do app', 'cyan'),
                ],
            ],
            [
                'title' => 'Financeiro consolidado',
                'subtitle' => 'Leitura rápida do dinheiro que entrou, saiu e ficou para a casa.',
                'items' => [
                    $this->dashboardMetric('Depósitos aprovados', $depositApprovedAmount, 'currency', 'Volume total aprovado', 'green'),
                    $this->dashboardMetric('Depósitos pendentes', $depositPendingCount, 'number', 'Aguardando conclusão', 'slate'),
                    $this->dashboardMetric('Saques aprovados', $withdrawApprovedAmount, 'currency', 'Volume total processado', 'red'),
                    $this->dashboardMetric('Saques pendentes', $withdrawPendingCount, 'number', 'Fila atual de saída', 'red'),
                    $this->dashboardMetric('Receita casa X1', $x1HouseRevenue, 'currency', 'Taxa acumulada sobre pagamentos X1', 'orange'),
                    $this->dashboardMetric('Receita casa bolão', $fantasyHouseRevenue, 'currency', 'Percentual acumulado nas ligas', 'violet'),
                    $this->dashboardMetric('Receita casa total', $houseRevenue, 'currency', 'Charges somadas em transações X1 + bolão', 'gold'),
                    $this->dashboardMetric('Usuários com PIX', $usersWithPixReady, 'number', 'Perfis prontos para receber', 'cyan'),
                ],
            ],
        ];

        $adminPulse = [
            $this->dashboardMetric('Notificações admin', $unreadAdminNotifications, 'number', $unreadAdminNotifications > 0 ? 'Itens não lidos' : 'Sem pendências agora', 'gold'),
            $this->dashboardMetric('Usuários verificados', $verifiedUsers, 'number', 'Email e celular validados', 'green'),
            $this->dashboardMetric('Email pendente', $emailUnverifiedUsers, 'number', 'Cadastro sem verificação de email', 'red'),
            $this->dashboardMetric('Celular pendente', $mobileUnverifiedUsers, 'number', 'Cadastro sem verificação de celular', 'red'),
            $this->dashboardMetric('Assinaturas totais', $totalSubscriptions, 'number', 'Histórico bruto de assinaturas', 'slate'),
        ];

        $currentRodeioSummary = ($currentRodeio = $this->resolveDashboardCurrentRodeio())
            ? $this->buildDashboardCurrentRodeioSummary($currentRodeio)
            : null;

        return view('admin.dashboard', compact(
            'pageTitle',
            'systemNow',
            'wholePeriodSections',
            'adminPulse',
            'currentRodeioSummary'
        ));
    }

    private function dashboardMetric(string $label, mixed $value, string $format = 'number', ?string $hint = null, string $tone = 'slate'): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'format' => $format,
            'hint' => $hint,
            'tone' => $tone,
        ];
    }

    private function resolveDashboardCurrentRodeio(): ?Rodeio
    {
        $query = Rodeio::query()->with('modalidadeAtual');

        if (Schema::hasColumn('rodeios', 'status_transmissao')) {
            $activeStatuses = [
                'ao_vivo',
                'pausado',
                'programado',
                'classificatoria',
                'em_apuracao',
                'inicio_finais',
                'divisao_finalizada',
            ];

            $liveOrScheduled = (clone $query)
                ->whereIn('status_transmissao', $activeStatuses)
                ->orderByRaw("CASE WHEN status_transmissao = 'ao_vivo' THEN 0 ELSE 1 END")
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();

            if ($liveOrScheduled) {
                return $liveOrScheduled;
            }
        }

        if (Schema::hasColumn('rodeios', 'status')) {
            $active = (clone $query)
                ->where('status', 'ativo')
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();

            if ($active) {
                return $active;
            }
        }

        return $query
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }

    private function buildDashboardCurrentRodeioSummary(Rodeio $rodeio): array
    {
        $modalidadeIds = Modalidade::query()
            ->where('rodeio_id', $rodeio->id)
            ->pluck('id');

        $modalidadesCount = $modalidadeIds->count();

        $competitorsCount = 0;
        if ($modalidadeIds->isNotEmpty() && Schema::hasTable('competitor_modalidade')) {
            $competitorsCount = (int) DB::table('competitor_modalidade')
                ->whereIn('modalidade_id', $modalidadeIds)
                ->distinct('competitor_id')
                ->count('competitor_id');
        }

        $scoreLogsCount = 0;
        $lastScoreAt = null;
        if (Schema::hasTable('competitor_scoring_logs')) {
            $scoreLogsBase = CompetitorScoringLog::query()->where('rodeio_id', $rodeio->id);
            $scoreLogsCount = (int) (clone $scoreLogsBase)->count();
            $lastScoreAt = (clone $scoreLogsBase)->max('scored_at');
        }

        $x1TotalRooms = 0;
        $x1OpenRooms = 0;
        $x1LiveRooms = 0;
        $x1ClosedRooms = 0;
        $x1ApprovedVolume = 0.0;
        $x1HouseRevenue = 0.0;

        if (Schema::hasTable('x1_rooms') && Schema::hasColumn('x1_rooms', 'rodeio_id')) {
            $x1RoomsBase = X1Room::query()->where('rodeio_id', $rodeio->id);
            $x1TotalRooms = (int) (clone $x1RoomsBase)->count();
            $x1OpenRooms = (int) (clone $x1RoomsBase)->where('status', 'open')->count();
            $x1LiveRooms = (int) (clone $x1RoomsBase)->where('status', 'in_progress')->count();
            $x1ClosedRooms = (int) (clone $x1RoomsBase)->whereIn('status', ['closed', 'cancelled'])->count();
        }

        if (
            Schema::hasTable('x1_payments')
            && Schema::hasTable('x1_rooms')
            && Schema::hasColumn('x1_rooms', 'rodeio_id')
        ) {
            $x1ApprovedVolume = (float) DB::table('x1_payments as p')
                ->join('x1_rooms as r', 'r.id', '=', 'p.x1_room_id')
                ->where('r.rodeio_id', $rodeio->id)
                ->where('p.status', 'approved')
                ->sum('p.amount');

            if (Schema::hasColumn('x1_payments', 'fee_percent')) {
                $x1HouseRevenue = (float) DB::table('x1_payments as p')
                    ->join('x1_rooms as r', 'r.id', '=', 'p.x1_room_id')
                    ->where('r.rodeio_id', $rodeio->id)
                    ->where('p.status', 'approved')
                    ->selectRaw('COALESCE(SUM(p.amount * (p.fee_percent / 100)), 0) as total')
                    ->value('total');
            }
        }

        $fantasyLeaguesTotal = 0;
        $fantasyActiveLeagues = 0;
        $fantasyTeamsTotal = 0;
        $fantasyEntryVolume = 0.0;
        $fantasyHouseRevenue = 0.0;
        $fantasyPrizePool = 0.0;

        if (Schema::hasTable('fantasy_leagues') && Schema::hasColumn('fantasy_leagues', 'rodeio_id')) {
            $leagueColumns = ['id'];
            foreach (['price', 'house_cut_percent', 'is_active', 'total_prize'] as $column) {
                if (Schema::hasColumn('fantasy_leagues', $column)) {
                    $leagueColumns[] = $column;
                }
            }

            $fantasyLeagueRows = FantasyLeague::query()
                ->where('rodeio_id', $rodeio->id)
                ->withCount('teams')
                ->get($leagueColumns);

            $fantasyLeaguesTotal = $fantasyLeagueRows->count();
            $fantasyActiveLeagues = Schema::hasColumn('fantasy_leagues', 'is_active')
                ? $fantasyLeagueRows->where('is_active', true)->count()
                : 0;
            $fantasyTeamsTotal = (int) $fantasyLeagueRows->sum('teams_count');
            $fantasyEntryVolume = (float) $fantasyLeagueRows->sum(function (FantasyLeague $league) {
                return (float) ($league->price ?? 0) * (int) ($league->teams_count ?? 0);
            });
            $fantasyHouseRevenue = (float) $fantasyLeagueRows->sum(function (FantasyLeague $league) {
                return (float) ($league->price ?? 0)
                    * (int) ($league->teams_count ?? 0)
                    * ((float) ($league->house_cut_percent ?? 0) / 100);
            });
            $fantasyPrizePool = (float) $fantasyLeagueRows->sum(function (FantasyLeague $league) {
                return (float) ($league->total_prize ?? 0);
            });
        }

        return [
            'name' => (string) ($rodeio->name ?? 'Rodeio atual'),
            'city' => (string) data_get($rodeio->info, 'cidade', ''),
            'status' => (string) ($rodeio->status_transmissao ?? $rodeio->status ?? 'programado'),
            'status_label' => $this->formatDashboardStatusLabel((string) ($rodeio->status_transmissao ?? $rodeio->status ?? 'programado')),
            'modalidade_atual' => optional($rodeio->modalidadeAtual)->nome,
            'divisao_atual' => $rodeio->divisao_atual,
            'start' => $rodeio->start,
            'end' => $rodeio->end,
            'start_label' => $rodeio->start ? Carbon::parse($rodeio->start)->format('d/m/Y') : null,
            'end_label' => $rodeio->end ? Carbon::parse($rodeio->end)->format('d/m/Y') : null,
            'updated_at' => $rodeio->updated_at,
            'updated_human' => $rodeio->updated_at?->diffForHumans(),
            'pulse' => [
                $this->dashboardMetric('Modalidades', $modalidadesCount, 'number', 'Modalidades vinculadas ao rodeio', 'blue'),
                $this->dashboardMetric('Competidores', $competitorsCount, 'number', 'Base ligada a este evento', 'orange'),
                $this->dashboardMetric('Ações de pontuação', $scoreLogsCount, 'number', 'Logs técnicos gerados neste rodeio', 'violet'),
                $this->dashboardMetric(
                    'Última atualização',
                    $lastScoreAt ? Carbon::parse($lastScoreAt)->diffForHumans() : 'Sem pontuação',
                    'text',
                    'Último log técnico lançado',
                    'slate'
                ),
            ],
            'x1' => [
                $this->dashboardMetric('Salas abertas', $x1OpenRooms, 'number', 'Aguardando adversário', 'orange'),
                $this->dashboardMetric('Em duelo', $x1LiveRooms, 'number', 'Salas já preenchidas', 'orange'),
                $this->dashboardMetric('Encerradas', $x1ClosedRooms, 'number', 'Concluídas ou canceladas', 'slate'),
                $this->dashboardMetric('Volume aprovado', $x1ApprovedVolume, 'currency', 'Pagamentos aprovados dentro do rodeio', 'green'),
                $this->dashboardMetric('Receita estimada', $x1HouseRevenue, 'currency', 'Taxa da casa gerada no X1 deste evento', 'gold'),
                $this->dashboardMetric('Salas totais', $x1TotalRooms, 'number', 'Histórico completo do X1 neste rodeio', 'blue'),
            ],
            'fantasy' => [
                $this->dashboardMetric('Bolões', $fantasyLeaguesTotal, 'number', 'Ligas ligadas a este rodeio', 'violet'),
                $this->dashboardMetric('Bolões ativos', $fantasyActiveLeagues, 'number', 'Ligas ainda ativas no frontend', 'violet'),
                $this->dashboardMetric('Equipes', $fantasyTeamsTotal, 'number', 'Entradas confirmadas neste rodeio', 'blue'),
                $this->dashboardMetric('Entradas', $fantasyEntryVolume, 'currency', 'Volume bruto do bolão no evento', 'green'),
                $this->dashboardMetric('Casa estimada', $fantasyHouseRevenue, 'currency', 'Percentual da casa nas ligas do evento', 'gold'),
                $this->dashboardMetric('Premiação prevista', $fantasyPrizePool, 'currency', 'Pool somada das ligas deste rodeio', 'cyan'),
            ],
        ];
    }

    private function formatDashboardStatusLabel(string $status): string
    {
        return match ($status) {
            'ao_vivo' => 'Ao vivo',
            'pausado' => 'Pausado',
            'programado' => 'Programado',
            'classificatoria' => 'Classificatória',
            'em_apuracao' => 'Em apuração',
            'inicio_finais' => 'Início das finais',
            'divisao_finalizada' => 'Divisão finalizada',
            'finalizado' => 'Finalizado',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    public function depositAndWithdrawReport(Request $request) {

        $diffInDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));

        $groupBy = $diffInDays > 30 ? 'months' : 'days';
        $format  = $diffInDays > 30 ? '%M-%Y' : '%d-%M-%Y';

        if ($groupBy == 'days') {
            $dates = $this->getAllDates($request->start_date, $request->end_date);
        } else {
            $dates = $this->getAllMonths($request->start_date, $request->end_date);
        }

        $deposits = Deposit::successful()
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $withdrawals = Withdrawal::approved()
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $data = [];

        foreach ($dates as $date) {
            $data[] = [
                'created_on'  => $date,
                'deposits'    => getAmount($deposits->where('created_on', $date)->first()?->amount ?? 0),
                'withdrawals' => getAmount($withdrawals->where('created_on', $date)->first()?->amount ?? 0),
            ];
        }

        $data = collect($data);

        // Monthly Deposit & Withdraw Report Graph
        $report['created_on'] = $data->pluck('created_on');
        $report['data']       = [
            [
                'name' => 'Deposited',
                'data' => $data->pluck('deposits'),
            ],
            [
                'name' => 'Withdrawn',
                'data' => $data->pluck('withdrawals'),
            ],
        ];

        return response()->json($report);
    }

    public function transactionReport(Request $request) {

        $diffInDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));

        $groupBy = $diffInDays > 30 ? 'months' : 'days';
        $format  = $diffInDays > 30 ? '%M-%Y' : '%d-%M-%Y';

        if ($groupBy == 'days') {
            $dates = $this->getAllDates($request->start_date, $request->end_date);
        } else {
            $dates = $this->getAllMonths($request->start_date, $request->end_date);
        }

        $plusTransactions = Transaction::where('trx_type', '+')
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $minusTransactions = Transaction::where('trx_type', '-')
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $data = [];

        foreach ($dates as $date) {
            $data[] = [
                'created_on' => $date,
                'credits'    => getAmount($plusTransactions->where('created_on', $date)->first()?->amount ?? 0),
                'debits'     => getAmount($minusTransactions->where('created_on', $date)->first()?->amount ?? 0),
            ];
        }

        $data = collect($data);

        // Monthly Deposit & Withdraw Report Graph
        $report['created_on'] = $data->pluck('created_on');
        $report['data']       = [
            [
                'name' => 'Plus Transactions',
                'data' => $data->pluck('credits'),
            ],
            [
                'name' => 'Minus Transactions',
                'data' => $data->pluck('debits'),
            ],
        ];

        return response()->json($report);
    }

    private function getAllDates($startDate, $endDate) {
        $dates       = [];
        $currentDate = new \DateTime($startDate);
        $endDate     = new \DateTime($endDate);

        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('d-F-Y');
            $currentDate->modify('+1 day');
        }

        return $dates;
    }

    private function getAllMonths($startDate, $endDate) {
        if ($endDate > now()) {
            $endDate = now()->format('Y-m-d');
        }

        $startDate = new \DateTime($startDate);
        $endDate   = new \DateTime($endDate);

        $months = [];

        while ($startDate <= $endDate) {
            $months[] = $startDate->format('F-Y');
            $startDate->modify('+1 month');
        }

        return $months;
    }

    public function profile() {
        $pageTitle = 'Profile';
        $admin     = auth('admin')->user();
        return view('admin.profile', compact('pageTitle', 'admin'));
    }

    public function profileUpdate(Request $request) {
        $request->validate([
            'name'  => 'required',
            'email' => 'required|email',
            'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);
        $user = auth('admin')->user();

        if ($request->hasFile('image')) {
            try {
                $old         = $user->image;
                $user->image = fileUploader($request->image, getFilePath('adminProfile'), getFileSize('adminProfile'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->save();
        $notify[] = ['success', 'Profile updated successfully'];
        return to_route('admin.profile')->withNotify($notify);
    }

    public function password() {
        $pageTitle = 'Password Setting';
        $admin     = auth('admin')->user();
        return view('admin.password', compact('pageTitle', 'admin'));
    }

    public function passwordUpdate(Request $request) {
        $request->validate([
            'old_password' => 'required',
            'password'     => 'required|min:5|confirmed',
        ]);

        $user = auth('admin')->user();
        if (!Hash::check($request->old_password, $user->password)) {
            $notify[] = ['error', 'Password doesn\'t match!!'];
            return back()->withNotify($notify);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        $notify[] = ['success', 'Password changed successfully.'];
        return to_route('admin.password')->withNotify($notify);
    }

    public function notifications() {
        $notifications   = AdminNotification::orderBy('id', 'desc')->with('user')->paginate(getPaginate());
        $hasUnread       = AdminNotification::where('is_read', Status::NO)->exists();
        $hasNotification = AdminNotification::exists();
        $pageTitle       = 'Notifications';
        return view('admin.notifications', compact('pageTitle', 'notifications', 'hasUnread', 'hasNotification'));
    }

    public function notificationRead($id) {
        $notification          = AdminNotification::findOrFail($id);
        $notification->is_read = Status::YES;
        $notification->save();
        $url = $notification->click_url;
        if ($url == '#') {
            $url = url()->previous();
        }
        return redirect($url);
    }

    public function requestReport() {
        // Local, brand-neutral placeholder: no external calls
        $pageTitle     = 'Report & Requests';
        $reports       = collect();
        $emptyMessage  = 'No items yet.';
        return view('admin.reports', compact('reports', 'pageTitle', 'emptyMessage'));
    }

    public function reportSubmit(Request $request) {
        $request->validate([
            'type'    => 'required|in:bug,feature',
            'message' => 'required',
        ]);

        // Local stub: log the request so the team can review later
        Log::info('Admin report/request', [
            'type'    => $request->type,
            'message' => $request->message,
            'admin'   => optional(auth('admin')->user())->only(['id','username','email']),
        ]);

        $notify[] = ['success', 'Thanks! Your message was recorded.'];
        return back()->withNotify($notify);
    }

    public function readAllNotification() {
        AdminNotification::where('is_read', Status::NO)->update([
            'is_read' => Status::YES,
        ]);
        $notify[] = ['success', 'Notifications read successfully'];
        return back()->withNotify($notify);
    }

    public function deleteAllNotification() {
        AdminNotification::truncate();
        $notify[] = ['success', 'Notifications deleted successfully'];
        return back()->withNotify($notify);
    }

    public function deleteSingleNotification($id) {
        AdminNotification::where('id', $id)->delete();
        $notify[] = ['success', 'Notification deleted successfully'];
        return back()->withNotify($notify);
    }

    public function downloadAttachment($fileHash) {
        $filePath  = decrypt($fileHash);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $title     = slug(gs('site_name')) . '- attachments.' . $extension;
        try {
            $mimetype = mime_content_type($filePath);
        } catch (\Exception $e) {
            $notify[] = ['error', 'File does not exists'];
            return back()->withNotify($notify);
        }
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }

}

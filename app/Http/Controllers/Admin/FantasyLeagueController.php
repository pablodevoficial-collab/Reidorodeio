<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FantasyLeague;
use App\Models\Rodeio;
use App\Models\Modalidade;
use App\Models\Competitor;
use App\Models\Sponsor;
use App\Services\FantasyLeagueOpeningReminderService;
use App\Services\FinalizeFantasyLeagueService;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FantasyLeagueController extends Controller
{
    private function fantasyLeagueHasColumn(string $column): bool
    {
        static $cache = [];

        if (!array_key_exists($column, $cache)) {
            $cache[$column] = Schema::hasColumn('fantasy_leagues', $column);
        }

        return $cache[$column];
    }

    private function buildFantasyLeaguePayload(array $validated, array $computed): array
    {
        $payload = [
            'name' => $validated['name'],
            'category' => $validated['category'],
            'price' => (float) ($computed['price'] ?? 0),
            'is_premium' => (bool) ($computed['is_premium'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];

        $optional = [
            'image' => null,
            'house_cut_percent' => (float) ($computed['house_cut_percent'] ?? 0),
            'reward_mode' => $computed['reward_mode'] ?? null,
            'manual_prize_pool' => $computed['manual_prize_pool'] ?? null,
            'prize_type' => $computed['prize_type'] ?? 'money',
            'prize_description' => $computed['prize_description'] ?? null,
            'prize_items' => $computed['prize_items'] ?? null,
            'prize_distribution' => $computed['prize_distribution'] ?? null,
            'max_users' => $validated['max_users'] ?? null,
            'paid_positions_override' => $computed['paid_positions_override'] ?? null,
            'season_id' => $validated['season_id'] ?? null,
            'rodeio_id' => isset($validated['rodeio_id']) ? (int) $validated['rodeio_id'] : null,
            'modalidade_id' => isset($validated['modalidade_id']) ? (int) $validated['modalidade_id'] : null,
            'organizer_sponsor_id' => isset($validated['organizer_sponsor_id']) ? (int) $validated['organizer_sponsor_id'] : null,
            'divisao' => (string) ($validated['divisao'] ?? ''),
            'closes_at' => $validated['closes_at'] ?? null,
            'registration_deadline' => $validated['registration_deadline'] ?? null,
            'allow_late_registration' => (bool) ($validated['allow_late_registration'] ?? false),
            'total_prize' => (float) ($computed['total_prize'] ?? 0),
        ];

        foreach ($optional as $column => $value) {
            if ($this->fantasyLeagueHasColumn($column)) {
                $payload[$column] = $value;
            }
        }

        return $payload;
    }

    private function normalizeDivisoes(Modalidade $modalidade): array
    {
        $raw = $modalidade->divisoes_nomes ?? $modalidade->divisoes ?? [];

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : preg_split('/[\r\n,;]+/', $raw);
        }

        if (!is_array($raw)) {
            return [];
        }

        return array_values(array_filter(array_map(static function ($value) {
            return trim((string) $value);
        }, $raw)));
    }

    private function validateLeagueContext(array $validated): array
    {
        $rodeioId = (int) ($validated['rodeio_id'] ?? 0);
        $modalidadeId = (int) ($validated['modalidade_id'] ?? 0);

        $modalidade = Modalidade::query()
            ->select(['id', 'rodeio_id', 'nome', 'tem_divisoes', 'divisoes'])
            ->find($modalidadeId);

        if (!$modalidade || (int) $modalidade->rodeio_id !== $rodeioId) {
            throw ValidationException::withMessages([
                'modalidade_id' => 'Selecione uma modalidade valida para o rodeio escolhido.',
            ]);
        }

        $divisao = trim((string) ($validated['divisao'] ?? ''));
        $divisoes = $this->normalizeDivisoes($modalidade);
        $temDivisoes = (bool) $modalidade->tem_divisoes && !empty($divisoes);

        if ($temDivisoes) {
            if ($divisao === '' || !in_array($divisao, $divisoes, true)) {
                throw ValidationException::withMessages([
                    'divisao' => 'Selecione uma divisao valida para a modalidade escolhida.',
                ]);
            }
        } else {
            $validated['divisao'] = '';
        }

        return $validated;
    }

    private function storeLeagueImage(UploadedFile $file): string
    {
        $relativeDir = 'assets/images/fantasy_leagues';
        $targetDir = public_path($relativeDir);
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        if ($extension === '') {
            $extension = 'png';
        }
        $fileName = 'league_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $file->move($targetDir, $fileName);

        return $relativeDir . '/' . $fileName;
    }

    private function deleteLeagueImage(?string $path): void
    {
        $p = trim((string) ($path ?? ''));
        if ($p === '' || filter_var($p, FILTER_VALIDATE_URL)) {
            return;
        }

        $p = str_replace('\\', '/', $p);
        $p = ltrim($p, '/');

        $candidates = [
            public_path($p),
            public_path('storage/' . $p),
            storage_path('app/public/' . preg_replace('#^storage/#i', '', $p)),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && file_exists($candidate) && is_file($candidate)) {
                @unlink($candidate);
            }
        }
    }

    /**
     * Clear all fantasy league API cache keys so the frontend
     * immediately reflects admin changes.
     */
    private function bustFantasyLeagueCache(): void
    {
        // Version bump evita cache stale sem depender de host/filtros exatos.
        if (!Cache::has('fantasy_leagues_cache_version')) {
            Cache::forever('fantasy_leagues_cache_version', 1);
        }
        Cache::increment('fantasy_leagues_cache_version');
    }

    private function getPaidPositionsForAdminPreview(int $totalPlayers, ?int $override = null): int
    {
        if ($totalPlayers <= 0) {
            return $override !== null && $override > 0 ? $override : 0;
        }

        if ($override !== null && $override > 0) {
            return min($override, $totalPlayers);
        }

        return max(1, (int) floor($totalPlayers * 10 / 100));
    }

    private function getAutoPrizeDistribution(int $paidPositions): array
    {
        $tiers = $this->generateAutoPrizeTiers($paidPositions);
        $distribution = [];

        foreach ($tiers as $tier) {
            $count = $tier['to'] - $tier['from'] + 1;
            $percentPerPosition = round($tier['pct'] / max(1, $count), 2);

            for ($position = $tier['from']; $position <= $tier['to']; $position++) {
                $distribution[$position] = $percentPerPosition;
            }
        }

        $sum = array_sum($distribution);
        if (!empty($distribution) && abs($sum - 100.0) > 0.01) {
            $distribution[1] = round(($distribution[1] ?? 0) + (100.0 - $sum), 2);
        }

        return $distribution;
    }

    private function generateAutoPrizeTiers(int $paidPositions): array
    {
        if ($paidPositions <= 0) {
            return [];
        }

        if ($paidPositions === 1) {
            return [['from' => 1, 'to' => 1, 'pct' => 100.0]];
        }

        if ($paidPositions === 2) {
            return [
                ['from' => 1, 'to' => 1, 'pct' => 65.0],
                ['from' => 2, 'to' => 2, 'pct' => 35.0],
            ];
        }

        if ($paidPositions === 3) {
            return [
                ['from' => 1, 'to' => 1, 'pct' => 50.0],
                ['from' => 2, 'to' => 2, 'pct' => 30.0],
                ['from' => 3, 'to' => 3, 'pct' => 20.0],
            ];
        }

        $tiers = [
            ['from' => 1, 'to' => 1],
            ['from' => 2, 'to' => 2],
            ['from' => 3, 'to' => 3],
        ];

        $remaining = $paidPositions - 3;
        $position = 4;

        if ($remaining <= 3) {
            $tiers[] = ['from' => 4, 'to' => $paidPositions];
        } else {
            $chunks = $remaining <= 8 ? 2 : ($remaining <= 20 ? 3 : 4);
            $base = (int) floor($remaining / $chunks);
            $extra = $remaining - ($base * $chunks);
            $sizes = [];

            for ($chunk = 0; $chunk < $chunks; $chunk++) {
                $sizes[] = $base + ($chunk < $extra ? 1 : 0);
            }

            sort($sizes);

            foreach ($sizes as $size) {
                $tiers[] = ['from' => $position, 'to' => $position + $size - 1];
                $position += $size;
            }
        }

        $tierCount = count($tiers);
        $floorPctPerPerson = 100.0 / ($paidPositions * 3.6);
        $totalFloor = $floorPctPerPerson * $paidPositions;
        $curvePool = 100.0 - $totalFloor;

        $spread = max(3, pow($paidPositions, 1.2));
        $ratio = pow($spread, 1.0 / max(1, $tierCount - 1));

        $perPerson = array_fill(0, $tierCount, 0);
        $perPerson[$tierCount - 1] = 1;

        for ($index = $tierCount - 2; $index >= 0; $index--) {
            $perPerson[$index] = $perPerson[$index + 1] * $ratio;
        }

        $totalRaw = 0;
        for ($index = 0; $index < $tierCount; $index++) {
            $count = $tiers[$index]['to'] - $tiers[$index]['from'] + 1;
            $totalRaw += $perPerson[$index] * $count;
        }

        for ($index = 0; $index < $tierCount; $index++) {
            $count = $tiers[$index]['to'] - $tiers[$index]['from'] + 1;
            $curvePctPerPerson = $curvePool * $perPerson[$index] / max($totalRaw, 1);
            $totalPctPerPerson = $floorPctPerPerson + $curvePctPerPerson;
            $tiers[$index]['pct'] = round($totalPctPerPerson * $count, 2);
        }

        $sum = array_sum(array_column($tiers, 'pct'));
        if (abs($sum - 100.0) > 0.01) {
            $tiers[0]['pct'] = round($tiers[0]['pct'] + (100.0 - $sum), 2);
        }

        return $tiers;
    }

    private function resolveManualPrizeDistribution(?string $rawValue, float $totalPrize, ?int $expectedPositions = null): ?array
    {
        $rawValue = trim((string) ($rawValue ?? ''));
        if ($rawValue === '') {
            return null;
        }

        if ($totalPrize <= 0) {
            throw ValidationException::withMessages([
                'prize_distribution' => 'A distribuição manual só pode ser usada quando houver prêmio total calculado.',
            ]);
        }

        $decoded = json_decode($rawValue, true);
        if (!is_array($decoded)) {
            throw ValidationException::withMessages([
                'prize_distribution' => 'A distribuição manual do prêmio está inválida.',
            ]);
        }

        $isFlatPercentMap = true;
        foreach ($decoded as $item) {
            if (is_array($item) || !is_numeric($item)) {
                $isFlatPercentMap = false;
                break;
            }
        }

        if ($isFlatPercentMap) {
            $distribution = [];
            foreach ($decoded as $position => $percent) {
                $position = (int) $position;
                $percent = (float) $percent;

                if ($position < 1) {
                    continue;
                }

                if ($percent < 0) {
                    throw ValidationException::withMessages([
                        'prize_distribution' => 'Os percentuais do prêmio não podem ser negativos.',
                    ]);
                }

                $distribution[$position] = round($percent, 6);
            }

            ksort($distribution);

            if (empty($distribution)) {
                return null;
            }

            $positionsCount = count($distribution);
            $expectedPositions ??= $positionsCount;

            if ($positionsCount !== $expectedPositions) {
                throw ValidationException::withMessages([
                    'prize_distribution' => 'Preencha o valor de todas as posições premiadas.',
                ]);
            }

            for ($position = 1; $position <= $expectedPositions; $position++) {
                if (!array_key_exists($position, $distribution)) {
                    throw ValidationException::withMessages([
                        'prize_distribution' => 'A distribuição manual precisa começar na 1ª posição e seguir sem pular posições.',
                    ]);
                }
            }

            $sum = array_sum($distribution);
            if ($sum > 100.05) {
                throw ValidationException::withMessages([
                    'prize_distribution' => 'A distribuição manual do prêmio não pode passar de 100%.',
                ]);
            }

            return $distribution;
        }

        $amounts = [];
        foreach ($decoded as $key => $item) {
            $position = null;
            $amount = null;

            if (is_array($item)) {
                $position = isset($item['position']) ? (int) $item['position'] : (is_numeric($key) ? ((int) $key + 1) : null);
                $amount = isset($item['amount']) ? (float) $item['amount'] : (isset($item['value']) ? (float) $item['value'] : null);
            } elseif (is_numeric($key)) {
                $position = (int) $key;
                $amount = (float) $item;
            }

            if ($position === null || $position < 1 || $amount === null) {
                continue;
            }

            if ($amount < 0) {
                throw ValidationException::withMessages([
                    'prize_distribution' => 'Os valores manuais do prêmio não podem ser negativos.',
                ]);
            }

            $amounts[$position] = round($amount, 2);
        }

        ksort($amounts);

        if (empty($amounts)) {
            return null;
        }

        $positionsCount = count($amounts);
        $expectedPositions ??= $positionsCount;

        if ($positionsCount !== $expectedPositions) {
            throw ValidationException::withMessages([
                'prize_distribution' => 'Preencha o valor de todas as posições premiadas.',
            ]);
        }

        for ($position = 1; $position <= $expectedPositions; $position++) {
            if (!array_key_exists($position, $amounts)) {
                throw ValidationException::withMessages([
                    'prize_distribution' => 'A distribuição manual precisa começar na 1ª posição e seguir sem pular posições.',
                ]);
            }
        }

        $sum = round(array_sum($amounts), 2);
        $expectedTotal = round($totalPrize, 2);

        if ($sum > ($expectedTotal + 0.05)) {
            throw ValidationException::withMessages([
                'prize_distribution' => 'A soma da distribuição manual não pode passar do prêmio total do bolão.',
            ]);
        }

        $distribution = [];
        foreach ($amounts as $position => $amount) {
            $distribution[$position] = round(($amount / $totalPrize) * 100, 6);
        }

        return $distribution;
    }

    private function resolvePhysicalPrizeItems(Request $request, int $paidPositions): array
    {
        if ($paidPositions <= 0) {
            throw ValidationException::withMessages([
                'paid_positions_override' => 'Informe quantas posicoes recebem premio fisico.',
            ]);
        }

        $rawItems = $request->input('physical_prizes', []);
        if (!is_array($rawItems)) {
            $rawItems = [];
        }

        $items = [];
        for ($position = 1; $position <= $paidPositions; $position++) {
            $value = trim((string) ($rawItems[$position] ?? $rawItems[(string) $position] ?? ''));

            if ($value === '') {
                throw ValidationException::withMessages([
                    "physical_prizes.{$position}" => "Informe o premio fisico da {$position}a posicao.",
                ]);
            }

            $items[$position] = $value;
        }

        return $items;
    }

    private function summarizePhysicalPrizeItems(array $items): ?string
    {
        $items = array_values(array_filter(array_map(static fn ($value) => trim((string) $value), $items)));

        if (empty($items)) {
            return null;
        }

        if (count($items) === 1) {
            return $items[0];
        }

        return count($items) . ' premios fisicos';
    }

    private function resolveEntryMode(Request $request): array
    {
        $entryMode = $request->input('entry_mode');

        // Back-compat for older forms.
        if ($entryMode === null || $entryMode === '') {
            $isPremium = (bool) $request->boolean('is_premium');
            $price = (float) $request->input('price', 0);

            return [
                'entry_mode' => $isPremium ? 'premium' : (string) (int) $price,
                'is_premium' => $isPremium,
                'price' => $isPremium ? 0.00 : $price,
            ];
        }

        $entryMode = (string) $entryMode;
        if ($entryMode === 'premium') {
            return [
                'entry_mode' => 'premium',
                'is_premium' => true,
                'price' => 0.00,
            ];
        }

        if ($entryMode === 'free') {
            return [
                'entry_mode' => 'free',
                'is_premium' => false,
                'price' => 0.00,
            ];
        }

        if ($entryMode === 'custom') {
            $customPrice = $request->input('custom_entry_price');

            if (!is_numeric($customPrice)) {
                throw ValidationException::withMessages([
                    'custom_entry_price' => 'Informe um valor personalizado válido para a entrada do bolão.',
                ]);
            }

            $price = round((float) $customPrice, 2);

            if ($price < 0.01) {
                throw ValidationException::withMessages([
                    'custom_entry_price' => 'O valor personalizado deve ser maior ou igual a R$ 0,01.',
                ]);
            }

            return [
                'entry_mode' => 'custom',
                'is_premium' => false,
                'price' => $price,
            ];
        }

        $price = round((float) $entryMode, 2);

        if ($price < 0.01) {
            throw ValidationException::withMessages([
                'entry_mode' => 'Selecione um tipo de entrada válido para o bolão.',
            ]);
        }

        return [
            'entry_mode' => (string) $price,
            'is_premium' => false,
            'price' => (float) $price,
        ];
    }

    private function notifyLeagueCompetitorFollowers(FantasyLeague $league): void
    {
        if (!$league->is_active || !$league->modalidade_id) {
            return;
        }

        $league->loadMissing('rodeio:id,name', 'modalidade:id,nome');
        $competitorIds = DB::table('competitor_modalidade')
            ->where('modalidade_id', $league->modalidade_id)
            ->when(trim((string) ($league->divisao ?? '')) !== '', function ($query) use ($league) {
                $query->where('divisao', trim((string) $league->divisao));
            })
            ->pluck('competitor_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($competitorIds)) {
            return;
        }

        $competitors = Competitor::query()->whereIn('id', $competitorIds)->get();
        $context = collect([
            $league->modalidade?->nome,
            $league->rodeio?->name,
            trim((string) ($league->divisao ?? '')) ?: null,
        ])->filter()->implode(' • ');

        foreach ($competitors as $competitor) {
            app(\App\Services\CompetitorFollowerService::class)->createEvent($competitor, 'fantasy_league_open', [
                'title' => $competitor->nome . ' está disponível em bolão',
                'message' => $competitor->nome . ' entrou no bolão ' . $league->name . ($context !== '' ? ' em ' . $context . '.' : '.'),
                'cta_label' => 'Ver bolão e ficha',
                'cta_url' => route('home'),
                'rodeio_id' => $league->rodeio_id,
                'modalidade_id' => $league->modalidade_id,
                'fantasy_league_id' => $league->id,
                'source_key' => 'fantasy_league:' . $league->id . ':' . $competitor->id,
                'metadata' => [
                    'league_name' => $league->name,
                    'divisao' => $league->divisao,
                ],
            ]);
        }
    }

    private function notifyLeagueOpeningReminderSubscribers(FantasyLeague $league, FantasyLeagueOpeningReminderService $service): void
    {
        try {
            $service->sendLeagueOpenedNotifications($league);
        } catch (\Throwable $exception) {
            Log::warning('[FantasyLeague] Falha ao disparar alertas de abertura de bolão', [
                'league_id' => $league->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function index(Request $request)
    {
        if (!Schema::hasTable('fantasy_leagues')) {
            $pageTitle = 'Bolão';
            $message = 'Tabela fantasy_leagues não existe neste ambiente.';
            return view('admin.feature_unavailable', compact('pageTitle', 'message'));
        }

        $pageTitle = 'Bolão';

        $query = FantasyLeague::query();

        if ($request->filled('category')) {
            $query->where('category', $request->string('category')->toString());
        }

        if ($request->filled('is_premium')) {
            $type = $request->string('is_premium')->toString();
            if ($type === 'free') {
                $query->where('is_premium', false)->where('price', '<=', 0);
            } elseif ($type === 'paid') {
                $query->where('is_premium', false)->where('price', '>', 0);
            } else {
                $query->where('is_premium', $type === '1');
            }
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->string('is_active')->toString() === '1');
        }

        if ($request->filled('q')) {
            $q = trim($request->string('q')->toString());
            $query->where('name', 'like', "%{$q}%");
        }

        $leagues = $query->orderByDesc('id')->paginate(getPaginate())->withQueryString();
        $categories = FantasyLeague::query()->select('category')->distinct()->orderBy('category')->pluck('category');

        return view('admin.fantasy_leagues.index', compact('pageTitle', 'leagues', 'categories'));
    }

    public function entries(Request $request)
    {
        $pageTitle = 'Entradas nos Bolões';

        $query = \App\Models\FantasyTeam::with(['fantasyLeague', 'user', 'botUser'])
            ->orderByDesc('created_at');

        if ($request->filled('league_id')) {
            $query->where('fantasy_league_id', $request->league_id);
        }

        if ($request->filled('type')) {
            if ($request->type === 'real') {
                $query->whereNotNull('user_id');
            } elseif ($request->type === 'bot') {
                $query->whereNotNull('bot_user_id');
            }
        }

        $entries = $query->paginate(50)->withQueryString();
        $leagues = FantasyLeague::orderByDesc('id')->pluck('name', 'id');

        return view('admin.fantasy_leagues.entries', compact('pageTitle', 'entries', 'leagues'));
    }

    public function create()
    {
        if (!Schema::hasTable('fantasy_leagues')) {
            $pageTitle = 'Bolão';
            $message = 'Tabela fantasy_leagues não existe neste ambiente.';
            return view('admin.feature_unavailable', compact('pageTitle', 'message'));
        }

        $pageTitle = 'Adicionar Liga';
        $rodeios = Rodeio::orderBy('id', 'desc')->get(['id', 'name']);
        $modalidades = Modalidade::orderBy('id', 'desc')->get(['id', 'rodeio_id', 'nome', 'tem_divisoes', 'divisoes']);
        $sponsors = Sponsor::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        return view('admin.fantasy_leagues.create', compact('pageTitle', 'rodeios', 'modalidades', 'sponsors'));
    }

    public function store(Request $request, FantasyLeagueOpeningReminderService $openingReminderService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'entry_mode' => 'required|in:premium,free,0.01,20,50,100,custom',
            'custom_entry_price' => 'nullable|required_if:entry_mode,custom|numeric|min:0.01|max:10000',
            'house_cut_percent' => 'nullable|numeric|min:0|max:50',
            'reward_mode' => 'nullable|in:computed,manual_prize,points',
            'manual_prize_pool' => 'nullable|numeric|min:0',
            'prize_type' => 'nullable|in:money,physical',
            'prize_description' => 'nullable|string|max:500',
            'physical_prizes' => 'nullable|array',
            'physical_prizes.*' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'max_users' => 'nullable|integer|min:1',
            'paid_positions_override' => 'nullable|integer|min:1',
            'prize_distribution' => 'nullable|string',
            'season_id' => 'nullable|integer|min:1',
            'rodeio_id' => 'required|exists:rodeios,id',
            'modalidade_id' => 'required|exists:modalidades,id',
            'organizer_sponsor_id' => 'nullable|exists:sponsors,id',
            'divisao' => 'nullable|string|max:60',
            'closes_at' => 'nullable|date',
            'registration_deadline' => 'nullable|date',
            'allow_late_registration' => 'nullable|boolean',
            'total_prize' => 'nullable|numeric|min:0',
        ]);

        $validated = $this->validateLeagueContext($validated);

        $paidPositionsOverride = isset($validated['paid_positions_override']) && $validated['paid_positions_override'] !== null
            ? (int) $validated['paid_positions_override']
            : null;

        $entry = $this->resolveEntryMode($request);
        $isPremium = (bool) $entry['is_premium'];
        $price = (float) $entry['price'];
        $isFree = !$isPremium && $price <= 0.0;

        $rewardMode = (string) ($validated['reward_mode'] ?? ($isPremium ? 'points' : 'computed'));
        $manualPrizePool = isset($validated['manual_prize_pool']) ? (float) $validated['manual_prize_pool'] : null;
        $prizeType = (string) ($validated['prize_type'] ?? 'money');
        $prizeDescription = trim((string) ($validated['prize_description'] ?? '')) ?: null;
        $prizeItems = null;

        if ($isPremium) {
            if (!in_array($rewardMode, ['manual_prize', 'points'], true)) {
                $rewardMode = 'points';
            }
            if ($rewardMode !== 'manual_prize') {
                $manualPrizePool = null;
                $prizeDescription = null;
                $prizeItems = null;
            }
        } elseif ($isFree) {
            $rewardMode = 'manual_prize';
        } else {
            if ($price < 0.01) {
                throw ValidationException::withMessages([
                    'custom_entry_price' => 'Informe um valor de entrada válido para o bolão.',
                ]);
            }
            $rewardMode = 'computed';
            $manualPrizePool = null;
            $prizeDescription = null;
            $prizeItems = null;

            // House cut is required for paid leagues.
            $request->validate([
                'house_cut_percent' => "required|numeric|min:0|max:50",
            ]);
        }

        if ($rewardMode === 'manual_prize') {
            if (!in_array($prizeType, ['money', 'physical'], true)) {
                $prizeType = 'money';
            }

            if ($prizeType === 'physical') {
                $manualPrizePool = null;
            } elseif ($manualPrizePool === null || $manualPrizePool <= 0) {
                throw ValidationException::withMessages([
                    'manual_prize_pool' => 'Informe o valor do premio em dinheiro.',
                ]);
            }
        } else {
            $prizeType = 'money';
            $prizeDescription = null;
            $prizeItems = null;
        }

        $houseCut = ($isPremium || $isFree) ? 0.00 : (float) ($validated['house_cut_percent'] ?? 30);

        // Calculate total_prize - prêmio potencial máximo (baseado no max_users)
        $totalPrize = 0;
        
        if ($rewardMode === 'manual_prize' && $prizeType === 'money' && $manualPrizePool !== null) {
            $totalPrize = $manualPrizePool;
        } elseif (!$isPremium && !$isFree && !empty($validated['max_users'])) {
            // Prêmio potencial = max_users × preço × (1 - taxa)
            $totalPrize = (int) $validated['max_users'] * $price * (1 - $houseCut / 100);
        }

        $paidPositions = $this->getPaidPositionsForAdminPreview((int) ($validated['max_users'] ?? 0), $paidPositionsOverride);
        $manualDistribution = $this->resolveManualPrizeDistribution(
            $totalPrize > 0 ? ($validated['prize_distribution'] ?? null) : null,
            (float) $totalPrize,
            $paidPositions > 0 ? $paidPositions : null
        );

        if ($manualDistribution !== null && $paidPositionsOverride === null) {
            $paidPositionsOverride = count($manualDistribution);
            $paidPositions = count($manualDistribution);
        }

        if ($totalPrize > 0 && $manualDistribution === null && $paidPositionsOverride !== null && $paidPositions > 0) {
            $manualDistribution = $this->getAutoPrizeDistribution($paidPositions);
        }

        if ($paidPositionsOverride !== null && !empty($validated['max_users']) && $paidPositionsOverride > (int) $validated['max_users']) {
            throw ValidationException::withMessages([
                'paid_positions_override' => 'O Top pago não pode ser maior que o máximo de usuários do bolão.',
            ]);
        }

        if ($rewardMode === 'manual_prize' && $prizeType === 'physical') {
            $prizeItems = $this->resolvePhysicalPrizeItems($request, $paidPositions);
            $prizeDescription = $prizeDescription ?: $this->summarizePhysicalPrizeItems($prizeItems);
        }

        $league = FantasyLeague::create($this->buildFantasyLeaguePayload($validated, [
            'price' => $price,
            'is_premium' => $isPremium,
            'house_cut_percent' => $houseCut,
            'reward_mode' => $rewardMode,
            'manual_prize_pool' => $manualPrizePool,
            'prize_type' => $prizeType,
            'prize_description' => $prizeDescription,
            'prize_items' => $prizeItems,
            'prize_distribution' => $manualDistribution,
            'paid_positions_override' => $paidPositionsOverride,
            'total_prize' => $totalPrize,
        ]));

        // Bust fantasy leagues API cache
        $this->bustFantasyLeagueCache();
        $this->notifyLeagueCompetitorFollowers($league);
        $this->notifyLeagueOpeningReminderSubscribers($league, $openingReminderService);

        $notify[] = ['success', 'Liga criada com sucesso'];
        return redirect()->route('admin.fantasy_leagues.edit', $league)->withNotify($notify);
    }

    public function edit(FantasyLeague $fantasyLeague)
    {
        $pageTitle = 'Editar Liga';
        $rodeios = Rodeio::orderBy('id', 'desc')->get(['id', 'name']);
        $modalidades = Modalidade::orderBy('id', 'desc')->get(['id', 'rodeio_id', 'nome', 'tem_divisoes', 'divisoes']);
        $sponsors = Sponsor::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        return view('admin.fantasy_leagues.edit', compact('pageTitle', 'fantasyLeague', 'rodeios', 'modalidades', 'sponsors'));
    }

    public function update(Request $request, FantasyLeague $fantasyLeague, FantasyLeagueOpeningReminderService $openingReminderService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'entry_mode' => 'required|in:premium,free,0.01,20,50,100,custom',
            'custom_entry_price' => 'nullable|required_if:entry_mode,custom|numeric|min:0.01|max:10000',
            'house_cut_percent' => 'nullable|numeric|min:0|max:50',
            'reward_mode' => 'nullable|in:computed,manual_prize,points',
            'manual_prize_pool' => 'nullable|numeric|min:0',
            'prize_type' => 'nullable|in:money,physical',
            'prize_description' => 'nullable|string|max:500',
            'physical_prizes' => 'nullable|array',
            'physical_prizes.*' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'max_users' => 'nullable|integer|min:1',
            'paid_positions_override' => 'nullable|integer|min:1',
            'prize_distribution' => 'nullable|string',
            'season_id' => 'nullable|integer|min:1',
            'rodeio_id' => 'required|exists:rodeios,id',
            'modalidade_id' => 'required|exists:modalidades,id',
            'organizer_sponsor_id' => 'nullable|exists:sponsors,id',
            'divisao' => 'nullable|string|max:60',
            'closes_at' => 'nullable|date',
            'registration_deadline' => 'nullable|date',
            'allow_late_registration' => 'nullable|boolean',
            'total_prize' => 'nullable|numeric|min:0',
        ]);

        $validated = $this->validateLeagueContext($validated);

        $paidPositionsOverride = isset($validated['paid_positions_override']) && $validated['paid_positions_override'] !== null
            ? (int) $validated['paid_positions_override']
            : null;

        $entry = $this->resolveEntryMode($request);
        $isPremium = (bool) $entry['is_premium'];
        $price = (float) $entry['price'];
        $isFree = !$isPremium && $price <= 0.0;

        $rewardMode = (string) ($validated['reward_mode'] ?? ($isPremium ? 'points' : 'computed'));
        $manualPrizePool = isset($validated['manual_prize_pool']) ? (float) $validated['manual_prize_pool'] : null;
        $prizeType = (string) ($validated['prize_type'] ?? 'money');
        $prizeDescription = trim((string) ($validated['prize_description'] ?? '')) ?: null;
        $prizeItems = null;

        if ($isPremium) {
            if (!in_array($rewardMode, ['manual_prize', 'points'], true)) {
                $rewardMode = 'points';
            }
            if ($rewardMode !== 'manual_prize') {
                $manualPrizePool = null;
                $prizeDescription = null;
                $prizeItems = null;
            }
        } elseif ($isFree) {
            $rewardMode = 'manual_prize';
        } else {
            if ($price < 0.01) {
                throw ValidationException::withMessages([
                    'custom_entry_price' => 'Informe um valor de entrada válido para o bolão.',
                ]);
            }
            $rewardMode = 'computed';
            $manualPrizePool = null;
            $prizeDescription = null;
            $prizeItems = null;
            
            // House cut is required for paid leagues.
            $request->validate([
                'house_cut_percent' => "required|numeric|min:0|max:50",
            ]);
        }

        if ($rewardMode === 'manual_prize') {
            if (!in_array($prizeType, ['money', 'physical'], true)) {
                $prizeType = 'money';
            }

            if ($prizeType === 'physical') {
                $manualPrizePool = null;
            } elseif ($manualPrizePool === null || $manualPrizePool <= 0) {
                throw ValidationException::withMessages([
                    'manual_prize_pool' => 'Informe o valor do premio em dinheiro.',
                ]);
            }
        } else {
            $prizeType = 'money';
            $prizeDescription = null;
            $prizeItems = null;
        }

        $houseCut = ($isPremium || $isFree) ? 0.00 : (float) ($validated['house_cut_percent'] ?? 30);

        if ($fantasyLeague->image) {
            $this->deleteLeagueImage($fantasyLeague->image);
        }

        // Calculate total_prize - prêmio potencial máximo (baseado no max_users)
        $totalPrize = 0;
        
        if ($rewardMode === 'manual_prize' && $prizeType === 'money' && $manualPrizePool !== null) {
            $totalPrize = $manualPrizePool;
        } elseif (!$isPremium && !$isFree && !empty($validated['max_users'])) {
            $totalPrize = (int) $validated['max_users'] * $price * (1 - $houseCut / 100);
        }

        $paidPositions = $this->getPaidPositionsForAdminPreview((int) ($validated['max_users'] ?? 0), $paidPositionsOverride);
        $manualDistribution = $this->resolveManualPrizeDistribution(
            $totalPrize > 0 ? ($validated['prize_distribution'] ?? null) : null,
            (float) $totalPrize,
            $paidPositions > 0 ? $paidPositions : null
        );

        if ($manualDistribution !== null && $paidPositionsOverride === null) {
            $paidPositionsOverride = count($manualDistribution);
            $paidPositions = count($manualDistribution);
        }

        if ($totalPrize > 0 && $manualDistribution === null && $paidPositionsOverride !== null && $paidPositions > 0) {
            $manualDistribution = $this->getAutoPrizeDistribution($paidPositions);
        }

        if ($paidPositionsOverride !== null && !empty($validated['max_users']) && $paidPositionsOverride > (int) $validated['max_users']) {
            throw ValidationException::withMessages([
                'paid_positions_override' => 'O Top pago não pode ser maior que o máximo de usuários do bolão.',
            ]);
        }

        if ($rewardMode === 'manual_prize' && $prizeType === 'physical') {
            $prizeItems = $this->resolvePhysicalPrizeItems($request, $paidPositions);
            $prizeDescription = $prizeDescription ?: $this->summarizePhysicalPrizeItems($prizeItems);
        }

        $fantasyLeague->update($this->buildFantasyLeaguePayload($validated, [
            'price' => $price,
            'is_premium' => $isPremium,
            'house_cut_percent' => $houseCut,
            'reward_mode' => $rewardMode,
            'manual_prize_pool' => $manualPrizePool,
            'prize_type' => $prizeType,
            'prize_description' => $prizeDescription,
            'prize_items' => $prizeItems,
            'prize_distribution' => $manualDistribution,
            'paid_positions_override' => $paidPositionsOverride,
            'total_prize' => $totalPrize,
        ]));

        // Bust fantasy leagues API cache so frontend picks up changes immediately
        $this->bustFantasyLeagueCache();
        $this->notifyLeagueCompetitorFollowers($fantasyLeague);
        $this->notifyLeagueOpeningReminderSubscribers($fantasyLeague, $openingReminderService);

        $notify[] = ['success', 'Liga atualizada com sucesso'];
        return back()->withNotify($notify);
    }

    public function destroy(FantasyLeague $fantasyLeague)
    {
        if ($fantasyLeague->image) {
            $this->deleteLeagueImage($fantasyLeague->image);
        }
        $fantasyLeague->delete();

        // Bust fantasy leagues API cache
        $this->bustFantasyLeagueCache();

        $notify[] = ['success', 'Liga excluída com sucesso'];
        return redirect()->route('admin.fantasy_leagues.index')->withNotify($notify);
    }

    public function toggleStatus(FantasyLeague $fantasyLeague, FantasyLeagueOpeningReminderService $openingReminderService)
    {
        $fantasyLeague->is_active = !$fantasyLeague->is_active;
        $fantasyLeague->save();

        // Bust fantasy leagues API cache
        $this->bustFantasyLeagueCache();
        $this->notifyLeagueCompetitorFollowers($fantasyLeague);
        $this->notifyLeagueOpeningReminderSubscribers($fantasyLeague, $openingReminderService);

        $notify[] = ['success', 'Status da liga atualizado'];
        return back()->withNotify($notify);
    }

    /**
     * Preview da finalização da liga (mostra ranking e prêmios sem executar)
     */
    public function previewFinalize(FantasyLeague $fantasyLeague, FinalizeFantasyLeagueService $service)
    {
        if ($fantasyLeague->status === 'finalized') {
            return response()->json([
                'success' => false,
                'error' => 'Liga já foi finalizada anteriormente',
            ], 400);
        }

        $preview = $service->preview($fantasyLeague);
        
        return response()->json([
            'success' => true,
            'data' => $preview,
        ]);
    }

    /**
     * Finalizar liga e distribuir prêmios
     */
    public function finalize(Request $request, FantasyLeague $fantasyLeague, FinalizeFantasyLeagueService $service)
    {
        if ($fantasyLeague->status === 'finalized') {
            $notify[] = ['error', 'Liga já foi finalizada anteriormente'];
            return back()->withNotify($notify);
        }

        $result = $service->finalize($fantasyLeague, auth()->id());

        if (!$result['success']) {
            $notify[] = ['error', $result['error']];
            return back()->withNotify($notify);
        }

        $prizesCount = count($result['prizes_paid'] ?? []);
        $commissionsCount = count($result['commissions_paid'] ?? []);
        $totalCommissions = $result['total_commissions'] ?? 0;

        $message = sprintf(
            'Liga finalizada! %d prêmios distribuídos (Total: R$ %.2f). %d comissões pagas (R$ %.2f).',
            $prizesCount,
            $result['prize_pool'] ?? 0,
            $commissionsCount,
            $totalCommissions
        );

        $notify[] = ['success', $message];
        return back()->withNotify($notify);
    }

    /**
     * API JSON para finalização (usado por AJAX)
     */
    public function finalizeApi(Request $request, FantasyLeague $fantasyLeague, FinalizeFantasyLeagueService $service)
    {
        if ($fantasyLeague->status === 'finalized') {
            return response()->json([
                'success' => false,
                'error' => 'Liga já foi finalizada anteriormente',
            ], 400);
        }

        $result = $service->finalize($fantasyLeague, auth()->id());

        return response()->json($result);
    }
}

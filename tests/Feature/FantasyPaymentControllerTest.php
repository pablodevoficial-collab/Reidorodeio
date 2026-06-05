<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\FantasyPaymentController;
use App\Models\FantasyLeague;
use App\Models\FantasyPayment;
use App\Models\FantasyTeam;
use App\Models\User;
use App\Services\AppCommunityFeedService;
use App\Services\AppStoreService;
use App\Services\FantasyOriginalityService;
use App\Services\FantasySalaryCapService;
use App\Services\MercadoPagoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class FantasyPaymentControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
    }

    public function test_it_creates_team_directly_using_wallet_balance_for_paid_league(): void
    {
        $user = $this->createUser(['balance' => 50.00]);
        $league = $this->createLeague(['price' => 50.00, 'is_premium' => false]);

        $storeService = Mockery::mock(AppStoreService::class);
        $storeService->shouldReceive('eligibleFantasyVoucher')->once()->andReturn(null);
        $storeService->shouldReceive('debitFantasyLeagueWallet')->once()->withArgs(function (User $walletUser, FantasyLeague $walletLeague, float $amount, array $metadata) use ($user, $league) {
            return $walletUser->is($user)
                && $walletLeague->is($league)
                && $amount === 50.0
                && $metadata['captain_id'] === 1
                && $metadata['competitor_ids'] === [1, 2, 3, 4];
        });
        $this->app->instance(AppStoreService::class, $storeService);

        $this->mockFantasySelectionServices();
        $this->actingAs($user);

        $request = Request::create('/api/fantasy/leagues/' . $league->id . '/teams/pay', 'POST', [
            'competitor_ids' => [1, 2, 3, 4],
            'captain_id' => 1,
        ]);

        $response = app(FantasyPaymentController::class)->initiatePayment($request, $league->id);
        $payload = $response->getData(true);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertTrue($payload['success']);
        $this->assertTrue($payload['wallet_applied']);
        $this->assertSame(1, FantasyTeam::query()->count());
        $this->assertDatabaseHas('fantasy_team_competitors', [
            'fantasy_team_id' => $payload['team_id'],
            'competitor_id' => 1,
            'is_captain' => 1,
        ]);
    }

    public function test_it_allows_extra_team_when_two_competitors_are_new_to_user(): void
    {
        $user = $this->createUser(['balance' => 50.00]);
        $league = $this->createLeague(['price' => 50.00, 'is_premium' => false]);
        $this->createTeamSelection($user, $league, [1, 2, 3, 4]);

        $storeService = Mockery::mock(AppStoreService::class);
        $storeService->shouldReceive('eligibleFantasyVoucher')->once()->andReturn(null);
        $storeService->shouldReceive('debitFantasyLeagueWallet')->once()->withArgs(function (User $walletUser, FantasyLeague $walletLeague, float $amount, array $metadata) use ($user, $league) {
            return $walletUser->is($user)
                && $walletLeague->is($league)
                && $amount === 50.0
                && $metadata['competitor_ids'] === [1, 2, 5, 6];
        });
        $this->app->instance(AppStoreService::class, $storeService);

        $this->mockFantasySelectionServices();
        $this->actingAs($user);

        $request = Request::create('/api/fantasy/leagues/' . $league->id . '/teams/pay', 'POST', [
            'competitor_ids' => [1, 2, 5, 6],
            'captain_id' => 1,
        ]);

        $response = app(FantasyPaymentController::class)->initiatePayment($request, $league->id);
        $payload = $response->getData(true);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertTrue($payload['success']);
        $this->assertSame(2, FantasyTeam::query()->where('user_id', $user->id)->count());
    }

    public function test_it_blocks_extra_team_when_less_than_two_competitors_are_new_to_user(): void
    {
        $user = $this->createUser(['balance' => 50.00]);
        $league = $this->createLeague(['price' => 50.00, 'is_premium' => false]);
        $this->createTeamSelection($user, $league, [1, 2, 3, 4]);

        $this->actingAs($user);

        $request = Request::create('/api/fantasy/leagues/' . $league->id . '/teams/pay', 'POST', [
            'competitor_ids' => [1, 2, 3, 5],
            'captain_id' => 1,
        ]);

        $response = app(FantasyPaymentController::class)->initiatePayment($request, $league->id);
        $payload = $response->getData(true);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertFalse($payload['success']);
        $this->assertSame('minimum_new_competitors', $payload['team_rule']);
        $this->assertSame(1, $payload['new_competitors']);
        $this->assertSame(1, FantasyTeam::query()->where('user_id', $user->id)->count());
    }

    public function test_it_generates_pix_for_ios_when_payment_is_needed(): void
    {
        $user = $this->createUser(['balance' => 0.00]);
        $league = $this->createLeague(['price' => 50.00, 'is_premium' => false]);

        $storeService = Mockery::mock(AppStoreService::class);
        $storeService->shouldReceive('eligibleFantasyVoucher')->once()->andReturn(null);
        $this->app->instance(AppStoreService::class, $storeService);

        $this->mockFantasySelectionServices();
        $this->mockFantasyPixGateway();
        $this->actingAs($user);

        $request = Request::create('/api/fantasy/leagues/' . $league->id . '/teams/pay', 'POST', [
            'competitor_ids' => [1, 2, 3, 4],
            'captain_id' => 1,
            'platform' => 'ios',
        ]);

        $response = app(FantasyPaymentController::class)->initiatePayment($request, $league->id);
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($payload['success']);
        $this->assertSame('pix-code-ios', $payload['qr_code']);
        $this->assertSame(1, FantasyPayment::query()->count());
    }

    public function test_it_processes_approved_payment_and_creates_team(): void
    {
        $payment = FantasyPayment::query()->create([
            'fantasy_league_id' => $this->createLeague(['price' => 20.00])->id,
            'user_id' => $this->createUser()->id,
            'amount' => 20.00,
            'provider' => 'mercadopago',
            'external_reference' => 'fantasy:test',
            'provider_preference_id' => 'pref-123',
            'status' => 'pending',
            'payload' => [
                'competitor_ids' => [11, 12, 13, 14],
                'captain_id' => 13,
                'team_name' => 'Equipe Teste',
            ],
        ]);

        $this->app->instance(AppCommunityFeedService::class, new class {
            public function publishFantasyTeamJoined($team): void {}
        });

        app(FantasyPaymentController::class)->processApproval($payment);

        $payment->refresh();

        $this->assertSame('approved', $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertNotNull($payment->fantasy_team_id);
        $this->assertDatabaseHas('fantasy_teams', [
            'id' => $payment->fantasy_team_id,
            'team_name' => 'Equipe Teste',
        ]);
        $this->assertDatabaseHas('fantasy_team_competitors', [
            'fantasy_team_id' => $payment->fantasy_team_id,
            'competitor_id' => 13,
            'is_captain' => 1,
        ]);
    }

    public function test_it_marks_expired_pending_payment_as_expired_on_status_check(): void
    {
        $user = $this->createUser();
        $payment = FantasyPayment::query()->create([
            'fantasy_league_id' => $this->createLeague()->id,
            'user_id' => $user->id,
            'amount' => 20.00,
            'provider' => 'mercadopago',
            'provider_preference_id' => 'pref-expired',
            'status' => 'pending',
            'expires_at' => now()->subMinute(),
        ]);

        $this->actingAs($user);
        $request = Request::create('/api/fantasy/payments/pref-expired/status', 'GET');

        $response = app(FantasyPaymentController::class)->checkStatus($request, 'pref-expired');
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('expired', $payload['status']);
        $this->assertDatabaseHas('fantasy_payments', [
            'id' => $payment->id,
            'status' => 'expired',
        ]);
    }

    public function test_it_blocks_non_premium_user_from_premium_league(): void
    {
        $user = $this->createUser(['balance' => 999.00]);
        $league = $this->createLeague([
            'price' => 0.00,
            'is_premium' => true,
        ]);

        $this->actingAs($user);

        $request = Request::create('/api/fantasy/leagues/' . $league->id . '/teams/pay', 'POST', [
            'competitor_ids' => [1, 2, 3, 4],
            'captain_id' => 1,
        ]);

        $response = app(FantasyPaymentController::class)->initiatePayment($request, $league->id);
        $payload = $response->getData(true);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertFalse($payload['success']);
        $this->assertSame('Liga Premium requer assinatura ativa', $payload['message']);
    }

    public function test_it_blocks_entry_when_league_is_full(): void
    {
        $user = $this->createUser(['balance' => 100.00]);
        $league = $this->createLeague([
            'price' => 50.00,
            'max_users' => 1,
        ]);

        FantasyTeam::query()->create([
            'user_id' => $this->createUser()->id,
            'fantasy_league_id' => $league->id,
            'team_name' => 'Equipe já dentro',
            'total_points' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $request = Request::create('/api/fantasy/leagues/' . $league->id . '/teams/pay', 'POST', [
            'competitor_ids' => [1, 2, 3, 4],
            'captain_id' => 1,
        ]);

        $response = app(FantasyPaymentController::class)->initiatePayment($request, $league->id);
        $payload = $response->getData(true);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertFalse($payload['success']);
        $this->assertSame('Liga atingiu o limite de participantes', $payload['message']);
    }

    private function mockFantasySelectionServices(): void
    {
        $salaryCapService = Mockery::mock(FantasySalaryCapService::class);
        $salaryCapService->shouldReceive('getEligibleCompetitorIds')->andReturn([1, 2, 3, 4]);
        $salaryCapService->shouldReceive('getLeaguePricing')->andReturn([
            'meta' => ['salary_cap' => 1000],
            'prices' => [1 => 200, 2 => 200, 3 => 200, 4 => 200],
        ]);
        $this->app->instance(FantasySalaryCapService::class, $salaryCapService);

        $originalityService = Mockery::mock(FantasyOriginalityService::class);
        $originalityService->shouldReceive('calculateOriginality')->andReturn([
            'originality_factor' => 1.15,
            'similarity_count' => 0,
        ]);
        $this->app->instance(FantasyOriginalityService::class, $originalityService);

        $this->app->instance(AppCommunityFeedService::class, new class {
            public function publishFantasyTeamJoined($team): void {}
        });
    }

    private function mockFantasyPixGateway(): void
    {
        $mercadoPago = Mockery::mock(MercadoPagoService::class);
        $mercadoPago->shouldReceive('createPixPayment')->once()->andReturn([
            'id' => 'mp-payment-ios',
            'status' => 'pending',
            'point_of_interaction' => [
                'transaction_data' => [
                    'qr_code' => 'pix-code-ios',
                    'qr_code_base64' => 'base64-ios',
                ],
            ],
        ]);
        $this->app->instance(MercadoPagoService::class, $mercadoPago);
    }

    private function createUser(array $overrides = []): User
    {
        $user = new User(array_merge([
            'firstname' => 'Teste',
            'lastname' => 'Financeiro',
            'username' => 'user_' . uniqid(),
            'email' => uniqid('user_', true) . '@example.com',
            'password' => bcrypt('secret'),
            'is_bot' => false,
            'show_in_listings' => true,
        ], array_diff_key($overrides, array_flip(['balance', 'referred_by_id']))));

        $user->balance = (float) ($overrides['balance'] ?? 0);
        $user->referred_by_id = $overrides['referred_by_id'] ?? null;
        $user->save();

        return $user;
    }

    private function createLeague(array $overrides = []): FantasyLeague
    {
        return FantasyLeague::query()->create(array_merge([
            'name' => 'Bolao Financeiro',
            'price' => 20.00,
            'is_premium' => false,
            'is_active' => true,
            'registration_deadline' => now()->addHour(),
            'allow_late_registration' => false,
        ], $overrides));
    }

    private function createTeamSelection(User $user, FantasyLeague $league, array $competitorIds): FantasyTeam
    {
        $team = FantasyTeam::query()->create([
            'user_id' => $user->id,
            'fantasy_league_id' => $league->id,
            'team_name' => 'Equipe existente',
            'total_points' => 0,
            'is_active' => true,
        ]);

        foreach ($competitorIds as $index => $competitorId) {
            DB::table('fantasy_team_competitors')->insert([
                'fantasy_team_id' => $team->id,
                'competitor_id' => $competitorId,
                'role' => 'titular',
                'is_captain' => $index === 0,
                'multiplier' => $index === 0 ? 1.5 : 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $team;
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->decimal('balance', 12, 2)->default(0);
            $table->unsignedBigInteger('referred_by_id')->nullable();
            $table->boolean('is_bot')->nullable();
            $table->boolean('show_in_listings')->default(true);
            $table->timestamps();
        });

        Schema::create('fantasy_leagues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('max_users')->nullable();
            $table->timestamp('registration_deadline')->nullable();
            $table->boolean('allow_late_registration')->default(false);
            $table->timestamp('closes_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fantasy_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('fantasy_league_id');
            $table->string('team_name');
            $table->decimal('total_points', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->decimal('originality_factor', 10, 2)->nullable();
            $table->unsignedInteger('similarity_count')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fantasy_team_competitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fantasy_team_id');
            $table->unsignedBigInteger('competitor_id');
            $table->string('role')->nullable();
            $table->boolean('is_captain')->default(false);
            $table->decimal('multiplier', 6, 2)->default(1);
            $table->decimal('current_points', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('fantasy_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fantasy_league_id');
            $table->foreignId('user_id');
            $table->foreignId('fantasy_team_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('provider')->nullable();
            $table->string('external_reference')->nullable();
            $table->string('provider_payment_id')->nullable();
            $table->string('provider_preference_id')->nullable();
            $table->string('status')->default('pending');
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }
}

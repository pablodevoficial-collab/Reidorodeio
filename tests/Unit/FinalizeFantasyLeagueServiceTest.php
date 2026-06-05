<?php

namespace Tests\Unit;

use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\FantasyLeague;
use App\Models\FantasyTeam;
use App\Models\User;
use App\Services\FinalizeFantasyLeagueService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FinalizeFantasyLeagueServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->unsignedBigInteger('referred_by_id')->nullable();
            $table->boolean('is_bot')->nullable();
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('total_earnings', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('referral_code')->nullable();
            $table->string('tier')->default('bronze');
            $table->unsignedInteger('total_referrals')->default(0);
            $table->unsignedInteger('active_referrals')->default(0);
            $table->decimal('total_earned', 12, 2)->default(0);
            $table->decimal('pending_commission', 12, 2)->default(0);
            $table->decimal('paid_total', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id');
            $table->unsignedBigInteger('referred_user_id')->nullable();
            $table->string('type');
            $table->unsignedBigInteger('x1_room_id')->nullable();
            $table->unsignedBigInteger('fantasy_team_id')->nullable();
            $table->decimal('base_amount', 12, 2)->nullable();
            $table->decimal('commission_percent', 12, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('eligible_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('fantasy_leagues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('house_cut_percent', 5, 2)->default(0);
            $table->boolean('is_premium')->default(false);
            $table->string('reward_mode')->nullable();
            $table->decimal('manual_prize_pool', 12, 2)->nullable();
            $table->decimal('total_prize', 12, 2)->nullable();
            $table->text('prize_distribution')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('max_users')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->unsignedBigInteger('finalized_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('fantasy_teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('bot_user_id')->nullable();
            $table->unsignedBigInteger('fantasy_league_id');
            $table->string('team_name')->nullable();
            $table->decimal('budget', 12, 2)->default(0);
            $table->decimal('total_points', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('final_position')->nullable();
            $table->decimal('prize_won', 12, 2)->default(0);
            $table->timestamp('prize_paid_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function test_it_credits_net_fantasy_prize_and_sends_commission_to_affiliate_pending(): void
    {
        $affiliateUser = $this->createUser('affiliate-finalize@example.com');
        $winner = $this->createUser('winner-finalize@example.com', $affiliateUser->id);

        $affiliate = Affiliate::query()->create([
            'user_id' => $affiliateUser->id,
            'tier' => 'gold',
            'status' => 'active',
        ]);

        $league = FantasyLeague::query()->create([
            'name' => 'Liga Teste',
            'category' => 'bolao',
            'price' => 100.00,
            'house_cut_percent' => 0.00,
            'is_premium' => false,
            'reward_mode' => 'computed',
            'is_active' => true,
            'status' => 'open',
        ]);

        $team = FantasyTeam::query()->create([
            'user_id' => $winner->id,
            'fantasy_league_id' => $league->id,
            'team_name' => 'Time Campeao',
            'total_points' => 123.00,
            'is_active' => true,
        ]);

        $result = app(FinalizeFantasyLeagueService::class)->finalize($league);

        $winner->refresh();
        $affiliate->refresh();
        $team->refresh();
        $commission = AffiliateCommission::query()->first();

        $this->assertTrue($result['success']);
        $this->assertNotNull($commission);
        $this->assertSame('100.00', number_format((float) $commission->base_amount, 2, '.', ''));
        $this->assertSame('6.00', number_format((float) $commission->commission_amount, 2, '.', ''));
        $this->assertSame('94.00', number_format((float) $winner->balance, 2, '.', ''));
        $this->assertSame('94.00', number_format((float) $winner->total_earnings, 2, '.', ''));
        $this->assertSame('94.00', number_format((float) $team->prize_won, 2, '.', ''));
        $this->assertSame('6.00', number_format((float) $affiliate->pending_commission, 2, '.', ''));
        $this->assertSame('6.00', number_format((float) $affiliate->total_earned, 2, '.', ''));
        $this->assertSame('94.00', number_format((float) ($result['prizes_paid'][0]['prize'] ?? 0), 2, '.', ''));
        $this->assertSame('100.00', number_format((float) ($result['prizes_paid'][0]['gross_prize'] ?? 0), 2, '.', ''));
        $this->assertSame('6.00', number_format((float) ($result['prizes_paid'][0]['commission_amount'] ?? 0), 2, '.', ''));
    }

    public function test_paid_positions_use_caps_for_large_leagues(): void
    {
        $service = app(FinalizeFantasyLeagueService::class);
        $method = new \ReflectionMethod($service, 'getPaidPositions');
        $method->setAccessible(true);

        $this->assertSame(50, $method->invoke($service, 250));
        $this->assertSame(50, $method->invoke($service, 501));
        $this->assertSame(50, $method->invoke($service, 999));
        $this->assertSame(100, $method->invoke($service, 1000));
        $this->assertSame(100, $method->invoke($service, 1200));
    }

    private function createUser(string $email, ?int $referredById = null): User
    {
        $user = new User([
            'firstname' => 'Teste',
            'lastname' => 'Usuario',
            'name' => 'Teste Usuario',
            'username' => str_replace(['@', '.'], '_', $email),
            'email' => $email,
        ]);

        $user->referred_by_id = $referredById;
        $user->save();

        return $user;
    }
}

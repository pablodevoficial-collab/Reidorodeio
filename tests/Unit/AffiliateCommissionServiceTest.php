<?php

namespace Tests\Unit;

use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\User;
use App\Services\AffiliateCommissionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AffiliateCommissionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->unsignedBigInteger('referred_by_id')->nullable();
            $table->boolean('is_bot')->nullable();
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
            $table->unsignedBigInteger('referred_user_id');
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
    }

    public function test_it_processes_fantasy_commission_for_referred_prize_winner(): void
    {
        $affiliateUser = $this->createUser('afiliado@example.com');
        $winner = $this->createUser('ganhador@example.com', $affiliateUser->id);
        $affiliate = Affiliate::query()->create([
            'user_id' => $affiliateUser->id,
            'tier' => 'gold',
            'status' => 'active',
        ]);

        $processed = app(AffiliateCommissionService::class)->processFantasyCommission(77, $winner->id, 1000.00);

        $affiliate->refresh();
        $commission = AffiliateCommission::query()->first();

        $this->assertTrue($processed);
        $this->assertNotNull($commission);
        $this->assertSame('fantasy_prize', $commission->type);
        $this->assertSame('60.00', number_format((float) $commission->commission_amount, 2, '.', ''));
        $this->assertSame('60.00', number_format((float) $affiliate->pending_commission, 2, '.', ''));
        $this->assertSame('60.00', number_format((float) $affiliate->total_earned, 2, '.', ''));
    }

    public function test_it_floors_fantasy_commission_instead_of_rounding_up(): void
    {
        $affiliateUser = $this->createUser('afiliado2@example.com');
        $winner = $this->createUser('ganhador2@example.com', $affiliateUser->id);
        $affiliate = Affiliate::query()->create([
            'user_id' => $affiliateUser->id,
            'tier' => 'gold',
            'status' => 'active',
        ]);

        $processed = app(AffiliateCommissionService::class)->processFantasyCommission(88, $winner->id, 333.39);

        $affiliate->refresh();
        $commission = AffiliateCommission::query()->where('fantasy_team_id', 88)->first();

        $this->assertTrue($processed);
        $this->assertNotNull($commission);
        $this->assertSame('20.00', number_format((float) $commission->commission_amount, 2, '.', ''));
        $this->assertSame('20.00', number_format((float) $affiliate->pending_commission, 2, '.', ''));
    }

    public function test_it_skips_x1_commission_when_winner_has_no_referral(): void
    {
        $winner = $this->createUser('x1@example.com');

        $processed = app(AffiliateCommissionService::class)->processX1Commission(91, $winner->id, 999, 25.00);

        $this->assertFalse($processed);
        $this->assertSame(0, AffiliateCommission::query()->count());
    }

    public function test_it_keeps_x1_commission_disabled_while_bolao_is_the_only_active_program(): void
    {
        $affiliateUserA = $this->createUser('afiliado-a@example.com');
        $affiliateUserB = $this->createUser('afiliado-b@example.com');
        $playerA = $this->createUser('player-a@example.com', $affiliateUserA->id);
        $playerB = $this->createUser('player-b@example.com', $affiliateUserB->id);

        $affiliateA = Affiliate::query()->create([
            'user_id' => $affiliateUserA->id,
            'tier' => 'diamond',
            'status' => 'active',
        ]);

        $affiliateB = Affiliate::query()->create([
            'user_id' => $affiliateUserB->id,
            'tier' => 'bronze',
            'status' => 'active',
        ]);

        $processed = app(AffiliateCommissionService::class)->processX1Commission(123, $playerA->id, $playerB->id, 150.00);

        $affiliateA->refresh();
        $affiliateB->refresh();
        $commissions = AffiliateCommission::query()->where('x1_room_id', 123)->orderBy('affiliate_id')->get();

        $this->assertFalse($processed);
        $this->assertCount(0, $commissions);
        $this->assertSame('0.00', number_format((float) $affiliateA->pending_commission, 2, '.', ''));
        $this->assertSame('0.00', number_format((float) $affiliateB->pending_commission, 2, '.', ''));
    }

    private function createUser(string $email, ?int $referredById = null): User
    {
        $user = new User([
            'firstname' => 'Teste',
            'lastname' => 'Afiliado',
            'username' => str_replace(['@', '.'], '_', $email),
            'email' => $email,
            'is_bot' => false,
        ]);

        $user->referred_by_id = $referredById;
        $user->save();

        return $user;
    }
}

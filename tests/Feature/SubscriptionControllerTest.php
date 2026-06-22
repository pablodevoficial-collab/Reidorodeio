<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\SubscriptionController;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class SubscriptionControllerTest extends TestCase
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
            $table->string('password')->nullable();
            $table->boolean('is_bot')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('original_price', 12, 2)->default(0);
            $table->unsignedInteger('duration_days')->default(30);
            $table->string('billing_cycle')->default('monthly');
            $table->json('payment_methods')->nullable();
            $table->timestamps();
        });
    }

    public function test_it_rejects_subscription_with_disallowed_payment_method(): void
    {
        $plan = SubscriptionPlan::query()->create([
            'name' => 'Premium Mensal',
            'slug' => 'mensal',
            'price' => 49.90,
            'original_price' => 49.90,
            'duration_days' => 30,
            'billing_cycle' => 'monthly',
            'payment_methods' => ['pix'],
        ]);

        $service = Mockery::mock(SubscriptionService::class);
        $service->shouldReceive('findPlanBySlug')->once()->with('mensal')->andReturn($plan);
        $controller = new SubscriptionController($service);

        $user = User::query()->create([
            'firstname' => 'Teste',
            'lastname' => 'Premium',
            'username' => 'premium_' . uniqid(),
            'email' => uniqid('premium_', true) . '@example.com',
        ]);

        $request = Request::create('/api/subscriptions/subscribe', 'POST', [
            'plan_slug' => 'mensal',
            'payment_method' => 'card',
        ]);
        $request->setUserResolver(fn ($guard = null) => $user);

        $response = $controller->subscribe($request);
        $payload = $response->getData(true);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertFalse($payload['success']);
        $this->assertStringContainsString('não aceita pagamento por cartão', mb_strtolower($payload['message']));
    }
}

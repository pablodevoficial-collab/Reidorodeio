<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\CompetitorStatsModalController;
use App\Http\Controllers\Admin\LiveTransmissionController;
use App\Http\Controllers\CompetitorFollowController;
use App\Models\Competitor;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CompetitorFollowControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
    }

    public function test_user_can_follow_and_unfollow_competitor(): void
    {
        $user = $this->createUser('seguidor@example.com');
        $competitor = $this->createCompetitor('Willian Dias');

        $request = Request::create('/web/competitors/' . $competitor->id . '/follow', 'POST');
        $request->setUserResolver(fn () => $user);

        $response = app(CompetitorFollowController::class)->store($request, $competitor, app(\App\Services\CompetitorFollowerService::class));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->getData(true)['success']);
        $this->assertTrue($response->getData(true)['following']);
        $this->assertSame(1, $response->getData(true)['followers_count']);

        $this->assertDatabaseHas('competitor_followers', [
            'competitor_id' => $competitor->id,
            'user_id' => $user->id,
        ]);

        $deleteRequest = Request::create('/web/competitors/' . $competitor->id . '/follow', 'DELETE');
        $deleteRequest->setUserResolver(fn () => $user);

        $deleteResponse = app(CompetitorFollowController::class)->destroy($deleteRequest, $competitor, app(\App\Services\CompetitorFollowerService::class));
        $this->assertSame(200, $deleteResponse->getStatusCode());
        $this->assertTrue($deleteResponse->getData(true)['success']);
        $this->assertFalse($deleteResponse->getData(true)['following']);
        $this->assertSame(0, $deleteResponse->getData(true)['followers_count']);

        $this->assertDatabaseMissing('competitor_followers', [
            'competitor_id' => $competitor->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_contexts_endpoint_returns_follow_state_and_recent_events(): void
    {
        $user = $this->createUser('radar@example.com');
        $competitor = $this->createCompetitor('Erick Viana');

        Schema::create('competitor_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('competitor_id');
            $table->unsignedBigInteger('rodeio_id')->nullable();
            $table->unsignedBigInteger('modalidade_id')->nullable();
            $table->string('divisao')->nullable();
            $table->string('tipo_fase')->nullable();
            $table->boolean('is_finalized')->default(false);
            $table->integer('count_boa')->default(0);
            $table->integer('count_negativas_total')->default(0);
            $table->decimal('aproveitamento', 8, 2)->default(0);
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('competitor_stats_global', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('competitor_id');
            $table->integer('count_boa')->default(0);
            $table->integer('count_negativas_total')->default(0);
            $table->decimal('aproveitamento', 8, 2)->default(0);
            $table->timestamps();
        });

        \DB::table('competitor_followers')->insert([
            'competitor_id' => $competitor->id,
            'user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('rodeios')->insert([
            'id' => 7,
            'name' => 'Seleções de Estados',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('modalidades')->insert([
            'id' => 9,
            'nome' => 'Laço Seleção',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('competitor_follow_events')->insert([
            'competitor_id' => $competitor->id,
            'event_type' => 'fantasy_league_open',
            'title' => 'Erick Viana está disponível em bolão',
            'message' => 'Erick Viana entrou no bolão principal.',
            'cta_label' => 'Ver bolão e ficha',
            'cta_url' => '/estatisticas?competitor=' . $competitor->id,
            'source_key' => 'fantasy_league:1:' . $competitor->id,
            'metadata' => json_encode(['league_name' => 'Bolão Principal']),
            'rodeio_id' => 7,
            'modalidade_id' => 9,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = Request::create('/api/stats/competitors/' . $competitor->id . '/contexts', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = app(CompetitorStatsModalController::class)->contexts($request, $competitor);
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(1, $payload['data']['competitor']['followers_count']);
        $this->assertTrue($payload['data']['competitor']['is_following']);
        $this->assertSame('Erick Viana está disponível em bolão', $payload['data']['recent_events'][0]['title']);
        $this->assertSame('Laço Seleção', $payload['data']['recent_events'][0]['modalidade_name']);
    }

    public function test_finish_modalidade_creates_champion_events_with_group_and_prize_context(): void
    {
        Schema::create('competitor_stats_global', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('competitor_id');
            $table->integer('pontuacao_total')->default(0);
            $table->integer('last_points')->default(0);
            $table->integer('vitorias')->default(0);
            $table->integer('derrotas')->default(0);
            $table->integer('empates')->default(0);
            $table->decimal('aproveitamento', 8, 2)->default(0);
            $table->decimal('pontuacao_media', 8, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('competitor_modalidade', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('competitor_id');
            $table->unsignedBigInteger('modalidade_id');
            $table->string('divisao')->nullable();
            $table->string('status')->nullable();
            $table->integer('numero_participacao')->nullable();
            $table->decimal('multiplicador_atual', 8, 2)->nullable();
            $table->boolean('disponivel_participacao')->nullable();
            $table->text('dados_especificos')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        Schema::create('modalidade_competitor_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('modalidade_id');
            $table->string('divisao')->nullable();
            $table->string('nome')->nullable();
            $table->integer('tamanho')->default(1);
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('modalidade_competitor_group_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('competitor_id');
            $table->timestamps();
        });

        $follower = $this->createUser('campeao@example.com');
        $groupCompetitor = $this->createCompetitor('Thaian De Avila');
        $soloCompetitor = $this->createCompetitor('Willian Dias');
        $dqCompetitor = $this->createCompetitor('Competidor DQ');

        \DB::table('competitor_followers')->insert([
            ['competitor_id' => $groupCompetitor->id, 'user_id' => $follower->id, 'created_at' => now(), 'updated_at' => now()],
            ['competitor_id' => $soloCompetitor->id, 'user_id' => $follower->id, 'created_at' => now(), 'updated_at' => now()],
            ['competitor_id' => $dqCompetitor->id, 'user_id' => $follower->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        \DB::table('rodeios')->insert([
            'id' => 21,
            'name' => '1º Desafio Seleções De Estados',
            'divisao_atual' => 'Profissional',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('modalidades')->insert([
            'id' => 31,
            'rodeio_id' => 21,
            'nome' => 'Laço Seleção',
            'status' => 'ao_vivo',
            'tipo_premio' => 'dinheiro',
            'valor_premio' => 1000,
            'descricao_premio' => null,
            'tamanho_equipe' => 2,
            'tem_divisoes' => 1,
            'divisoes' => json_encode([
                ['nome' => 'Profissional', 'tipo_premio' => 'dinheiro', 'valor_premio' => 2000, 'descricao_premio' => null],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('competitor_modalidade')->insert([
            ['competitor_id' => $groupCompetitor->id, 'modalidade_id' => 31, 'divisao' => 'Profissional', 'status' => null, 'created_at' => now(), 'updated_at' => now()],
            ['competitor_id' => $soloCompetitor->id, 'modalidade_id' => 31, 'divisao' => 'Profissional', 'status' => null, 'created_at' => now(), 'updated_at' => now()],
            ['competitor_id' => $dqCompetitor->id, 'modalidade_id' => 31, 'divisao' => 'Profissional', 'status' => 'desqualificado', 'created_at' => now(), 'updated_at' => now()],
        ]);

        \DB::table('modalidade_competitor_groups')->insert([
            'id' => 41,
            'modalidade_id' => 31,
            'divisao' => 'Profissional',
            'nome' => 'Tropa Ouro',
            'tamanho' => 2,
            'status' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('modalidade_competitor_group_members')->insert([
            ['group_id' => 41, 'competitor_id' => $groupCompetitor->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $request = Request::create('/admin/live-transmission/finish-modalidade', 'POST', [
            'modalidade_id' => 31,
        ]);

        $response = app(LiveTransmissionController::class)->finishModalidade($request);
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($payload['success']);
        $this->assertSame(2, $payload['champions_notified']);

        $groupEvent = \DB::table('competitor_follow_events')
            ->where('competitor_id', $groupCompetitor->id)
            ->where('event_type', 'modalidade_champion')
            ->first();

        $soloEvent = \DB::table('competitor_follow_events')
            ->where('competitor_id', $soloCompetitor->id)
            ->where('event_type', 'modalidade_champion')
            ->first();

        $dqEvent = \DB::table('competitor_follow_events')
            ->where('competitor_id', $dqCompetitor->id)
            ->where('event_type', 'modalidade_champion')
            ->first();

        $this->assertNotNull($groupEvent);
        $this->assertNotNull($soloEvent);
        $this->assertNull($dqEvent);

        $groupMetadata = json_decode($groupEvent->metadata ?? '{}', true);
        $soloMetadata = json_decode($soloEvent->metadata ?? '{}', true);

        $this->assertSame('Tropa Ouro', $groupMetadata['group_name'] ?? null);
        $this->assertSame('Profissional', $groupMetadata['divisao'] ?? null);
        $this->assertSame('R$ 2.000,00', $groupMetadata['prize_label'] ?? null);
        $this->assertSame('R$ 2.000,00', $soloMetadata['prize_label'] ?? null);
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
            $table->boolean('is_bot')->default(false);
            $table->boolean('show_in_listings')->default(true);
            $table->timestamps();
        });

        Schema::create('competitors', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('foto')->nullable();
            $table->string('nivel')->nullable();
            $table->boolean('profile_claimed')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('rodeios', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('divisao_atual')->nullable();
            $table->timestamps();
        });

        Schema::create('modalidades', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->unsignedBigInteger('rodeio_id')->nullable();
            $table->string('status')->nullable();
            $table->string('tipo_premio')->nullable();
            $table->decimal('valor_premio', 12, 2)->nullable();
            $table->string('descricao_premio')->nullable();
            $table->integer('tamanho_equipe')->nullable();
            $table->boolean('tem_divisoes')->default(false);
            $table->json('divisoes')->nullable();
            $table->timestamps();
        });

        Schema::create('competitor_followers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('competitor_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->unique(['competitor_id', 'user_id']);
        });

        Schema::create('competitor_follow_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('competitor_id');
            $table->string('event_type');
            $table->string('title');
            $table->text('message');
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('source_key')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('rodeio_id')->nullable();
            $table->unsignedBigInteger('modalidade_id')->nullable();
            $table->unsignedBigInteger('fantasy_league_id')->nullable();
            $table->unsignedBigInteger('scoring_log_id')->nullable();
            $table->timestamps();
        });
    }

    private function createUser(string $email): User
    {
        $user = new User([
            'firstname' => 'Teste',
            'lastname' => 'Seguidor',
            'username' => str_replace(['@', '.'], '_', $email),
            'email' => $email,
            'password' => bcrypt('secret'),
        ]);
        $user->save();

        return $user;
    }

    private function createCompetitor(string $name): Competitor
    {
        $competitor = new Competitor([
            'nome' => $name,
            'nivel' => 'competidor',
            'status' => 'active',
        ]);
        $competitor->save();

        return $competitor;
    }
}

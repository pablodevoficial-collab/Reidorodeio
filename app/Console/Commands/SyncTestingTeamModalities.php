<?php

namespace App\Console\Commands;

use App\Models\Competitor;
use App\Models\Modalidade;
use App\Models\ModalidadeCompetitorGroup;
use App\Models\Rodeio;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SyncTestingTeamModalities extends Command
{
    protected $signature = 'testing:sync-team-modalities';

    protected $description = 'Sincroniza as modalidades de equipe do rodeio de testes com todos os competidores';

    private const TEST_RODEIO_NAME = 'Rodeio Rei do Rodeio - Testes';
    private const TEST_DIVISAO = 'Teste';
    private const BASE_MODALIDADE_NAME = 'Laço Comprido Teste';

    /**
     * @var array<int, array<string, int|string>>
     */
    private array $summary = [];

    public function handle(): int
    {
        if (!Schema::hasTable('competitor_modalidade')) {
            $this->error('Tabela competitor_modalidade nao encontrada.');
            return self::FAILURE;
        }

        if (!Schema::hasTable('modalidade_competitor_groups') || !Schema::hasTable('modalidade_competitor_group_members')) {
            $this->error('As tabelas de grupos de modalidade nao existem neste ambiente.');
            return self::FAILURE;
        }

        $competitorIds = Competitor::query()
            ->orderByDesc('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if ($competitorIds === []) {
            $this->error('Nao ha competidores para sincronizar.');
            return self::FAILURE;
        }

        $now = now();

        DB::transaction(function () use ($competitorIds, $now) {
            $rodeio = $this->ensureTestingRodeio($now);

            $this->syncIndividualTestingModalidade($rodeio, $competitorIds, $now);

            foreach ($this->teamSpecs() as $spec) {
                $this->summary[] = $this->syncTeamModalidade($rodeio, $competitorIds, $spec, $now);
            }
        });

        $this->info('Modalidades de equipe de teste sincronizadas com sucesso.');
        $this->table(
            ['Modalidade', 'Tipo', 'Equipe', 'Grupos', 'Competidores', 'Sobrando'],
            array_map(function (array $row): array {
                return [
                    $row['nome'],
                    $row['tipo'],
                    $row['tamanho_equipe'],
                    $row['grupos_criados'],
                    $row['competidores_vinculados'],
                    $row['competidores_sobrando'],
                ];
            }, $this->summary)
        );

        return self::SUCCESS;
    }

    private function ensureTestingRodeio(Carbon $now): Rodeio
    {
        return Rodeio::query()->updateOrCreate(
            ['name' => self::TEST_RODEIO_NAME],
            [
                'start' => Carbon::today(),
                'end' => Carbon::today()->addDays(7),
                'status' => 1,
                'status_transmissao' => 'ao_vivo',
                'divisao_atual' => self::TEST_DIVISAO,
                'pausar_x1' => false,
                'info' => [
                    'cidade' => 'Ambiente de Testes',
                    'descricao' => 'Rodeio criado automaticamente para testes de bolao e X1 com entradas baixas.',
                ],
                'updated_at' => $now,
            ]
        );
    }

    /**
     * Mantem a modalidade individual de teste com todos os competidores.
     *
     * @param  array<int, int>  $competitorIds
     */
    private function syncIndividualTestingModalidade(Rodeio $rodeio, array $competitorIds, Carbon $now): void
    {
        $modalidade = Modalidade::query()->updateOrCreate(
            [
                'rodeio_id' => $rodeio->id,
                'nome' => self::BASE_MODALIDADE_NAME,
            ],
            [
                'inicio' => $now,
                'tipo_premio' => 'dinheiro',
                'valor_premio' => 0,
                'descricao_premio' => 'Premiacao de ambiente de testes',
                'status' => 'programado',
                'pausar_x1' => false,
                'tem_divisoes' => true,
                'divisoes' => [
                    ['nome' => self::TEST_DIVISAO],
                ],
                'tipo_participacao' => 'individual',
                'tamanho_equipe' => 1,
            ]
        );

        $rodeio->forceFill([
            'modalidade_atual' => $modalidade->id,
            'updated_at' => $now,
        ])->save();

        $modalidade->competitors()->sync(
            $this->buildPivotPayload($competitorIds, 'Fixture automatica para testes de arena', $now)
        );

        $this->clearModalidadeCaches($modalidade->id);
    }

    /**
     * @return array<int, array{name:string,tipo:string,size:int,group_prefix:string}>
     */
    private function teamSpecs(): array
    {
        return [
            [
                'name' => 'Laço em Dupla Teste',
                'tipo' => 'dupla',
                'size' => 2,
                'group_prefix' => 'Dupla Teste',
            ],
            [
                'name' => 'Laço em Trio Teste',
                'tipo' => 'trio',
                'size' => 3,
                'group_prefix' => 'Trio Teste',
            ],
            [
                'name' => 'Laço em Equipes de 4 Teste',
                'tipo' => 'quarteto',
                'size' => 4,
                'group_prefix' => 'Equipe 4 Teste',
            ],
            [
                'name' => 'Laço em Equipes de 10 Teste',
                'tipo' => 'deceto',
                'size' => 10,
                'group_prefix' => 'Equipe 10 Teste',
            ],
        ];
    }

    /**
     * @param  array<int, int>  $competitorIds
     * @param  array{name:string,tipo:string,size:int,group_prefix:string}  $spec
     * @return array<string, int|string>
     */
    private function syncTeamModalidade(Rodeio $rodeio, array $competitorIds, array $spec, Carbon $now): array
    {
        $modalidade = Modalidade::query()->updateOrCreate(
            [
                'rodeio_id' => $rodeio->id,
                'nome' => $spec['name'],
            ],
            [
                'inicio' => $now,
                'tipo_premio' => 'dinheiro',
                'valor_premio' => 0,
                'descricao_premio' => 'Premiacao de ambiente de testes',
                'status' => 'programado',
                'pausar_x1' => false,
                'tem_divisoes' => true,
                'divisoes' => [
                    ['nome' => self::TEST_DIVISAO],
                ],
                'tipo_participacao' => $spec['tipo'],
                'tamanho_equipe' => $spec['size'],
            ]
        );

        $groupChunks = array_values(array_filter(
            array_chunk($competitorIds, $spec['size']),
            fn (array $chunk): bool => count($chunk) === $spec['size']
        ));

        $expectedGroupIds = [];
        $groupedCompetitorIds = [];

        foreach ($groupChunks as $index => $memberIds) {
            $groupName = sprintf('%s %03d', $spec['group_prefix'], $index + 1);

            $group = ModalidadeCompetitorGroup::query()->updateOrCreate(
                [
                    'modalidade_id' => $modalidade->id,
                    'nome' => $groupName,
                ],
                [
                    'divisao' => self::TEST_DIVISAO,
                    'tamanho' => $spec['size'],
                    'status' => 'ativo',
                    'updated_at' => $now,
                ]
            );

            $group->members()->sync($memberIds);

            $expectedGroupIds[] = (int) $group->id;
            foreach ($memberIds as $memberId) {
                $groupedCompetitorIds[] = (int) $memberId;
            }
        }

        $staleGroups = ModalidadeCompetitorGroup::query()
            ->where('modalidade_id', $modalidade->id);

        if ($expectedGroupIds !== []) {
            $staleGroups->whereNotIn('id', $expectedGroupIds);
        }

        $staleGroups->delete();

        $modalidade->competitors()->sync(
            $this->buildPivotPayload(
                $groupedCompetitorIds,
                'Fixture automatica para ' . $spec['name'],
                $now
            )
        );

        $this->clearModalidadeCaches($modalidade->id);

        return [
            'nome' => $modalidade->nome,
            'tipo' => $modalidade->tipo_participacao ?? $spec['tipo'],
            'tamanho_equipe' => (int) ($modalidade->tamanho_equipe ?? $spec['size']),
            'grupos_criados' => count($groupChunks),
            'competidores_vinculados' => count($groupedCompetitorIds),
            'competidores_sobrando' => count($competitorIds) - count($groupedCompetitorIds),
        ];
    }

    /**
     * @param  array<int, int>  $competitorIds
     * @return array<int, array<string, int|string|float>>
     */
    private function buildPivotPayload(array $competitorIds, string $observacoes, Carbon $now): array
    {
        $payload = [];

        foreach (array_values($competitorIds) as $index => $competitorId) {
            $payload[(int) $competitorId] = [
                'divisao' => self::TEST_DIVISAO,
                'status' => 'ativo',
                'numero_participacao' => $index + 1,
                'multiplicador_atual' => 1.9,
                'disponivel_participacao' => 1,
                'observacoes' => $observacoes,
                'updated_at' => $now,
                'created_at' => $now,
            ];
        }

        return $payload;
    }

    private function clearModalidadeCaches(int $modalidadeId): void
    {
        $divisaoSlug = Str::slug(self::TEST_DIVISAO);

        Cache::forget("modalidade_{$modalidadeId}_grupos");
        Cache::forget("modalidade_{$modalidadeId}_divisao_{$divisaoSlug}_grupos");
        Cache::forget("modalidade_{$modalidadeId}_competitors");
        Cache::forget("modalidade_{$modalidadeId}_divisao_{$divisaoSlug}_competitors");
    }
}

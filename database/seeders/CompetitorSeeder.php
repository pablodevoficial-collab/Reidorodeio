<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Competitor;
use App\Models\CompetitorStat;

class CompetitorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $competitors = [
            [
                'nome' => 'João "Touro de Ouro" Silva',
                'idade' => 28,
                'categoria' => 'Montaria em Touros',
                'cidade' => 'Barretos - SP',
                'biografia' => 'Campeão nacional de montaria em touros em 2023. Conhecido por sua técnica impecável e coragem extraordinária.',
                'status' => 'ativo',
                'stats' => ['vitorias' => 45, 'derrotas' => 12, 'empates' => 3, 'tempo_medio' => 7.8, 'melhor_tempo' => 8.9, 'total_montarias' => 60, 'pontuacao_media' => 89.5]
            ],
            [
                'nome' => 'Maria "Relâmpago" Santos',
                'idade' => 25,
                'categoria' => 'Três Tambores',
                'cidade' => 'Ribeirão Preto - SP',
                'biografia' => 'Recordista brasileira na modalidade três tambores. Cavalga desde os 8 anos de idade.',
                'status' => 'ativo',
                'stats' => ['vitorias' => 38, 'derrotas' => 8, 'empates' => 2, 'tempo_medio' => 15.2, 'melhor_tempo' => 14.1, 'total_montarias' => 48, 'pontuacao_media' => 92.3]
            ],
            [
                'nome' => 'Pedro "Laço Certeiro" Oliveira',
                'idade' => 32,
                'categoria' => 'Laço Individual',
                'cidade' => 'Presidente Prudente - SP',
                'biografia' => 'Especialista em laço com mais de 15 anos de experiência. Tricampeão regional.',
                'status' => 'ativo',
                'stats' => ['vitorias' => 52, 'derrotas' => 15, 'empates' => 5, 'tempo_medio' => 12.5, 'melhor_tempo' => 8.9, 'total_montarias' => 72, 'pontuacao_media' => 87.8]
            ],
            [
                'nome' => 'Carlos "Vaqueiro Real" Ferreira',
                'idade' => 29,
                'categoria' => 'Vaquejada',
                'cidade' => 'Araçatuba - SP',
                'biografia' => 'Vaqueiro profissional há 12 anos. Conhecido pela parceria perfeita com seu cavalo Furacão.',
                'status' => 'ativo',
                'stats' => ['vitorias' => 41, 'derrotas' => 18, 'empates' => 4, 'tempo_medio' => 18.7, 'melhor_tempo' => 16.2, 'total_montarias' => 63, 'pontuacao_media' => 85.1]
            ],
            [
                'nome' => 'Ana "Domadora" Ribeiro',
                'idade' => 26,
                'categoria' => 'Montaria em Cavalos',
                'cidade' => 'São José do Rio Preto - SP',
                'biografia' => 'Única mulher na categoria de montaria em cavalos da região. Inspiração para muitas jovens.',
                'status' => 'ativo',
                'stats' => ['vitorias' => 29, 'derrotas' => 14, 'empates' => 2, 'tempo_medio' => 6.8, 'melhor_tempo' => 8.1, 'total_montarias' => 45, 'pontuacao_media' => 83.7]
            ],
            [
                'nome' => 'Roberto "Veterano" Costa',
                'idade' => 45,
                'categoria' => 'Laço em Dupla',
                'cidade' => 'Franca - SP',
                'biografia' => 'Veterano com mais de 25 anos de rodeio. Mentor de vários jovens competidores.',
                'status' => 'inativo',
                'stats' => ['vitorias' => 87, 'derrotas' => 23, 'empates' => 8, 'tempo_medio' => 14.2, 'melhor_tempo' => 11.5, 'total_montarias' => 118, 'pontuacao_media' => 91.2]
            ]
        ];

        foreach ($competitors as $competitorData) {
            $stats = $competitorData['stats'];
            unset($competitorData['stats']);
            
            $competitor = Competitor::create($competitorData);
            
            CompetitorStat::create(array_merge([
                'competitor_id' => $competitor->id
            ], $stats));
        }
    }
}

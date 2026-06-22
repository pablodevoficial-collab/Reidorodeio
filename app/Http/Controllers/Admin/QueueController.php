<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessCompetitorUpdate;
use App\Jobs\ProcessLiveTransmission;
use App\Jobs\ProcessRankingUpdate;
use App\Jobs\ProcessScoringUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueController extends Controller
{
    /**
     * Exibir página de monitoramento das filas
     */
    public function index()
    {
        $pageTitle = 'Monitoramento de Filas';
        return view('admin.websocket.index', compact('pageTitle'));
    }
    public function getStatus()
    {
        try {
            // Verificar jobs na tabela jobs
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();

            // Simular workers ativos (em produção, verificar processos)
            $workers = $this->getActiveWorkers();

            // Estatísticas de processamento
            $processedToday = Cache::get('jobs_processed_today', 0);

            // Status das filas específicas
            $queues = [
                'scoring-updates' => [
                    'active' => $pendingJobs > 0,
                    'pending' => DB::table('jobs')->where('queue', 'default')->count(),
                    'processed' => $processedToday,
                    'failed' => $failedJobs
                ],
                'live-transmission' => [
                    'active' => Cache::has('active_transmissions'),
                    'pending' => 0,
                    'processed' => 0,
                    'failed' => 0
                ],
                'competitor-updates' => [
                    'active' => false,
                    'pending' => 0,
                    'processed' => 0,
                    'failed' => 0
                ],
                'ranking-updates' => [
                    'active' => false,
                    'pending' => 0,
                    'processed' => 0,
                    'failed' => 0
                ]
            ];

            $stats = [
                'pending' => $pendingJobs,
                'processed' => $processedToday,
                'failed' => $failedJobs,
                'workers' => $workers
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'queues' => $queues,
                'queue_driver' => config('queue.default'),
                'cache_driver' => config('cache.default'),
                'server_time' => now()->toISOString(),
                'worker_status' => $workers > 0 ? 'active' : 'stopped'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get queue status', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter status das filas'
            ], 500);
        }
    }

    /**
     * Iniciar worker de filas
     */
    public function startWorker(Request $request)
    {
        try {
            // Verificar se já existe um worker rodando
            if ($this->getActiveWorkers() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Worker já está ativo'
                ]);
            }

            // Em desenvolvimento localhost, não podemos iniciar worker via HTTP
            // devido a limitações do servidor web local
            return response()->json([
                'success' => false,
                'message' => 'Para iniciar o worker em localhost, execute manualmente: php artisan queue:work --tries=3 --timeout=90 --sleep=3 --max-jobs=1000',
                'command' => 'php artisan queue:work --tries=3 --timeout=90 --sleep=3 --max-jobs=1000'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to check queue worker status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar status do worker: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pausar worker de filas
     */
    public function pauseWorker(Request $request)
    {
        try {
            // Em Laravel, não há pausa direta, mas podemos simular
            Cache::put('queue_worker_paused', true, now()->addHours(1));

            Log::info('Queue worker paused by admin');

            return response()->json([
                'success' => true,
                'message' => 'Worker de filas pausado'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to pause queue worker', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao pausar worker: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpar jobs das filas
     */
    public function clearJobs(Request $request)
    {
        try {
            $cleared = DB::table('jobs')->delete();
            $failedCleared = DB::table('failed_jobs')->delete();

            // Limpar cache relacionado
            Cache::forget('jobs_processed_today');
            Cache::forget('active_transmissions');

            Log::info('Queue jobs cleared by admin', [
                'pending_cleared' => $cleared,
                'failed_cleared' => $failedCleared
            ]);

            return response()->json([
                'success' => true,
                'message' => "Filas limpas: {$cleared} jobs pendentes e {$failedCleared} jobs falhados removidos"
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to clear queue jobs', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar filas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disparar job de teste
     */
    public function dispatchTestJob(Request $request)
    {
        try {
            $jobType = $request->input('job_type');
            $data = $request->input('data', []);

            switch ($jobType) {
                case 'scoring':
                    ProcessScoringUpdate::dispatch($data);
                    $message = 'Job de pontuação disparado';
                    break;

                case 'transmission':
                    ProcessLiveTransmission::dispatch($data);
                    $message = 'Job de transmissão disparado';
                    break;

                case 'competitor':
                    ProcessCompetitorUpdate::dispatch($data);
                    $message = 'Job de competidor disparado';
                    break;

                case 'ranking':
                    ProcessRankingUpdate::dispatch($data);
                    $message = 'Job de ranking disparado';
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Tipo de job inválido'
                    ], 400);
            }

            Log::info('Test job dispatched by admin', [
                'job_type' => $jobType,
                'data' => $data
            ]);

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to dispatch test job', [
                'error' => $e->getMessage(),
                'job_type' => $request->input('job_type')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao disparar job: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter workers ativos (simulação)
     */
    private function getActiveWorkers()
    {
        // Em produção, verificar processos do sistema
        // Por enquanto, verificar se há jobs sendo processados recentemente
        $recentJobs = DB::table('jobs')
            ->where('created_at', '>', now()->subMinutes(5))
            ->count();

        return $recentJobs > 0 ? 1 : 0;
    }
}

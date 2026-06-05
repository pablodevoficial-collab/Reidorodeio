<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * SystemCommandsController
 * 
 * Executa comandos Artisan diretamente pelo painel admin
 * Útil para shared hosting sem acesso SSH
 */
class SystemCommandsController extends Controller
{
    /**
     * Exibe a página de comandos do sistema
     */
    public function index()
    {
        $pageTitle = 'Comandos do Sistema';
        
        // Verificar último status dos comandos (se houver logs)
        $lastRun = cache()->get('system_commands_last_run', []);
        
        return view('admin.system.commands', compact('pageTitle', 'lastRun'));
    }
    
    /**
     * Executa o comando de aquecer cache
     */
    public function warmupCache(Request $request)
    {
        $start = microtime(true);
        
        try {
            // Limpar cache antes se solicitado
            $clear = $request->boolean('clear', false);
            
            Artisan::call('cache:warmup', [
                '--clear' => $clear
            ]);
            
            $output = Artisan::output();
            $duration = round((microtime(true) - $start) * 1000);
            
            // Salvar log do último run
            $this->logLastRun('warmup', true, $duration);
            
            return response()->json([
                'success' => true,
                'message' => 'Cache aquecido com sucesso!',
                'output' => $output,
                'duration_ms' => $duration
            ]);
            
        } catch (\Exception $e) {
            $this->logLastRun('warmup', false);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao aquecer cache: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Executa o comando de atualizar rankings
     */
    public function updateRankings(Request $request)
    {
        $start = microtime(true);
        
        try {
            $warm = $request->boolean('warm', true);
            
            Artisan::call('cache:update-rankings', [
                '--warm' => $warm
            ]);
            
            $output = Artisan::output();
            $duration = round((microtime(true) - $start) * 1000);
            
            $this->logLastRun('rankings', true, $duration);
            
            return response()->json([
                'success' => true,
                'message' => 'Rankings atualizados com sucesso!',
                'output' => $output,
                'duration_ms' => $duration
            ]);
            
        } catch (\Exception $e) {
            $this->logLastRun('rankings', false);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar rankings: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Executa o comando de processar pagamentos X1 pendentes
     */
    public function processPayments(Request $request)
    {
        $start = microtime(true);
        
        try {
            $limit = $request->input('limit', 20);
            $dryRun = $request->boolean('dry_run', false);
            
            Artisan::call('x1:process-payments', [
                '--limit' => $limit,
                '--dry-run' => $dryRun
            ]);
            
            $output = Artisan::output();
            $duration = round((microtime(true) - $start) * 1000);
            
            $this->logLastRun('payments', true, $duration);
            
            return response()->json([
                'success' => true,
                'message' => $dryRun ? 'Simulação concluída!' : 'Pagamentos processados com sucesso!',
                'output' => $output,
                'duration_ms' => $duration
            ]);
            
        } catch (\Exception $e) {
            $this->logLastRun('payments', false);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar pagamentos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Limpa todos os caches do sistema
     */
    public function clearCache()
    {
        $start = microtime(true);
        
        try {
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            
            $duration = round((microtime(true) - $start) * 1000);
            
            $this->logLastRun('clear', true, $duration);
            
            return response()->json([
                'success' => true,
                'message' => 'Todos os caches foram limpos!',
                'duration_ms' => $duration
            ]);
            
        } catch (\Exception $e) {
            $this->logLastRun('clear', false);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar cache: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Limpa salas X1 expiradas
     */
    public function cleanExpiredRooms()
    {
        $start = microtime(true);
        
        try {
            Artisan::call('x1:clean-expired');
            
            $output = Artisan::output();
            $duration = round((microtime(true) - $start) * 1000);
            
            $this->logLastRun('clean_x1', true, $duration);
            
            return response()->json([
                'success' => true,
                'message' => 'Salas X1 expiradas limpas!',
                'output' => $output,
                'duration_ms' => $duration
            ]);
            
        } catch (\Exception $e) {
            $this->logLastRun('clean_x1', false);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar salas: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Registra o último run de um comando
     */
    private function logLastRun(string $command, bool $success, int $duration = 0): void
    {
        $lastRun = cache()->get('system_commands_last_run', []);
        
        $lastRun[$command] = [
            'success' => $success,
            'duration_ms' => $duration,
            'ran_at' => now()->toDateTimeString(),
            'ran_by' => auth('admin')->user()->name ?? 'Admin'
        ];
        
        cache()->put('system_commands_last_run', $lastRun, now()->addDays(7));
    }
}

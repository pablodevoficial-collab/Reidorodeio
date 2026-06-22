<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WebSocketService;
use App\Models\Rodeio;
use App\Models\Modalidade;
use App\Models\CompetitorModalidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WebSocketController extends Controller
{
    /**
     * Página principal de monitoramento WebSocket
     */
    public function index()
    {
        $pageTitle = 'Monitoramento WebSocket';
        
        return view('admin.websocket.index', compact('pageTitle'));
    }

    /**
     * Verificar saúde dos canais WebSocket
     */
    public function checkHealth()
    {
        try {
            $health = WebSocketService::checkChannelsHealth();
            
            return response()->json([
                'success' => true,
                'channels' => $health,
                'server_time' => now()->toISOString(),
                'cache_driver' => config('cache.default'),
                'broadcast_driver' => config('broadcasting.default')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar saúde dos canais: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estatísticas em tempo real
     */
    public function getRealTimeStats(Request $request)
    {
        try {
            $stats = [
                'total_rodeios_ativos' => Rodeio::where('status', 'ativo')->count(),
                'total_modalidades' => Modalidade::count(),
                'total_competidores_ativos' => CompetitorModalidade::where('status', 'confirmado')->count(),
                'viewers_online' => 0,
                'active_sessions' => 0
            ];

            // Contar viewers online em todos os rodeios
            $rodeiosAtivos = Rodeio::where('status', 'ativo')->pluck('id');
            foreach ($rodeiosAtivos as $rodeioId) {
                $stats['viewers_online'] += WebSocketService::getViewersCount($rodeioId);
            }

            // Contar sessões ativas
            $stats['active_sessions'] = Cache::get('total_active_sessions', 0);

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Conectar cliente WebSocket
     */
    public function connect(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'rodeio_id' => 'nullable|exists:rodeios,id',
            'modalidade_id' => 'nullable|exists:modalidades,id',
            'user_type' => 'in:admin,user,guest'
        ]);

        try {
            $sessionId = $request->session_id;
            $rodeioId = $request->rodeio_id;
            $userType = $request->user_type ?? 'guest';

            // Registrar sessão
            $sessionData = [
                'session_id' => $sessionId,
                'user_type' => $userType,
                'connected_at' => now()->toISOString(),
                'rodeio_id' => $rodeioId,
                'modalidade_id' => $request->modalidade_id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ];

            Cache::put("websocket_session_{$sessionId}", $sessionData, 3600);

            // Adicionar à lista de sessões ativas
            $activeSessions = Cache::get('active_websocket_sessions', []);
            if (!in_array($sessionId, $activeSessions)) {
                $activeSessions[] = $sessionId;
                Cache::put('active_websocket_sessions', $activeSessions, 3600);
            }

            // Incrementar viewers se conectado a um rodeio
            if ($rodeioId) {
                $viewersCount = WebSocketService::incrementViewers($rodeioId, $sessionId);
            }

            // Incrementar sessões ativas totais
            $totalSessions = Cache::get('total_active_sessions', 0) + 1;
            Cache::put('total_active_sessions', $totalSessions, 3600);

            return response()->json([
                'success' => true,
                'message' => 'Conectado com sucesso',
                'session_id' => $sessionId,
                'viewers_count' => $viewersCount ?? 0,
                'channels' => $this->getChannelsForUser($userType, $rodeioId, $request->modalidade_id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao conectar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desconectar cliente WebSocket
     */
    public function disconnect(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);

        try {
            $sessionId = $request->session_id;
            
            // Obter dados da sessão
            $sessionData = Cache::get("websocket_session_{$sessionId}");
            
            if ($sessionData) {
                // Decrementar viewers se estava conectado a um rodeio
                if (isset($sessionData['rodeio_id'])) {
                    WebSocketService::decrementViewers($sessionData['rodeio_id'], $sessionId);
                }

                // Decrementar sessões ativas totais
                $totalSessions = max(0, Cache::get('total_active_sessions', 0) - 1);
                Cache::put('total_active_sessions', $totalSessions, 3600);

                // Remover sessão
                Cache::forget("websocket_session_{$sessionId}");
                
                // Remover da lista de sessões ativas
                $activeSessions = Cache::get('active_websocket_sessions', []);
                $activeSessions = array_diff($activeSessions, [$sessionId]);
                Cache::put('active_websocket_sessions', array_values($activeSessions), 3600);
            }

            return response()->json([
                'success' => true,
                'message' => 'Desconectado com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao desconectar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desconectar todos os clientes WebSocket
     */
    public function disconnectAll(Request $request)
    {
        try {
            // Obter lista de sessões ativas
            $activeSessions = Cache::get('active_websocket_sessions', []);
            
            $disconnectedCount = 0;
            $affectedRodeios = [];

            // Processar cada sessão ativa
            foreach ($activeSessions as $sessionId) {
                $sessionData = Cache::get("websocket_session_{$sessionId}");
                
                if ($sessionData) {
                    // Decrementar viewers se estava conectado a um rodeio
                    if (isset($sessionData['rodeio_id'])) {
                        WebSocketService::decrementViewers($sessionData['rodeio_id'], $sessionId);
                        $affectedRodeios[] = $sessionData['rodeio_id'];
                    }
                    
                    // Remover sessão
                    Cache::forget("websocket_session_{$sessionId}");
                    $disconnectedCount++;
                }
            }

            // Limpar lista de sessões ativas
            Cache::put('active_websocket_sessions', [], 3600);
            
            // Resetar contador total de sessões ativas
            Cache::put('total_active_sessions', 0, 3600);

            // Remover duplicatas dos rodeios afetados
            $affectedRodeios = array_unique($affectedRodeios);

            return response()->json([
                'success' => true,
                'message' => 'Todos os clientes WebSocket foram desconectados com sucesso',
                'disconnected_count' => $disconnectedCount,
                'affected_rodeios' => $affectedRodeios
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao desconectar todos os clientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar mensagem de teste para canais
     */
    public function sendTestMessage(Request $request)
    {
        $request->validate([
            'channel' => 'required|string',
            'message' => 'required|string',
            'data' => 'nullable|array'
        ]);

        try {
            $testData = [
                'type' => 'test',
                'message' => $request->message,
                'data' => $request->data ?? [],
                'timestamp' => now()->toISOString(),
                'sent_by' => 'admin'
            ];

            // Usar o serviço adequado baseado no canal
            switch ($request->channel) {
                case 'scoring-updates':
                    WebSocketService::broadcastScoring($testData);
                    break;
                case 'live-transmission':
                    WebSocketService::broadcastTransmission($testData);
                    break;
                case 'competitor-updates':
                    WebSocketService::broadcastCompetitorStatus($testData);
                    break;
                case 'ranking-updates':
                    WebSocketService::broadcastRanking($testData);
                    break;
                default:
                    throw new \Exception('Canal não reconhecido');
            }

            WebSocketService::logChannelActivity($request->channel, 'test_message');

            return response()->json([
                'success' => true,
                'message' => 'Mensagem de teste enviada com sucesso',
                'channel' => $request->channel,
                'data' => $testData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar mensagem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpar cache de canais
     */
    public function clearCache(Request $request)
    {
        $request->validate([
            'modalidade_id' => 'nullable|exists:modalidades,id',
            'type' => 'in:all,modalidade,sessions'
        ]);

        try {
            $cleared = [];

            switch ($request->type) {
                case 'modalidade':
                    if ($request->modalidade_id) {
                        WebSocketService::clearModalidadeCache($request->modalidade_id);
                        $cleared[] = "Modalidade {$request->modalidade_id}";
                    }
                    break;

                case 'sessions':
                    // Limpar todas as sessões
                    Cache::flush();
                    $cleared[] = 'Todas as sessões';
                    break;

                case 'all':
                default:
                    Cache::flush();
                    $cleared[] = 'Todo o cache';
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Cache limpo com sucesso',
                'cleared' => $cleared
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter canais apropriados para o tipo de usuário
     */
    private function getChannelsForUser($userType, $rodeioId = null, $modalidadeId = null)
    {
        $channels = [];

        switch ($userType) {
            case 'admin':
                $channels = [
                    'scoring-updates',
                    'live-transmission',
                    'competitor-updates',
                    'ranking-updates'
                ];
                break;

            case 'user':
                $channels = [
                    'live-transmission',
                    'ranking-updates'
                ];
                break;

            case 'guest':
            default:
                $channels = [
                    'live-transmission'
                ];
                break;
        }

        // Adicionar canais específicos se aplicável
        if ($rodeioId) {
            $channels[] = "rodeio.{$rodeioId}";
            $channels[] = "rodeio.{$rodeioId}.live";
        }

        if ($modalidadeId) {
            $channels[] = "modalidade.{$modalidadeId}";
            $channels[] = "modalidade.{$modalidadeId}.ranking";
        }

        return array_unique($channels);
    }
}

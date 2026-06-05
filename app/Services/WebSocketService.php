<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use App\Events\ScoringUpdated;
use App\Events\LiveTransmissionUpdated;
use App\Events\CompetitorStatusUpdated;
use App\Events\RankingUpdated;

class WebSocketService
{
    /**
     * Configurar canais Redis para WebSocket
     */
    public static function setupChannels()
    {
        $channels = [
            'scoring-updates',
            'live-transmission',
            'competitor-updates',
            'ranking-updates'
        ];

        foreach ($channels as $channel) {
            Cache::put("websocket_channel_{$channel}", true, 3600);
        }

        return $channels;
    }

    /**
     * Broadcast de pontuação atualizada
     */
    public static function broadcastScoring($data)
    {
        event(new ScoringUpdated($data));
        
        // Salvar no cache para clientes que se conectarem depois
        $cacheKey = "latest_scoring_{$data['modalidade_id']}_{$data['competitor_id']}";
        Cache::put($cacheKey, $data, 300); // 5 minutos
    }

    /**
     * Broadcast de transmissão ao vivo
     */
    public static function broadcastTransmission($data)
    {
        event(new LiveTransmissionUpdated($data));
        
        // Salvar estado da transmissão
        $cacheKey = "transmission_state_{$data['rodeio_id']}";
        Cache::put($cacheKey, $data, 1800); // 30 minutos
    }

    /**
     * Broadcast de status do competidor
     */
    public static function broadcastCompetitorStatus($data)
    {
        event(new CompetitorStatusUpdated($data));
        
        // Atualizar cache de status
        $cacheKey = "competitor_status_{$data['competitor_id']}";
        Cache::put($cacheKey, $data, 600); // 10 minutos
    }

    /**
     * Broadcast de ranking atualizado
     */
    public static function broadcastRanking($data)
    {
        event(new RankingUpdated($data));
        
        // Salvar ranking no cache
        $cacheKey = "ranking_{$data['modalidade_id']}";
        Cache::put($cacheKey, $data, 900); // 15 minutos
    }

    /**
     * Obter estado atual de uma modalidade
     */
    public static function getModalidadeState($modalidadeId)
    {
        return [
            'ranking' => Cache::get("ranking_{$modalidadeId}", []),
            'latest_scores' => Cache::get("latest_scoring_{$modalidadeId}_*", []),
            'transmission' => Cache::get("transmission_state_*", null)
        ];
    }

    /**
     * Obter contadores de viewers em tempo real
     */
    public static function getViewersCount($rodeioId)
    {
        $key = "viewers_count_{$rodeioId}";
        return Cache::get($key, 0);
    }

    /**
     * Incrementar contador de viewers
     */
    public static function incrementViewers($rodeioId, $sessionId)
    {
        $key = "viewers_count_{$rodeioId}";
        $viewersKey = "viewers_sessions_{$rodeioId}";
        
        // Adicionar sessão à lista de viewers
        $sessions = Cache::get($viewersKey, []);
        if (!in_array($sessionId, $sessions)) {
            $sessions[] = $sessionId;
            Cache::put($viewersKey, $sessions, 3600);
            
            // Incrementar contador
            $count = count($sessions);
            Cache::put($key, $count, 3600);
            
            return $count;
        }
        
        return count($sessions);
    }

    /**
     * Decrementar contador de viewers
     */
    public static function decrementViewers($rodeioId, $sessionId)
    {
        $viewersKey = "viewers_sessions_{$rodeioId}";
        $sessions = Cache::get($viewersKey, []);
        
        if (($index = array_search($sessionId, $sessions)) !== false) {
            unset($sessions[$index]);
            $sessions = array_values($sessions);
            
            Cache::put($viewersKey, $sessions, 3600);
            
            $key = "viewers_count_{$rodeioId}";
            $count = count($sessions);
            Cache::put($key, $count, 3600);
            
            return $count;
        }
        
        return count($sessions);
    }

    /**
     * Limpar cache de uma modalidade
     */
    public static function clearModalidadeCache($modalidadeId)
    {
        $keys = [
            "ranking_{$modalidadeId}",
            "latest_scoring_{$modalidadeId}_*"
        ];

        foreach ($keys as $key) {
            if (str_contains($key, '*')) {
                // Buscar chaves com wildcard e remover
                $cacheKeys = Cache::get('all_keys', []);
                foreach ($cacheKeys as $cacheKey) {
                    if (str_starts_with($cacheKey, str_replace('*', '', $key))) {
                        Cache::forget($cacheKey);
                    }
                }
            } else {
                Cache::forget($key);
            }
        }
    }

    /**
     * Verificar saúde dos canais WebSocket
     */
    public static function checkChannelsHealth()
    {
        $channels = self::setupChannels();
        $health = [];

        foreach ($channels as $channel) {
            $cacheKey = "websocket_channel_{$channel}";
            $health[$channel] = [
                'active' => Cache::has($cacheKey),
                'last_activity' => Cache::get("{$cacheKey}_last_activity", 'never'),
                'message_count' => Cache::get("{$cacheKey}_message_count", 0)
            ];
        }

        return $health;
    }

    /**
     * Registrar atividade em um canal
     */
    public static function logChannelActivity($channel, $messageType = 'unknown')
    {
        $cacheKey = "websocket_channel_{$channel}";
        $lastActivityKey = "{$cacheKey}_last_activity";
        $messageCountKey = "{$cacheKey}_message_count";

        Cache::put($lastActivityKey, now()->toISOString(), 3600);
        $count = Cache::get($messageCountKey, 0) + 1;
        Cache::put($messageCountKey, $count, 3600);
    }
}

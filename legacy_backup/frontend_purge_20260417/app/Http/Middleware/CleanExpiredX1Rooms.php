<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\X1RoomInstance;
use App\Models\X1Payment;
use Illuminate\Support\Facades\Log;

class CleanExpiredX1Rooms
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Limpar salas expiradas ANTES de processar a requisição
        $this->cleanExpiredRooms();
        
        return $next($request);
    }
    
    /**
     * Limpar salas X1 que expiraram
     */
    private function cleanExpiredRooms(): void
    {
        try {
            // Buscar salas pending_payment que expiraram (apenas host pendente)
            $expiredRooms = X1RoomInstance::where('status', 'pending_payment')
                ->where('expires_at', '<=', now())
                ->get();

            if ($expiredRooms->isEmpty()) {
                return;
            }

            foreach ($expiredRooms as $room) {
                Log::info("🗑️ Deletando sala X1 expirada #{$room->id} (expirou em {$room->expires_at})");
                
                // Cancelar payments associados
                X1Payment::where('x1_room_id', $room->id)
                    ->update(['status' => 'expired']);
                
                // Deletar sala
                $room->delete();
            }
            
            Log::info("✅ {$expiredRooms->count()} sala(s) X1 expirada(s) deletada(s) automaticamente");
        } catch (\Exception $e) {
            Log::error("❌ Erro ao limpar salas X1 expiradas: {$e->getMessage()}");
        }
    }
}

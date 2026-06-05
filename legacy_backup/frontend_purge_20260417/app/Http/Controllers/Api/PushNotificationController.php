<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushNotificationController extends Controller
{
    public function __construct(
        private PushNotificationService $pushService
    ) {}

    /**
     * Subscrever ao push
     */
    public function subscribe(Request $request)
    {
        try {
            $subscription = $this->pushService->subscribe(
                $request->all(),
                auth()->id(),
                $request->userAgent(),
                $request->ip()
            );

            return response()->json([
                'success' => true,
                'message' => 'Subscrito com sucesso!',
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao subscrever push', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao subscrever',
            ], 500);
        }
    }

    /**
     * Desinscrever do push
     */
    public function unsubscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        $success = $this->pushService->unsubscribe($request->endpoint);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Desinscrito com sucesso' : 'Subscription não encontrada',
        ]);
    }

    /**
     * Enviar notificação para todos (admin only)
     */
    public function sendToAll(Request $request)
    {
        // Verificar se é admin (ajustar conforme sua lógica)
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado',
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'url' => 'nullable|url',
            'icon' => 'nullable|url',
            'image' => 'nullable|url',
        ]);

        $payload = [
            'title' => $request->title,
            'body' => $request->body,
            'url' => $request->url ?? url('/'),
            'icon' => $request->icon ?? asset('assets/images/logo_icon/logo.png'),
            'badge' => asset('assets/images/logo_icon/favicon.png'),
        ];

        if ($request->image) {
            $payload['image'] = $request->image;
        }

        $results = $this->pushService->sendToAll($payload);

        return response()->json([
            'success' => true,
            'message' => 'Notificações enviadas',
            'results' => $results,
        ]);
    }

    /**
     * Enviar notificação para um usuário (admin only)
     */
    public function sendToUser(Request $request, int $userId)
    {
        // Verificar se é admin (ajustar conforme sua lógica)
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado',
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'url' => 'nullable|url',
            'icon' => 'nullable|url',
            'image' => 'nullable|url',
        ]);

        $payload = [
            'title' => $request->title,
            'body' => $request->body,
            'url' => $request->url ?? url('/'),
            'icon' => $request->icon ?? asset('assets/images/logo_icon/logo.png'),
            'badge' => asset('assets/images/logo_icon/favicon.png'),
        ];

        if ($request->image) {
            $payload['image'] = $request->image;
        }

        $results = $this->pushService->sendToUser($userId, $payload);

        return response()->json([
            'success' => true,
            'message' => 'Notificação enviada',
            'results' => $results,
        ]);
    }

    /**
     * Testar notificação (own user)
     */
    public function test(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        }

        $payload = [
            'title' => '🔥 Teste de Notificação',
            'body' => 'Push Notifications configurado e funcionando!',
            'url' => url('/'),
            'icon' => asset('assets/images/logo_icon/logo.png'),
            'badge' => asset('assets/images/logo_icon/favicon.png'),
        ];

        $results = $this->pushService->sendToUser(auth()->id(), $payload);

        return response()->json([
            'success' => true,
            'message' => 'Notificação de teste enviada',
            'results' => $results,
        ]);
    }
}

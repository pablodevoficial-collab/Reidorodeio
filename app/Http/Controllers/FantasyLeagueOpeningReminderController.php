<?php

namespace App\Http\Controllers;

use App\Services\FantasyLeagueOpeningReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FantasyLeagueOpeningReminderController extends Controller
{
    public function store(Request $request, string $slot, FantasyLeagueOpeningReminderService $service): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Faça login para ativar a notificação desse bolão.',
            ], 401);
        }

        if (!$this->hasCompleteProfile($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Complete todo o seu perfil para ativar a notificação desse bolão.',
            ], 422);
        }

        $slotKey = $service->normalizeSlot($slot);
        if (!$slotKey) {
            return response()->json([
                'success' => false,
                'message' => 'Card de bolão inválido para notificação.',
            ], 422);
        }

        if (!$service->canSubscribe($slotKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Esse card já tem bolão aberto no momento. Entre nele agora.',
            ], 422);
        }

        $email = trim((string) ($user->email ?? ''));
        if ($email === '') {
            return response()->json([
                'success' => false,
                'message' => 'Cadastre um e-mail na sua conta para ativar a notificação desse bolão.',
            ], 422);
        }

        try {
            $reminder = $service->subscribe($slotKey, $email, $user);
        } catch (\Throwable $exception) {
            Log::warning('[FantasyLeagueOpeningReminder] Falha ao ativar alerta', [
                'slot_key' => $slotKey,
                'user_id' => $user->id,
                'email' => $email,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Não conseguimos ativar o alerta por agora. Tente novamente em alguns instantes.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => sprintf('Alerta ativado para o card %s. Você vai receber um e-mail quando esse bolão abrir.', $service->slotLabel($slotKey)),
            'data' => [
                'slot_key' => $reminder->slot_key,
                'email' => $reminder->email,
                'name' => $reminder->name,
            ],
        ]);
    }

    private function hasCompleteProfile($user): bool
    {
        $hasRealEmail = method_exists($user, 'hasRealEmail') ? (bool) $user->hasRealEmail() : !empty($user->email);

        return $hasRealEmail
            && trim((string) ($user->username ?? '')) !== ''
            && trim((string) ($user->mobile ?? '')) !== ''
            && !empty($user->birthdate)
            && trim((string) ($user->pix_key ?? '')) !== '';
    }
}
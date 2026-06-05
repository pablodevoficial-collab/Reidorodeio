<?php

namespace App\Http\Controllers;

use App\Models\Rodeio;
use App\Services\RodeioEmailReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RodeioEmailReminderController extends Controller
{
    public function store(Request $request, Rodeio $rodeio, RodeioEmailReminderService $service): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Faça login para ativar a notificação do rodeio.',
            ], 401);
        }

        if (!$this->hasCompleteProfile($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Complete todo o seu perfil para ativar a notificação do rodeio.',
            ], 422);
        }

        if (!$service->canSubscribe($rodeio)) {
            return response()->json([
                'success' => false,
                'message' => 'Esse rodeio já começou. Ative o alerta somente para o próximo evento programado.',
            ], 422);
        }

        $email = trim((string) ($user->email ?? ''));

        if ($email === '') {
            return response()->json([
                'success' => false,
                'message' => 'Cadastre um e-mail na sua conta para ativar a notificação do rodeio.',
            ], 422);
        }

        try {
            $reminder = $service->subscribe($rodeio, $email, $user);
        } catch (\Throwable $exception) {
            Log::warning('[RodeioReminder] Falha ao ativar alerta', [
                'rodeio_id' => $rodeio->id,
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
            'message' => 'Alerta ativado. Você vai receber um e-mail de confirmação e outro quando o rodeio começar.',
            'data' => [
                'rodeio_id' => $reminder->rodeio_id,
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

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfilePhotoRequest;
use App\Services\ProfilePhotoApprovalService;
use Illuminate\Http\Request;
use App\Rules\FileTypeValidate;

class UserProfileController extends Controller
{
    public function __construct(
        private ProfilePhotoApprovalService $profilePhotoApprovalService
    ) {
    }

    public function showProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'ok' => false,
                'message' => 'Usuário não autenticado.',
            ], 401);
        }

        return response()->json([
            'ok' => true,
            'user' => $this->buildProfilePayload($user->fresh()),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'username' => 'required|string|min:3|max:40|alpha_dash|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255',
            'whatsapp' => 'required|string|max:20',
            'birth_date' => 'required|date',
            'pix_key' => 'required|string|max:255',
            'avatar' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png', 'webp']), 'max:2048'],
        ]);

        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->mobile = $validated['whatsapp'];
        $user->birthdate = $validated['birth_date'];
        
        // Auto-detect tip_chave_pix se for CPF, Email, Telefone, ou Aleatoria
        $chave = $validated['pix_key'];
        if (filter_var($chave, FILTER_VALIDATE_EMAIL)) {
            $user->pix_key_type = 'email';
        } else if (preg_match('/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/', $chave) || preg_match('/^\d{11}$/', $chave)) {
            $user->pix_key_type = 'cpf';
        } else if (preg_match('/^\+?\d{10,14}$/', preg_replace('/[^0-9]/', '', $chave))) {
            $user->pix_key_type = 'phone';
        } else {
            $user->pix_key_type = 'random';
        }
        $user->pix_key = $chave;


        $photoQueued = false;
        if ($request->hasFile('avatar')) {
            try {
                $this->profilePhotoApprovalService->submit($user, $request->file('avatar'));
                $photoQueued = true;
            } catch (\Throwable $e) {
                return response()->json([
                    'ok' => false,
                    'message' => $e->getMessage() ?: 'Não foi possível enviar a foto para análise.',
                ], 422);
            }
        }

        $user->save();

        return response()->json([
            'ok' => true,
            'message' => $photoQueued
                ? 'Perfil atualizado. Sua foto foi enviada para análise e você receberá um email com o resultado.'
                : 'Perfil atualizado com sucesso!',
            'photo_pending_review' => $photoQueued,
            'user' => $this->buildProfilePayload($user->fresh()),
        ]);
    }

    private function buildProfilePayload($user): array
    {
        $latestPhotoRequest = null;
        $photoStatus = null;

        if ($user) {
            try {
                $latestPhotoRequest = $user->profilePhotoRequests()->latest('id')->first();
                $photoStatus = $latestPhotoRequest?->status;
            } catch (\Throwable $e) {
                $latestPhotoRequest = null;
                $photoStatus = null;
            }
        }

        $rodeioReminders = [];
        $fantasyReminderSlots = [];

        if ($user) {
            try {
                $rodeioReminders = app(\App\Services\RodeioEmailReminderService::class)
                    ->subscribedRodeioIdsFor($user, $user->email ?? null);
            } catch (\Throwable $e) {
                $rodeioReminders = [];
            }

            try {
                $fantasyReminderSlots = app(\App\Services\FantasyLeagueOpeningReminderService::class)
                    ->subscribedSlotsFor($user, $user->email ?? null);
            } catch (\Throwable $e) {
                $fantasyReminderSlots = [];
            }
        }

        return [
            'id' => $user?->id,
            'username' => $user?->username,
            'email' => $user?->email,
            'mobile' => $user?->mobile,
            'birth_date' => $user?->birthdate ? $user->birthdate->format('Y-m-d') : null,
            'pix_key' => $user?->pix_key,
            'pix_key_type' => $user?->pix_key_type,
            'has_real_email' => $user && method_exists($user, 'hasRealEmail') ? $user->hasRealEmail() : false,
            'profile_complete' => $this->isProfileComplete($user),
            'reminders' => $rodeioReminders,
            'fantasy_reminder_slots' => $fantasyReminderSlots,
            'avatar_url' => $user && !empty($user->image) ? asset('assets/images/user/profile/' . ltrim((string) $user->image, '/')) : null,
            'photo_status' => $photoStatus,
            'photo_pending_review' => $photoStatus === 'pending',
            'photo_reviewed_at' => $latestPhotoRequest?->reviewed_at?->toIso8601String(),
        ];
    }

    private function isProfileComplete($user): bool
    {
        if (!$user) {
            return false;
        }

        $hasRealEmail = method_exists($user, 'hasRealEmail') ? (bool) $user->hasRealEmail() : !empty($user->email);

        return $hasRealEmail
            && trim((string) ($user->username ?? '')) !== ''
            && trim((string) ($user->mobile ?? '')) !== ''
            && !empty($user->birthdate)
            && trim((string) ($user->pix_key ?? '')) !== '';
    }
}

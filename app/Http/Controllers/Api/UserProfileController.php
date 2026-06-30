<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfilePhotoRequest;
use App\Services\ProfilePhotoApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
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
            'firstname' => 'required|string|max:60',
            'lastname' => 'required|string|max:120',
            'cpf' => [
                'required',
                'string',
                'size:11',
                Rule::unique('users', 'cpf')->ignore($user->id),
            ],
            'pix_key' => 'required|string|max:255',
            'avatar' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png', 'webp']), 'max:2048'],
        ], [
            'firstname.required' => 'Informe seu nome completo.',
            'lastname.required' => 'Informe seu nome completo.',
            'cpf.required' => 'Informe seu CPF.',
            'cpf.size' => 'Informe um CPF válido com 11 dígitos.',
            'cpf.unique' => 'Este CPF já está cadastrado em outra conta.',
            'pix_key.required' => 'Informe sua chave Pix.',
        ]);

        $user->firstname = $validated['firstname'];
        $user->lastname = $validated['lastname'];
        $user->cpf = preg_replace('/\D+/', '', (string) $validated['cpf']);
        
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
                : 'Dados do prêmio salvos com sucesso!',
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
            'firstname' => $user?->firstname,
            'lastname' => $user?->lastname,
            'cpf' => $user?->cpf,
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
            'winnings' => $this->buildWinningsPayload($user),
        ];
    }

    private function buildWinningsPayload($user): array
    {
        if (!$user) {
            return [
                'total' => 0.0,
                'fantasy' => 0.0,
                'x1' => 0.0,
            ];
        }

        $fantasy = 0.0;
        $x1 = 0.0;

        try {
            if (Schema::hasTable('fantasy_teams') && Schema::hasColumn('fantasy_teams', 'prize_won')) {
                $query = DB::table('fantasy_teams')
                    ->where('user_id', $user->id)
                    ->where('prize_won', '>', 0);

                if (Schema::hasColumn('fantasy_teams', 'prize_paid_at')) {
                    $query->whereNotNull('prize_paid_at');
                }

                $fantasy = (float) $query->sum('prize_won');
            }
        } catch (\Throwable $e) {
            $fantasy = 0.0;
        }

        try {
            if (Schema::hasTable('user_x1_stats') && Schema::hasColumn('user_x1_stats', 'total_prize_won')) {
                $x1 = (float) DB::table('user_x1_stats')
                    ->where('user_id', $user->id)
                    ->sum('total_prize_won');
            }
        } catch (\Throwable $e) {
            $x1 = 0.0;
        }

        return [
            'total' => round($fantasy + $x1, 2),
            'fantasy' => round($fantasy, 2),
            'x1' => round($x1, 2),
        ];
    }

    private function isProfileComplete($user): bool
    {
        if (!$user) {
            return false;
        }

        return trim((string) ($user->firstname ?? '')) !== ''
            && trim((string) ($user->lastname ?? '')) !== ''
            && trim((string) ($user->cpf ?? '')) !== ''
            && trim((string) ($user->pix_key ?? '')) !== '';
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\ClaimedCompetitorProfileSyncService;
use App\Services\ProfilePhotoApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function __construct(
        private ClaimedCompetitorProfileSyncService $claimedCompetitorProfileSyncService,
        private ProfilePhotoApprovalService $profilePhotoApprovalService
    ) {
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $currentUsername = trim((string) ($user->username ?? ''));
        $requestedUsername = trim((string) $request->input('username', ''));
        $isChangingUsername = $requestedUsername !== '' && $requestedUsername !== $currentUsername;

        $rules = [
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            'firstname' => ['nullable', 'string', 'max:60'],
            'lastname' => ['nullable', 'string', 'max:60'],

            'username' => ['nullable', 'string', 'min:3', 'max:40', 'alpha_dash', 'unique:users,username,' . $user->id],
            'username_confirmation' => [$isChangingUsername ? 'required' : 'nullable', 'string'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,' . $user->id],

            'mobile' => ['nullable', 'string', 'max:30'],
            'cpf' => ['nullable', 'string', 'max:20'],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'show_in_listings' => ['nullable', 'boolean'],
        ];

        $validator = Validator::make($request->all(), $rules, [
            'image.image' => 'A foto de perfil deve ser uma imagem válida.',
            'image.mimes' => 'A foto deve ser JPG, PNG ou WEBP.',
            'image.max' => 'A foto deve ter no máximo 5MB.',
            'username_confirmation.required' => 'Confirme o username para concluir a alteração.',
        ]);

        $validator->after(function ($validator) use ($isChangingUsername, $requestedUsername, $request) {
            if (!$isChangingUsername) {
                return;
            }

            $confirmation = trim((string) $request->input('username_confirmation', ''));
            if ($requestedUsername !== $confirmation) {
                $validator->errors()->add('username', 'A confirmação do username não confere.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $errors = [];

        $labels = [
            'firstname' => 'Primeiro nome',
            'lastname' => 'Sobrenome',
            'username' => 'Username',
            'email' => 'Email',
            'mobile' => 'WhatsApp',
            'cpf' => 'CPF',
            'birthdate' => 'Data de nascimento',
            'pix_key_type' => 'Tipo de Chave PIX',
            'pix_key' => 'Chave PIX',
        ];

        $applyIfUnlocked = function (string $field, $value) use ($user, &$errors, $labels) {
            if ($value === null || $value === '') {
                return;
            }

            $current = $user->{$field} ?? null;
            $currentFilled = is_string($current) ? trim($current) !== '' : !is_null($current);

            if ($currentFilled) {
                // Allow sending the same value (no-op), but block changes.
                if ((string) $current !== (string) $value) {
                    // Campos que qualquer usuário pode alterar sempre
                    $alwaysEditableFields = ['firstname', 'lastname', 'email', 'mobile', 'birthdate', 'pix_key_type', 'pix_key'];
                    
                    if (in_array($field, $alwaysEditableFields)) {
                        // Qualquer usuário pode alterar esses campos
                        $user->{$field} = $value;
                        return;
                    }
                    
                    // Username: liberado para todos
                    if ($field === 'username') {
                        $user->{$field} = $value;
                        return;
                    }

                    // CPF: bloqueado após preenchimento (ninguém pode alterar)
                    $label = $labels[$field] ?? $field;
                    $errors[] = "{$label} já está cadastrado e não pode ser alterado.";
                }

                return;
            }

            $user->{$field} = $value;
        };

        // Normalize values that can be formatted.
        $mobile = $request->filled('mobile') ? preg_replace('/\D+/', '', (string) $request->input('mobile')) : null;
        $cpf = $request->filled('cpf') ? preg_replace('/\D+/', '', (string) $request->input('cpf')) : null;

        if ($cpf !== null && $cpf !== '' && !preg_match('/^\d{11}$/', $cpf)) {
            $errors[] = 'CPF inválido. Informe 11 dígitos.';
        }

        // Verificar se CPF já existe no banco (outro usuário)
        if ($cpf !== null && $cpf !== '' && empty($user->cpf)) {
            $cpfExists = \App\Models\User::where('cpf', $cpf)
                ->where('id', '!=', $user->id)
                ->exists();
            
            if ($cpfExists) {
                $errors[] = 'Este CPF já está cadastrado em outra conta.';
            }
        }

        if ($mobile !== null && $mobile !== '' && !preg_match('/^\d{8,15}$/', $mobile)) {
            $errors[] = 'WhatsApp inválido. Informe apenas números (8 a 15 dígitos).';
        }

        $applyIfUnlocked('firstname', $request->input('firstname'));
        $applyIfUnlocked('lastname', $request->input('lastname'));
        $applyIfUnlocked('username', $request->input('username'));
        $applyIfUnlocked('email', $request->input('email'));
        $applyIfUnlocked('mobile', $mobile);
        $applyIfUnlocked('cpf', $cpf);
        $applyIfUnlocked('birthdate', $request->input('birthdate'));
        $applyIfUnlocked('pix_key_type', $request->input('pix_key_type'));
        $applyIfUnlocked('pix_key', $request->input('pix_key'));
        $user->show_in_listings = $request->boolean('show_in_listings');

        $photoQueued = false;
        if ($request->hasFile('image')) {
            try {
                $this->profilePhotoApprovalService->submit($user, $request->file('image'));
                $photoQueued = true;
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'errors' => [$e->getMessage() ?: 'Não foi possível enviar a foto de perfil.'],
                ], 422);
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'errors' => $errors,
            ], 422);
        }

        $user->save();
        $this->claimedCompetitorProfileSyncService->syncFromUser($user->fresh());

        return response()->json([
            'success' => true,
            'message' => $photoQueued
                ? 'Perfil atualizado. Sua foto foi enviada para análise e você receberá um email com o resultado.'
                : 'Perfil atualizado com sucesso.',
            'photo_pending_review' => $photoQueued,
            'user' => [
                'username' => $user->username,
                'email' => $user->email,
                'firstname' => $user->firstname ?? null,
                'lastname' => $user->lastname ?? null,
                'mobile' => $user->mobile ?? null,
                'cpf' => $user->cpf ?? null,
                'birthdate' => $user->birthdate ?? null,
                'pix_key_type' => $user->pix_key_type ?? null,
                'pix_key' => $user->pix_key ?? null,
                'show_in_listings' => (bool) $user->show_in_listings,
                'avatar' => $user->image ? asset(getFilePath('userProfile') . '/' . $user->image) : null,
            ],
            'locked' => [
                'firstname' => false, // Qualquer um pode alterar
                'lastname' => false, // Qualquer um pode alterar
                'username' => false, // Qualquer um pode alterar
                'email' => false, // Qualquer um pode alterar
                'mobile' => false, // Qualquer um pode alterar
                'cpf' => !empty($user->cpf), // Bloqueado após preenchimento
                'birthdate' => false, // Qualquer um pode alterar
                'pix_key_type' => false, // Sempre editável
                'pix_key' => false, // Sempre editável
            ],
        ]);
    }

    public function toggleListings(Request $request)
    {
        $user = $request->user();
        $user->show_in_listings = !$user->show_in_listings;
        $user->save();

        return response()->json([
            'success' => true,
            'show_in_listings' => $user->show_in_listings,
            'message' => $user->show_in_listings
                ? 'Seu usuário agora aparece em listas públicas.'
                : 'Seu usuário foi ocultado das listas públicas.',
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], 401);
        }

        $oldImage = $user->image;
        $suffix = $user->id . '_' . now()->timestamp;

        DB::beginTransaction();
        try {
            $user->status = 0;
            $user->firstname = 'Conta';
            $user->lastname = 'Excluida';
            $user->username = 'deleted_user_' . $suffix;
            $user->email = 'deleted_' . $suffix . '@deleted.local';
            $user->mobile = null;
            $user->cpf = null;
            $user->birthdate = null;
            $user->image = null;
            $user->provider = null;
            $user->provider_id = null;
            $user->pix_key_type = null;
            $user->pix_key = null;
            $user->password = Hash::make(Str::random(40));
            $user->current_session_id = null;

            if (array_key_exists('show_in_listings', $user->getAttributes())) {
                $user->show_in_listings = false;
            }

            $user->save();

            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível excluir a conta agora. Tente novamente.',
            ], 500);
        }

        if (!empty($oldImage)) {
            $path = public_path(getFilePath('userProfile') . '/' . $oldImage);
            if (is_file($path)) {
                @unlink($path);
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Conta excluída com sucesso.',
            'redirect' => route('home'),
        ]);
    }

    public function checkUsername(Request $request)
    {
        $username = $request->input('username');
        $currentUserId = $request->user()->id;

        if (empty($username) || strlen($username) < 3) {
            return response()->json([
                'available' => false,
                'message' => 'Username deve ter no mínimo 3 caracteres'
            ]);
        }

        // Verifica se o username já existe (excluindo o próprio usuário)
        $exists = \App\Models\User::where('username', $username)
            ->where('id', '!=', $currentUserId)
            ->exists();

        if ($exists) {
            return response()->json([
                'available' => false,
                'message' => 'Username já está em uso'
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'Username disponível'
        ]);
    }

    public function checkCpf(Request $request)
    {
        $cpf = $request->input('cpf');
        $currentUserId = $request->user()->id;

        // Remove formatação
        $cpf = preg_replace('/\D+/', '', (string) $cpf);

        if (empty($cpf) || strlen($cpf) !== 11) {
            return response()->json([
                'available' => false,
                'message' => 'CPF deve ter 11 dígitos'
            ]);
        }

        // Verifica se o CPF já existe (excluindo o próprio usuário)
        $exists = \App\Models\User::where('cpf', $cpf)
            ->where('id', '!=', $currentUserId)
            ->exists();

        if ($exists) {
            return response()->json([
                'available' => false,
                'message' => 'CPF já cadastrado em outra conta'
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'CPF disponível'
        ]);
    }
}

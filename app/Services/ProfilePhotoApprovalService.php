<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AdminNotification;
use App\Models\ProfilePhotoRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProfilePhotoApprovalService
{
    private const ADMIN_EMAIL = 'reidorodeio.host@gmail.com';

    public function __construct(
        private ClaimedCompetitorProfileSyncService $claimedCompetitorProfileSyncService
    ) {
    }

    public function submit(User $user, UploadedFile $file): ProfilePhotoRequest
    {
        $this->assertValidImagePayload($file);

        $imageInfo = @getimagesize($file->getRealPath());
        if ($imageInfo === false) {
            throw new RuntimeException('Não foi possível validar a imagem enviada.');
        }

        $pending = ProfilePhotoRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        if ($pending?->image_path) {
            Storage::disk('public')->delete($pending->image_path);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $filename = 'profile-photo-request-' . $user->id . '-' . Str::uuid() . '.' . $extension;
        $path = $file->storeAs('profile-photo-requests', $filename, 'public');

        $photoRequest = $pending ?: new ProfilePhotoRequest([
            'user_id' => $user->id,
        ]);

        $photoRequest->fill([
            'status' => 'pending',
            'image_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'width' => $imageInfo[0] ?? null,
            'height' => $imageInfo[1] ?? null,
            'admin_notes' => null,
            'approved_by_admin_id' => null,
            'reviewed_at' => null,
        ])->save();

        $this->notifyAdmin($user, $photoRequest);
        $this->notifyUserSubmission($user, $photoRequest);

        return $photoRequest;
    }

    public function approve(ProfilePhotoRequest $photoRequest, ?Admin $admin = null, ?string $notes = null): ProfilePhotoRequest
    {
        $updatedRequest = DB::transaction(function () use ($photoRequest, $admin, $notes) {
            /** @var ProfilePhotoRequest $lockedRequest */
            $lockedRequest = ProfilePhotoRequest::query()
                ->with('user')
                ->lockForUpdate()
                ->findOrFail($photoRequest->id);

            if ($lockedRequest->status === 'approved') {
                return $lockedRequest;
            }

            $user = User::query()->lockForUpdate()->findOrFail($lockedRequest->user_id);
            $sourcePath = Storage::disk('public')->path($lockedRequest->image_path);

            if (!is_file($sourcePath)) {
                throw new RuntimeException('A imagem pendente não foi encontrada para aprovação.');
            }

            $destinationPath = public_path('assets/images/user/profile');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $extension = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'jpg';
            $filename = uniqid('approved_', true) . '.' . $extension;
            $targetPath = $destinationPath . DIRECTORY_SEPARATOR . $filename;

            if (!@copy($sourcePath, $targetPath)) {
                throw new RuntimeException('Não foi possível publicar a foto aprovada.');
            }

            if ($user->image) {
                $currentPath = $destinationPath . DIRECTORY_SEPARATOR . $user->image;
                if (is_file($currentPath)) {
                    @unlink($currentPath);
                }
            }

            $user->image = $filename;
            $user->save();

            $lockedRequest->forceFill([
                'status' => 'approved',
                'approved_by_admin_id' => $admin?->id,
                'admin_notes' => $notes,
                'reviewed_at' => now(),
            ])->save();

            ProfilePhotoRequest::query()
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->where('id', '!=', $lockedRequest->id)
                ->get()
                ->each(function (ProfilePhotoRequest $pendingItem) {
                    if ($pendingItem->image_path) {
                        Storage::disk('public')->delete($pendingItem->image_path);
                    }
                    $pendingItem->delete();
                });

            $this->claimedCompetitorProfileSyncService->syncFromUser($user->fresh());

            return $lockedRequest->fresh(['user', 'approvedByAdmin']);
        });

        $this->notifyUserDecision($updatedRequest->user, $updatedRequest, true);

        return $updatedRequest;
    }

    public function reject(ProfilePhotoRequest $photoRequest, ?Admin $admin = null, ?string $notes = null): ProfilePhotoRequest
    {
        $photoRequest->forceFill([
            'status' => 'rejected',
            'approved_by_admin_id' => $admin?->id,
            'admin_notes' => $notes,
            'reviewed_at' => now(),
        ])->save();

        $updatedRequest = $photoRequest->fresh(['user', 'approvedByAdmin']);
        $this->notifyUserDecision($updatedRequest->user, $updatedRequest, false);

        return $updatedRequest;
    }

    private function assertValidImagePayload(UploadedFile $file): void
    {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $mime = strtolower((string) $file->getMimeType());

        if (!in_array($mime, $allowedMimeTypes, true)) {
            throw new RuntimeException('Formato de foto inválido. Envie JPG, PNG ou WEBP.');
        }
    }

    private function notifyAdmin(User $user, ProfilePhotoRequest $photoRequest): void
    {
        try {
            $notification = new AdminNotification();
            $notification->user_id = $user->id;
            $notification->title = 'Nova foto de perfil aguardando aprovação';
            $notification->click_url = route('admin.users.profile_photos.index', ['status' => 'pending']);
            $notification->save();
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            $this->deliverEmail(
                'emails.profile_photo.admin_new_request',
                [
                'user' => $user,
                'photoRequest' => $photoRequest,
                'approvalUrl' => route('admin.users.profile_photos.index', ['status' => 'pending']),
                ],
                self::ADMIN_EMAIL,
                'Admin Rei do Rodeio',
                'Nova foto de perfil aguardando aprovação'
            );
        } catch (\Throwable $e) {
            Log::warning('[ProfilePhotoApproval] Falha ao enviar email para admin', [
                'user_id' => $user->id,
                'email' => self::ADMIN_EMAIL,
                'error' => $e->getMessage(),
            ]);
            report($e);
        }
    }

    private function notifyUserSubmission(User $user, ProfilePhotoRequest $photoRequest): void
    {
        if (!$user->hasRealEmail()) {
            Log::info('[ProfilePhotoApproval] Email de submissao pulado por email invalido', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
            return;
        }

        try {
            $this->deliverEmail(
                'emails.profile_photo.user_submitted',
                [
                'user' => $user,
                'photoRequest' => $photoRequest,
                ],
                (string) $user->email,
                (string) ($user->fullname ?: $user->firstname ?: $user->username ?: 'Competidor'),
                'Recebemos sua foto de perfil para analise'
            );
        } catch (\Throwable $e) {
            Log::warning('[ProfilePhotoApproval] Falha ao enviar email de submissao', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            report($e);
        }
    }

    private function notifyUserDecision(User $user, ProfilePhotoRequest $photoRequest, bool $approved): void
    {
        if (!$user->hasRealEmail()) {
            Log::info('[ProfilePhotoApproval] Email de decisao pulado por email invalido', [
                'user_id' => $user->id,
                'email' => $user->email,
                'approved' => $approved,
            ]);
            return;
        }

        try {
            $this->deliverEmail(
                'emails.profile_photo.user_decision',
                [
                'user' => $user,
                'photoRequest' => $photoRequest,
                'approved' => $approved,
                ],
                (string) $user->email,
                (string) ($user->fullname ?: $user->firstname ?: $user->username ?: 'Competidor'),
                $approved ? 'Sua foto de perfil foi aprovada' : 'Sua foto de perfil nao foi aprovada'
            );
        } catch (\Throwable $e) {
            Log::warning('[ProfilePhotoApproval] Falha ao enviar email de decisao', [
                'user_id' => $user->id,
                'email' => $user->email,
                'approved' => $approved,
                'error' => $e->getMessage(),
            ]);
            report($e);
        }
    }

    private function deliverEmail(string $view, array $data, string $toEmail, string $toName, string $subject): void
    {
        $email = trim($toEmail);
        if ($email === '') {
            throw new RuntimeException('O destinatario do email nao foi informado.');
        }

        $fromAddress = (string) (gs('email_from') ?: config('mail.from.address'));
        $fromName = (string) (gs('email_from_name') ?: gs('site_name') ?: config('mail.from.name'));

        if ($fromAddress === '') {
            throw new RuntimeException('O endereco remetente de email nao esta configurado.');
        }

        Mail::send($view, $data, function ($message) use ($email, $toName, $subject, $fromAddress, $fromName) {
            $message->to($email, $toName)
                ->subject($subject)
                ->from($fromAddress, $fromName);
        });
    }
}

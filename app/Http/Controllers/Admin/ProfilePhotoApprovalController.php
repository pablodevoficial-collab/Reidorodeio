<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfilePhotoRequest;
use App\Services\ProfilePhotoApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfilePhotoApprovalController extends Controller
{
    public function __construct(
        private ProfilePhotoApprovalService $profilePhotoApprovalService
    ) {
    }

    public function index(Request $request): View
    {
        $pageTitle = 'Aprovar Fotos';
        $status = trim((string) $request->input('status', 'pending'));

        $requests = ProfilePhotoRequest::query()
            ->with(['user', 'approvedByAdmin'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending' => ProfilePhotoRequest::query()->where('status', 'pending')->count(),
            'approved' => ProfilePhotoRequest::query()->where('status', 'approved')->count(),
            'rejected' => ProfilePhotoRequest::query()->where('status', 'rejected')->count(),
        ];

        return view('admin.users.profile_photos.index', compact('pageTitle', 'requests', 'counts', 'status'));
    }

    public function approve(Request $request, ProfilePhotoRequest $profilePhotoRequest): RedirectResponse
    {
        if ($profilePhotoRequest->status === 'approved') {
            return back()->withNotify([['info', 'Essa foto já foi aprovada.']]);
        }

        $notes = trim((string) $request->input('admin_notes', ''));
        $this->profilePhotoApprovalService->approve($profilePhotoRequest, auth('admin')->user(), $notes !== '' ? $notes : null);

        return back()->withNotify([['success', 'Foto aprovada e publicada no perfil do usuário.']]);
    }

    public function reject(Request $request, ProfilePhotoRequest $profilePhotoRequest): RedirectResponse
    {
        if ($profilePhotoRequest->status === 'approved') {
            return back()->withNotify([['error', 'Uma foto já aprovada não pode ser rejeitada.']]);
        }

        $notes = trim((string) $request->input('admin_notes', ''));
        $this->profilePhotoApprovalService->reject($profilePhotoRequest, auth('admin')->user(), $notes !== '' ? $notes : 'Foto rejeitada na moderação.');

        return back()->withNotify([['success', 'Foto rejeitada e usuário notificado.']]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreProductSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreProductSubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $pageTitle = 'Loja • Produtos enviados';
        $status = (string) $request->input('status', '');
        $search = trim((string) $request->input('search', ''));
        $emptyMessage = 'Nenhum produto enviado encontrado.';

        $query = StoreProductSubmission::query()
            ->with('user')
            ->latest('id');

        if (in_array($status, ['pending', 'approved', 'rejected', 'archived'], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('username', 'like', '%' . $search . '%')
                            ->orWhere('firstname', 'like', '%' . $search . '%')
                            ->orWhere('lastname', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        $submissions = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => StoreProductSubmission::query()->count(),
            'pending' => StoreProductSubmission::query()->where('status', 'pending')->count(),
            'approved' => StoreProductSubmission::query()->where('status', 'approved')->count(),
            'rejected' => StoreProductSubmission::query()->where('status', 'rejected')->count(),
        ];

        return view('admin.store_submissions.index', compact(
            'pageTitle',
            'submissions',
            'stats',
            'status',
            'search',
            'emptyMessage'
        ));
    }

    public function review(Request $request, StoreProductSubmission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected,archived'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $submission->status = (string) $validated['status'];
        $submission->admin_notes = $validated['admin_notes'] ?? null;
        $submission->reviewed_at = now();
        $submission->save();

        $notify[] = ['success', 'Produto da loja atualizado com sucesso.'];

        return back()->withNotify($notify);
    }
}

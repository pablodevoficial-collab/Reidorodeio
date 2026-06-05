<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competitor;
use App\Models\CompetitorStat;
use App\Models\User;
use App\Services\ClaimedCompetitorProfileSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class CompetitorController extends Controller
{
    public function __construct(
        private ClaimedCompetitorProfileSyncService $claimedCompetitorProfileSyncService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageTitle = 'Competidores';
        $query = Competitor::with('stats');
        $search = trim((string) $request->input('q', ''));
        if ($search !== '') {
            $query->where('nome', 'like', '%' . $search . '%');
        }
        $competitors = $query->paginate(15);
        return view('admin.competitors.index', compact('competitors', 'pageTitle'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pageTitle = 'Criar Competidor';
        return view('admin.competitors.create', compact('pageTitle'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'biografia' => 'nullable|string|max:1000',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'status' => ['required', Rule::in(['ativo', 'inativo'])],
            'nivel' => ['required', Rule::in(['favorito', 'elite', 'ascendente', 'competidor'])],
        ]);

        $validated['profile_claimed'] = $request->boolean('profile_claimed');

        // Foto principal - salvar diretamente em public/storage/competitors
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = $file->hashName();
            $file->move(public_path('storage/competitors'), $filename);
            $validated['foto'] = 'competitors/' . $filename;
        }

        // Remover campos que não existem mais
        unset($validated['status_ext'], $validated['historico_rodeios'], $validated['tags']);

        $competitor = Competitor::create($validated);

        // Criar estatísticas iniciais
        CompetitorStat::create([
            'competitor_id' => $competitor->id
        ]);

        return redirect()->route('admin.competitors.index')
            ->with('notify', [['success', 'Competidor criado com sucesso!']]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Competitor $competitor)
    {
        $pageTitle = 'Detalhes do Competidor';
        $competitor->load('stats');
        return view('admin.competitors.show', compact('competitor', 'pageTitle'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Competitor $competitor)
    {
        $pageTitle = 'Editar Competidor';
        $competitor->load('claimedUser');
        return view('admin.competitors.edit', compact('competitor', 'pageTitle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Competitor $competitor)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'biografia' => 'nullable|string|max:1000',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'status' => ['required', Rule::in(['ativo', 'inativo'])],
            'nivel' => ['required', Rule::in(['favorito', 'elite', 'ascendente', 'competidor'])],
        ]);

        // Foto principal - salvar diretamente em public/storage/competitors
        if ($request->hasFile('foto')) {
            // Delete old photo
            if ($competitor->foto) {
                $oldPath = public_path('storage/' . $competitor->foto);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $file = $request->file('foto');
            $filename = $file->hashName();
            $file->move(public_path('storage/competitors'), $filename);
            $validated['foto'] = 'competitors/' . $filename;
        } elseif ($request->input('delete_foto') == '1') {
            // Excluir foto atual se marcada para exclusão
            if ($competitor->foto) {
                $oldPath = public_path('storage/' . $competitor->foto);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
                $validated['foto'] = null;
            }
        }

        // Remover campos que não existem mais
        unset($validated['status_ext'], $validated['historico_rodeios'], $validated['tags']);

        $validated['profile_claimed'] = $request->boolean('profile_claimed');

        DB::transaction(function () use ($request, $competitor, &$validated) {
            if ($validated['profile_claimed']) {
                $linkedUser = $this->syncClaimedUser($request, $competitor);
                $validated['claimed_user_id'] = $linkedUser->id;
            } else {
                $validated['claimed_user_id'] = null;
            }

            $competitor->update($validated);

            if ($validated['profile_claimed']) {
                $this->claimedCompetitorProfileSyncService->syncFromCompetitor($competitor);
            }
        });

        $competitor->refresh();

        if ($request->expectsJson()) {
            $badgeHtml = match ($competitor->nivel) {
                'favorito' => '<i class="las la-star"></i> Favorito',
                'elite' => '<i class="las la-trophy"></i> Elite',
                'legado', 'ascendente' => '<i class="las la-arrow-up"></i> Ascendente',
                'presilha', 'competidor' => '<i class="las la-user"></i> Competidor',
                default => e(ucfirst($competitor->nivel ?? '-')),
            };

            return response()->json([
                'success' => true,
                'message' => 'Competidor atualizado com sucesso!',
                'competitor' => [
                    'id' => $competitor->id,
                    'nome' => $competitor->nome,
                    'nivel' => $competitor->nivel,
                    'status' => $competitor->status,
                    'biografia' => $competitor->biografia,
                    'foto_url' => $competitor->foto_url,
                ],
                'badge_html' => $badgeHtml,
            ]);
        }

        return redirect()->route('admin.competitors.index')
            ->with('notify', [['success', 'Competidor atualizado com sucesso!']]);
    }

    protected function syncClaimedUser(Request $request, Competitor $competitor): User
    {
        $linkedUser = $competitor->claimedUser;
        $payload = $request->all();
        $payload['claimed_user_cpf'] = preg_replace('/\D/', '', (string) ($payload['claimed_user_cpf'] ?? ''));

        $validator = Validator::make($payload, [
            'claimed_user_firstname' => ['required', 'string', 'max:40'],
            'claimed_user_lastname' => ['required', 'string', 'max:40'],
            'claimed_user_username' => [
                'required',
                'string',
                'min:3',
                'max:40',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($linkedUser?->id),
            ],
            'claimed_user_email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($linkedUser?->id),
            ],
            'claimed_user_mobile' => ['nullable', 'string', 'max:40'],
            'claimed_user_cpf' => [
                'required',
                'string',
                'max:14',
                Rule::unique('users', 'cpf')->ignore($linkedUser?->id),
            ],
            'claimed_user_birthdate' => ['required', 'date', 'before_or_equal:' . now()->subYears(18)->format('Y-m-d')],
            'claimed_user_password' => [$linkedUser ? 'nullable' : 'required', 'confirmed', 'min:6'],
            'claimed_user_status' => ['required', 'in:0,1'],
            'claimed_user_ev' => ['nullable', 'boolean'],
            'claimed_user_sv' => ['nullable', 'boolean'],
            'claimed_user_show_in_listings' => ['nullable', 'boolean'],
        ], [
            'claimed_user_firstname.required' => 'Informe o nome do usuário vinculado.',
            'claimed_user_lastname.required' => 'Informe o sobrenome do usuário vinculado.',
            'claimed_user_username.required' => 'Informe o username do usuário vinculado.',
            'claimed_user_username.unique' => 'Este username já está em uso por outro usuário.',
            'claimed_user_email.required' => 'Informe o email do usuário vinculado.',
            'claimed_user_email.unique' => 'Este email já está em uso por outro usuário.',
            'claimed_user_cpf.required' => 'Informe o CPF do usuário vinculado.',
            'claimed_user_cpf.unique' => 'Este CPF já está em uso por outro usuário.',
            'claimed_user_birthdate.required' => 'Informe a data de nascimento do usuário vinculado.',
            'claimed_user_birthdate.before_or_equal' => 'O usuário vinculado precisa ter 18 anos ou mais.',
            'claimed_user_password.required' => 'Defina uma senha para o usuário vinculado.',
            'claimed_user_password.confirmed' => 'A confirmação da senha do usuário vinculado não confere.',
            'claimed_user_password.min' => 'A senha do usuário vinculado deve ter no mínimo 6 caracteres.',
        ]);

        $validator->after(function ($validator) use ($payload) {
            $cpf = (string) ($payload['claimed_user_cpf'] ?? '');
            if (strlen($cpf) !== 11) {
                $validator->errors()->add('claimed_user_cpf', 'O CPF do usuário vinculado deve ter 11 dígitos.');
            }
        });

        $validated = $validator->validate();
        $sanitizedCpf = (string) $validated['claimed_user_cpf'];

        $user = $linkedUser ?? new User();
        $user->firstname = $validated['claimed_user_firstname'];
        $user->lastname = $validated['claimed_user_lastname'];
        $user->username = $validated['claimed_user_username'];
        $user->email = $validated['claimed_user_email'];
        $user->mobile = $validated['claimed_user_mobile'] ?? null;
        $user->cpf = $sanitizedCpf;
        $user->birthdate = $validated['claimed_user_birthdate'];
        $user->status = (int) $validated['claimed_user_status'];
        $user->ev = $request->boolean('claimed_user_ev') ? 1 : 0;
        $user->sv = $request->boolean('claimed_user_sv') ? 1 : 0;
        $user->show_in_listings = $request->boolean('claimed_user_show_in_listings');
        $user->ts = $user->ts ?? 0;
        $user->tv = $user->tv ?? 1;

        if (!empty($validated['claimed_user_password'])) {
            $user->password = Hash::make($validated['claimed_user_password']);
        }

        $user->save();

        return $user;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Competitor $competitor)
    {
        $competitor->stats()->delete();
        $competitor->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Competidor excluído com sucesso!'
            ]);
        }

        return redirect()->route('admin.competitors.index')
            ->with('notify', [['success', 'Competidor excluído com sucesso!']]);
    }

    /**
     * Update competitor statistics
     */
    public function updateStats(Request $request, Competitor $competitor)
    {
        $validated = $request->validate([
            'vitorias' => 'required|integer|min:0',
            'derrotas' => 'required|integer|min:0',
            'empates' => 'required|integer|min:0',
            'pontuacao_media' => 'nullable|numeric|min:0'
        ]);

        $competitor->stats()->updateOrCreate(
            ['competitor_id' => $competitor->id],
            collect($validated)->only(['vitorias','derrotas','empates','pontuacao_media'])->toArray()
        );

        return redirect()->back()
            ->with('notify', [['success', 'Estatísticas atualizadas com sucesso!']]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rodeio;
use App\Models\Modalidade;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\JsonResponse;

class RodeioController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Rodeios';
        $query = Rodeio::with('modalidades');
        $search = trim((string) $request->input('q', ''));
        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }
        $status = (string) $request->input('status', '');
        if ($status !== '') {
            $query->where('status', $status);
        }

        $rodeios = $query->orderByDesc('id')->paginate(15)->appends($request->only('q', 'status'));
        return view('admin.rodeios.index', compact('rodeios', 'pageTitle'));
    }

    public function create()
    {
        $pageTitle = 'Criar Rodeio';
        return view('admin.rodeios.create', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cidade' => 'required|string|max:100',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'status' => 'required|in:ativo,inativo',
            'descricao' => 'nullable|string',
            'imagem' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = [
            'name' => $validated['nome'],
            'start' => Carbon::parse($validated['data_inicio']),
            'end' => Carbon::parse($validated['data_fim']),
            'status' => $validated['status'],
            'info' => [
                'cidade' => $validated['cidade'],
                'descricao' => $validated['descricao'] ?? null,
            ]
        ];

        if (Schema::hasColumn('rodeios', 'status_transmissao') && $validated['status'] === 'ativo') {
            $data['status_transmissao'] = 'programado';
        }

        if ($request->hasFile('imagem')) {
            $data['logo'] = $request->file('imagem')->store('rodeios', 'public');
        }

        $rodeio = Rodeio::create($data);

        return redirect()->route('admin.rodeios.index')->with('notify', [['success', 'Rodeio criado com sucesso!']]);
    }

    public function edit(Rodeio $rodeio)
    {
        $pageTitle = 'Editar Rodeio';
        return view('admin.rodeios.edit', compact('rodeio', 'pageTitle'));
    }

    public function update(Request $request, Rodeio $rodeio)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cidade' => 'required|string|max:100',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'status' => 'required|in:ativo,inativo',
            'descricao' => 'nullable|string',
            'imagem' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = [
            'name' => $validated['nome'],
            'start' => Carbon::parse($validated['data_inicio']),
            'end' => Carbon::parse($validated['data_fim']),
            'status' => $validated['status'],
            'info' => [
                'cidade' => $validated['cidade'],
                'descricao' => $validated['descricao'] ?? null,
            ]
        ];

        if (Schema::hasColumn('rodeios', 'status_transmissao')
            && $validated['status'] === 'ativo'
            && blank($rodeio->status_transmissao)
        ) {
            $data['status_transmissao'] = 'programado';
        }

        if ($request->hasFile('imagem')) {
            if ($rodeio->logo) {
                Storage::disk('public')->delete($rodeio->logo);
            }
            $data['logo'] = $request->file('imagem')->store('rodeios', 'public');
        }

        $rodeio->update($data);

        return redirect()->route('admin.rodeios.index')->with('notify', [['success', 'Rodeio atualizado com sucesso!']]);
    }

    public function destroy(Rodeio $rodeio)
    {
        if ($rodeio->logo) {
            Storage::disk('public')->delete($rodeio->logo);
        }
        $rodeio->delete();
        return redirect()->route('admin.rodeios.index')->with('notify', [['success', 'Rodeio removido com sucesso!']]);
    }

    public function show(Rodeio $rodeio)
    {
        $pageTitle = 'Detalhes do Rodeio';
        $rodeio->load('modalidades');
        return view('admin.rodeios.show', compact('rodeio', 'pageTitle'));
    }

    /**
     * Endpoint usado por alguns templates antigos do admin para popular selects.
     * Retorna uma lista simples de rodeios como "categorias".
     */
    public function fetchCategories(Request $request): JsonResponse
    {
        $term = (string) $request->input('q', '');

        $query = Rodeio::query()->select(['id', 'name'])->orderBy('name');
        if ($term !== '') {
            $query->where('name', 'like', '%' . $term . '%');
        }

        $items = $query->limit(50)->get()->map(fn ($r) => [
            'id' => $r->id,
            'text' => $r->name,
        ])->values();

        return response()->json([
            'results' => $items,
        ]);
    }
}

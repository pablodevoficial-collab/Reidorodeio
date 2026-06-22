<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SponsorController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Patrocinadores';
        $search = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');

        $query = Sponsor::query();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('url', 'like', '%' . $search . '%');
            });
        }

        if ($status !== '') {
            $query->where('is_active', $status === 'active');
        }

        $sponsors = $query
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(15)
            ->appends($request->only('q', 'status'));

        return view('admin.sponsors.index', compact('pageTitle', 'sponsors', 'search', 'status'));
    }

    public function create()
    {
        $pageTitle = 'Novo Patrocinador';

        return view('admin.sponsors.create', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSponsor($request, true);

        $validated['logo'] = $request->file('logo')->store('sponsors', 'public');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        Sponsor::create($validated);

        return redirect()
            ->route('admin.sponsors.index')
            ->with('notify', [['success', 'Patrocinador criado com sucesso!']]);
    }

    public function edit(Sponsor $sponsor)
    {
        $pageTitle = 'Editar Patrocinador';

        return view('admin.sponsors.edit', compact('pageTitle', 'sponsor'));
    }

    public function update(Request $request, Sponsor $sponsor)
    {
        $validated = $this->validateSponsor($request, false);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        if ($request->hasFile('logo')) {
            if ($sponsor->logo) {
                Storage::disk('public')->delete($sponsor->logo);
            }

            $validated['logo'] = $request->file('logo')->store('sponsors', 'public');
        }

        $sponsor->update($validated);

        return redirect()
            ->route('admin.sponsors.index')
            ->with('notify', [['success', 'Patrocinador atualizado com sucesso!']]);
    }

    public function destroy(Sponsor $sponsor)
    {
        if ($sponsor->logo) {
            Storage::disk('public')->delete($sponsor->logo);
        }

        $sponsor->delete();

        return redirect()
            ->route('admin.sponsors.index')
            ->with('notify', [['success', 'Patrocinador removido com sucesso!']]);
    }

    private function validateSponsor(Request $request, bool $logoRequired): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'url' => ['required', 'url', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['nullable', 'boolean'],
            'logo' => [$logoRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }
}

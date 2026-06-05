@extends('admin.layouts.app')
@section('panel')
<div class="modalidades-groups-wrapper">
    <div class="modalidades-groups-header">
        <div class="modalidades-groups-header__content">
            <h5 class="modalidades-groups-header__title">
                <i class="la la-layer-group"></i> Grupos da Modalidade
            </h5>
            <div class="modalidades-groups-header__meta">
                <span><i class="la la-tag"></i> {{ $modalidade->nome }}</span>
                <span><i class="la la-users"></i> {{ $tipoLabel }}</span>
                @if($modalidade->tem_divisoes)
                    <span><i class="la la-sitemap"></i> {{ implode(', ', $modalidade->divisoes_nomes ?? []) }}</span>
                @endif
            </div>
        </div>
        <a class="btn btn-sm btn-outline--primary modalidades-groups-back" href="{{ route('admin.modalidades.index') }}">
            <i class="la la-arrow-left"></i> Voltar
        </a>
    </div>

    @if(($modalidade->tamanho_equipe ?? 1) <= 1)
        <div class="modalidades-groups-alert">
            <i class="la la-info-circle"></i>
            Esta modalidade é individual. Grupos não são necessários.
        </div>
    @endif

    @if($modalidade->tem_divisoes)
        <form method="GET" class="modalidades-groups-filter">
            <label class="modalidades-groups-filter__label">Filtrar por divisão</label>
            <select name="divisao" class="form-control modalidades-groups-filter__select" onchange="this.form.submit()">
                <option value="">Todas</option>
                @foreach(($modalidade->divisoes_nomes ?? []) as $div)
                    <option value="{{ $div }}" {{ ($divisao ?? '') === $div ? 'selected' : '' }}>{{ $div }}</option>
                @endforeach
            </select>
        </form>
    @endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="modalidades-groups-card">
                <div class="modalidades-groups-card__header">
                    <div>
                        <h6 class="modalidades-groups-card__title">Criar grupo</h6>
                        <div class="modalidades-groups-card__hint">
                            Selecione exatamente {{ $modalidade->tamanho_equipe ?? 1 }} competidores.
                        </div>
                    </div>
                </div>
                <div class="modalidades-groups-card__body">
                    <form action="{{ route('admin.modalidades.groups.store', $modalidade->id) }}" method="POST">
                        @csrf
                        <input type="hidden" id="groupTeamSize" value="{{ $modalidade->tamanho_equipe ?? 1 }}">
                        <div class="row g-3">
                            @if($modalidade->tem_divisoes)
                                <div class="col-md-6" id="groupDivisionField">
                                    <label class="form-label">Divisão *</label>
                                    <select name="divisao" class="form-control" id="groupDivisaoSelect" required>
                                        <option value="">Selecione</option>
                                        @foreach(($modalidade->divisoes_nomes ?? []) as $div)
                                            <option value="{{ $div }}" {{ ($divisao ?? '') === $div ? 'selected' : '' }}>{{ $div }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <label class="form-label">Nome do grupo (opcional)</label>
                                <input type="text" name="nome" class="form-control" value="{{ old('nome') }}">
                            </div>
                            <div class="col-12">
                                <label class="modalidades-classificatoria">
                                    <input type="checkbox" name="is_classificatoria" id="groupIsClassificatoria" value="1" @checked(old('is_classificatoria'))>
                                    <span>
                                        Classificatória (não exigir divisão)
                                        <small>Use quando o grupo ainda está na fase classificatória.</small>
                                    </span>
                                </label>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Competidores</label>
                                <div class="modalidades-group-summary">
                                    <span>Selecionados</span>
                                    <strong id="selectedCount">0 / {{ $modalidade->tamanho_equipe ?? 1 }}</strong>
                                </div>
                                <div class="input-group modalidades-groups-search">
                                    <span class="input-group-text"><i class="la la-search"></i></span>
                                    <input type="text" id="competitorSearch" class="form-control" placeholder="Pesquisar competidor...">
                                </div>
                                <div class="modalidades-group-list" id="competitorsList">
                                    @forelse($availableCompetitors as $c)
                                        @php
                                            $isGrouped = in_array((int) $c->id, $groupedIds ?? [], true);
                                        @endphp
                                        <label class="modalidades-group-item {{ $isGrouped ? 'modalidades-group-item--locked' : '' }}" data-name="{{ strtolower($c->nome) }}">
                                            <input type="checkbox" name="competitor_ids[]" value="{{ $c->id }}" {{ $isGrouped ? 'disabled data-locked=1' : '' }}>
                                            <span class="modalidades-group-item__content">
                                                <span class="modalidades-group-item__name">{{ $c->nome }}</span>
                                                <span class="modalidades-group-item__meta">ID #{{ $c->id }}</span>
                                            </span>
                                            @if($isGrouped)
                                                <span class="modalidades-group-item__badge">Já em grupo</span>
                                            @endif
                                        </label>
                                    @empty
                                        <div class="modalidades-groups-empty">Nenhum competidor disponível</div>
                                    @endforelse
                                </div>
                                <small class="modalidades-groups-note">
                                    Você pode selecionar qualquer competidor do sistema. Os já agrupados ficam desabilitados.
                                </small>
                            </div>
                        </div>
                        <div class="modalidades-groups-actions">
                            <button type="submit" class="btn btn--primary">Salvar grupo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="modalidades-groups-card">
                <div class="modalidades-groups-card__header">
                    <div>
                        <h6 class="modalidades-groups-card__title">Grupos cadastrados</h6>
                        <div class="modalidades-groups-card__hint">Gerencie os grupos já criados.</div>
                    </div>
                </div>
                <div class="modalidades-groups-card__body">
                    <div class="table-responsive">
                        <table class="modalidades-groups-table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    @if($modalidade->tem_divisoes)
                                        <th>Divisão</th>
                                    @endif
                                    <th>Integrantes</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($groups as $group)
                                    @php
                                        $memberNames = $group->members?->pluck('nome')->implode(' + ');
                                        $groupLabel = $group->nome ?: $memberNames;
                                    @endphp
                                    <tr>
                                        <td>{{ $groupLabel ?: ('Grupo #' . $group->id) }}</td>
                                        @if($modalidade->tem_divisoes)
                                            <td>
                                                <select class="form-control form-control-sm modalidades-divisao-select" 
                                                        data-group-id="{{ $group->id }}"
                                                        data-original="{{ $group->divisao ?? '' }}"
                                                        style="min-width: 120px;">
                                                    <option value="">Sem divisão</option>
                                                    @foreach(($modalidade->divisoes_nomes ?? []) as $div)
                                                        <option value="{{ $div }}" {{ ($group->divisao ?? '') === $div ? 'selected' : '' }}>{{ $div }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        @endif
                                        <td>{{ $memberNames ?: '—' }}</td>
                                        <td>
                                            <form action="{{ route('admin.modalidades.groups.destroy', [$modalidade->id, $group->id]) }}" method="POST" style="display:inline-block">
                                                @csrf
                                                @method('DELETE')
                                                @if(!empty($divisao))
                                                    <input type="hidden" name="divisao" value="{{ $divisao }}">
                                                @endif
                                                <button type="submit" class="btn btn-sm btn-outline--danger" onclick="return confirm('Remover grupo?')">
                                                    <i class="la la-trash"></i> Remover
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%" class="modalidades-groups-empty">Nenhum grupo cadastrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    .modalidades-groups-wrapper {
        max-width: 1400px;
        margin: 0 auto;
    }
    .modalidades-groups-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
        padding: 1.75rem 2rem;
        border-radius: 16px;
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    .modalidades-groups-header::before {
        content: '';
        position: absolute;
        top: -40%;
        right: -10%;
        width: 260px;
        height: 260px;
        background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
        border-radius: 50%;
    }
    .modalidades-groups-header__content {
        position: relative;
        z-index: 1;
    }
    .modalidades-groups-header__title {
        color: #fff;
        font-size: 1.6rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .modalidades-groups-header__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1.25rem;
        color: rgba(255,255,255,0.9);
        font-size: 0.9rem;
    }
    .modalidades-groups-header__meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    .modalidades-groups-back {
        background: rgba(15, 23, 42, 0.2);
        color: #fff;
        border-color: rgba(255,255,255,0.4);
        position: relative;
        z-index: 1;
    }
    .modalidades-groups-back:hover {
        background: rgba(15, 23, 42, 0.35);
        color: #fff;
    }
    .modalidades-groups-alert {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(251, 191, 36, 0.15);
        color: #fbbf24;
        padding: 0.85rem 1rem;
        border-radius: 12px;
        border: 1px solid rgba(251, 191, 36, 0.3);
        margin-bottom: 1.5rem;
    }
    .modalidades-groups-filter {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        max-width: 320px;
    }
    .modalidades-groups-filter__label {
        color: #e2e8f0;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .modalidades-groups-filter__select {
        background: rgba(15, 23, 42, 0.7);
        border: 1px solid rgba(148, 163, 184, 0.3);
        color: #e2e8f0;
        border-radius: 10px;
    }
    .modalidades-groups-card {
        background: rgba(15, 23, 42, 0.75);
        border: 1px solid rgba(249, 115, 22, 0.18);
        border-radius: 16px;
        box-shadow: 0 12px 30px rgba(0,0,0,0.3);
        overflow: hidden;
        height: 100%;
    }
    .modalidades-groups-card__header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(249, 115, 22, 0.15);
        background: rgba(15, 23, 42, 0.6);
    }
    .modalidades-groups-card__title {
        color: #f97316;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .modalidades-groups-card__hint {
        color: #94a3b8;
        font-size: 0.85rem;
    }
    .modalidades-groups-card__body {
        padding: 1.5rem;
    }
    .modalidades-group-summary {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.6rem 0.85rem;
        border-radius: 10px;
        border: 1px solid rgba(148, 163, 184, 0.2);
        background: rgba(15, 23, 42, 0.6);
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
        color: #cbd5f5;
    }
    .modalidades-group-summary strong {
        color: #fbbf24;
    }
    .modalidades-groups-search .input-group-text,
    .modalidades-groups-search .form-control {
        background: rgba(15, 23, 42, 0.7);
        border-color: rgba(148, 163, 184, 0.25);
        color: #e2e8f0;
    }
    .modalidades-groups-search .form-control::placeholder {
        color: rgba(226, 232, 240, 0.5);
    }
    .modalidades-group-list {
        border: 1px solid rgba(148, 163, 184, 0.2);
        border-radius: 12px;
        background: rgba(15, 23, 42, 0.55);
        max-height: 360px;
        overflow: auto;
    }
    .modalidades-group-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.65rem 0.85rem;
        border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        color: #e2e8f0;
        cursor: pointer;
    }
    .modalidades-group-item:last-child {
        border-bottom: 0;
    }
    .modalidades-group-item input[type="checkbox"] {
        accent-color: #f97316;
    }
    .modalidades-group-item__content {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
        flex: 1;
        min-width: 0;
    }
    .modalidades-group-item__name {
        font-weight: 600;
    }
    .modalidades-group-item__meta {
        font-size: 0.8rem;
        color: #94a3b8;
    }
    .modalidades-group-item__badge {
        background: rgba(239, 68, 68, 0.2);
        color: #f87171;
        border-radius: 999px;
        padding: 0.2rem 0.6rem;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .modalidades-group-item--locked {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .modalidades-classificatoria {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.2);
        background: rgba(15, 23, 42, 0.55);
        color: #e2e8f0;
        font-size: 0.9rem;
    }
    .modalidades-classificatoria input {
        margin-top: 0.2rem;
        accent-color: #f97316;
    }
    .modalidades-classificatoria span {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .modalidades-classificatoria small {
        color: #94a3b8;
        font-size: 0.8rem;
    }
    .modalidades-division-disabled {
        opacity: 0.5;
        pointer-events: none;
    }
    .modalidades-groups-note {
        display: block;
        margin-top: 0.65rem;
        color: #94a3b8;
        font-size: 0.82rem;
    }
    .modalidades-groups-actions {
        margin-top: 1.2rem;
    }
    .modalidades-groups-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .modalidades-groups-table thead th {
        padding: 0.75rem 1rem;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        color: #f97316;
        background: rgba(15, 23, 42, 0.8);
        border-bottom: 1px solid rgba(249, 115, 22, 0.2);
    }
    .modalidades-groups-table tbody tr {
        background: rgba(15, 23, 42, 0.55);
    }
    .modalidades-groups-table tbody tr:hover {
        background: rgba(15, 23, 42, 0.75);
    }
    .modalidades-groups-table tbody td {
        padding: 0.85rem 1rem;
        color: #e2e8f0;
        border-bottom: 1px solid rgba(148, 163, 184, 0.15);
    }
    .modalidades-groups-empty {
        text-align: center;
        color: #94a3b8;
        padding: 1rem;
    }
    .modalidades-divisao-select {
        background: rgba(15, 23, 42, 0.7);
        border: 1px solid rgba(148, 163, 184, 0.3);
        color: #e2e8f0;
        border-radius: 8px;
        padding: 0.35rem 0.6rem;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .modalidades-divisao-select:hover {
        border-color: #f97316;
    }
    .modalidades-divisao-select:focus {
        border-color: #f97316;
        box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.2);
        outline: none;
    }
    .modalidades-divisao-select.saving {
        opacity: 0.6;
        pointer-events: none;
    }
    .modalidades-divisao-select.saved {
        border-color: #22c55e;
        background: rgba(34, 197, 94, 0.1);
    }
    @media (max-width: 991px) {
        .modalidades-groups-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .modalidades-groups-header__title {
            font-size: 1.35rem;
        }
    }
</style>
@endpush

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.modalidades.index') }}" />
@endpush

@push('script')
<script>
(function(){
    const input = document.getElementById('competitorSearch');
    const list = document.getElementById('competitorsList');
    const countEl = document.getElementById('selectedCount');
    const sizeField = document.getElementById('groupTeamSize');
    const classificatoriaCheckbox = document.getElementById('groupIsClassificatoria');
    const divisaoField = document.getElementById('groupDivisionField');
    const divisaoSelect = document.getElementById('groupDivisaoSelect');
    if (!list) return;

    const max = parseInt(sizeField ? sizeField.value : '1', 10) || 1;

    function updateSelection() {
        const boxes = Array.from(list.querySelectorAll('input[type="checkbox"]'));
        const selected = boxes.filter(cb => cb.checked).length;
        if (countEl) countEl.textContent = `${selected} / ${max}`;
        const limitReached = max > 0 && selected >= max;
        boxes.forEach(cb => {
            if (cb.dataset.locked === '1') return;
            if (!cb.checked) {
                cb.disabled = limitReached;
            } else {
                cb.disabled = false;
            }
        });
    }

    if (input) {
        input.addEventListener('input', () => {
            const q = (input.value || '').toLowerCase().trim();
            list.querySelectorAll('[data-name]').forEach(row => {
                const name = row.getAttribute('data-name') || '';
                row.style.display = name.includes(q) ? '' : 'none';
            });
        });
    }

    list.addEventListener('change', (e) => {
        if (e.target && e.target.matches('input[type="checkbox"]')) {
            updateSelection();
        }
    });

    function syncDivisaoRequirement() {
        if (!divisaoField || !divisaoSelect) return;
        const isClassificatoria = !!(classificatoriaCheckbox && classificatoriaCheckbox.checked);
        if (isClassificatoria) {
            divisaoField.classList.add('modalidades-division-disabled');
            divisaoSelect.required = false;
            divisaoSelect.value = '';
        } else {
            divisaoField.classList.remove('modalidades-division-disabled');
            divisaoSelect.required = true;
        }
    }

    if (classificatoriaCheckbox) {
        classificatoriaCheckbox.addEventListener('change', syncDivisaoRequirement);
    }
    syncDivisaoRequirement();

    updateSelection();

    // === EDIÇÃO DE DIVISÃO INLINE ===
    const modalidadeId = {{ $modalidade->id }};
    document.querySelectorAll('.modalidades-divisao-select').forEach(select => {
        select.addEventListener('change', async function() {
            const groupId = this.dataset.groupId;
            const novaDivisao = this.value;
            const original = this.dataset.original;

            this.classList.add('saving');
            this.classList.remove('saved');

            try {
                const response = await fetch(`/admin/modalidades/${modalidadeId}/groups/${groupId}/divisao`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ divisao: novaDivisao })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.dataset.original = novaDivisao;
                    this.classList.add('saved');
                    setTimeout(() => this.classList.remove('saved'), 2000);
                    
                    // Toast de sucesso
                    if (typeof notify === 'function') {
                        notify('success', 'Divisão atualizada!');
                    }
                } else {
                    // Reverter para valor original
                    this.value = original;
                    alert(data.message || 'Erro ao atualizar divisão');
                }
            } catch (error) {
                console.error('Erro:', error);
                this.value = original;
                alert('Erro ao atualizar divisão');
            } finally {
                this.classList.remove('saving');
            }
        });
    });
})();
</script>
@endpush

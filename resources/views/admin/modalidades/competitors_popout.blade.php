@extends('admin.layouts.popout')

@section('panel')
<div class="card" style="border-radius:16px; overflow:hidden;">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-lg-6">
                <label class="form-label">Pesquisar competidor</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="la la-search"></i></span>
                    <input id="searchInput" type="text" class="form-control" placeholder="Digite um nome...">
                </div>
            </div>
            <div class="col-lg-6 d-flex gap-2 justify-content-lg-end">
                <button type="button" class="btn btn-outline--primary" id="btnSelectAllFiltered">
                    <i class="la la-check-square"></i> Adicionar todos (filtrados)
                </button>
                <button type="button" class="btn btn-outline--danger" id="btnClearSelection">
                    <i class="la la-trash"></i> Limpar seleção
                </button>
            </div>
        </div>

        <hr class="my-3">

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="fw-semibold">
                        <i class="la la-user-plus"></i> Disponíveis
                    </div>
                    <span class="badge badge--primary" id="availableCount">0</span>
                </div>
                <div class="border rounded" style="min-height:420px; max-height:58vh; overflow:auto;" id="availableList">
                    <div class="text-muted text-center py-4">Carregando...</div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="fw-semibold">
                        <i class="la la-users"></i> Selecionados
                    </div>
                    <span class="badge badge--success" id="selectedCount">0</span>
                </div>
                <div class="border rounded" style="min-height:420px; max-height:58vh; overflow:auto;" id="selectedList">
                    <div class="text-muted text-center py-4">Carregando...</div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 justify-content-end mt-3">
            <button type="button" class="btn btn--dark" onclick="window.close()">
                Cancelar
            </button>
            <button type="button" class="btn btn--primary" id="btnSave">
                <i class="la la-save"></i> Salvar
            </button>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    .rr-person {
        display:flex;
        align-items:center;
        gap:12px;
        padding:10px 12px;
        border-bottom:1px solid rgba(0,0,0,0.06);
    }
    .rr-person:last-child { border-bottom:0; }
    .rr-avatar {
        width:36px;
        height:36px;
        border-radius:10px;
        display:flex;
        align-items:center;
        justify-content:center;
        font-weight:700;
        color:#fff;
        background: linear-gradient(135deg, #6f42c1, #0d6efd);
        flex:0 0 auto;
    }
    .rr-name { font-weight:600; }
    .rr-sub { font-size:0.85rem; color:#6c757d; }
</style>
@endpush

@push('script')
<script>
(function(){
    const modalidadeId = {{ (int) $modalidade->id }};
    const modalidadeTemDivisoes = {{ ($modalidade->tem_divisoes ? 'true' : 'false') }};
    const modalidadeDivisoes = @json($modalidade->divisoes ?? []);
    const endpoints = {
        list: `{{ url('admin/modalidades') }}/${modalidadeId}/competitors`,
        save: `{{ url('admin/modalidades') }}/${modalidadeId}/competitors/attach`,
    };

    const state = {
        byId: new Map(),
        selected: new Set(),
        divisaoById: new Map(),
        lastFilter: '',
        saving: false,
    };

    function safeNotify(type, message) {
        try {
            if (typeof notify === 'function') {
                notify(type, message);
                return;
            }
        } catch (e) {}
        if (type === 'error' || type === 'danger') alert(message || 'Falha');
        else console.log(type, message);
    }

    function initials(name) {
        const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
        const a = (parts[0] || '').charAt(0);
        const b = (parts[1] || '').charAt(0);
        return (a + b).toUpperCase() || '?';
    }

    function matchesFilter(name, filter) {
        if (!filter) return true;
        return String(name || '').toLowerCase().includes(filter);
    }

    function render() {
        const filter = state.lastFilter;

        const all = Array.from(state.byId.values());
        const selected = all.filter(c => state.selected.has(c.id) && matchesFilter(c.nome, filter));
        const available = all.filter(c => !state.selected.has(c.id) && matchesFilter(c.nome, filter));

        document.getElementById('selectedCount').textContent = String(state.selected.size);
        document.getElementById('availableCount').textContent = String(available.length);

        const availableEl = document.getElementById('availableList');
        const selectedEl = document.getElementById('selectedList');

        if (available.length === 0) {
            availableEl.innerHTML = '<div class="text-muted text-center py-4">Nenhum disponível</div>';
        } else {
            availableEl.innerHTML = available.map(c => `
                <div class="rr-person">
                    <div class="rr-avatar">${initials(c.nome)}</div>
                    <div class="flex-grow-1">
                        <div class="rr-name">${c.nome}</div>
                        <div class="rr-sub">ID #${c.id}</div>
                    </div>
                    <button class="btn btn-sm btn-outline--primary" data-add="${c.id}">
                        <i class="la la-plus"></i> Adicionar
                    </button>
                </div>
            `).join('');
        }

        const renderDivisaoSelect = (id) => {
            if (!modalidadeTemDivisoes || !Array.isArray(modalidadeDivisoes) || modalidadeDivisoes.length === 0) {
                return '';
            }
            const current = String(state.divisaoById.get(id) || '');
            const options = ['<option value="">— Selecione —</option>']
                .concat(modalidadeDivisoes.map(d => {
                    const val = String(d || '');
                    const sel = (val !== '' && val === current) ? 'selected' : '';
                    return `<option value="${val.replace(/"/g, '&quot;')}" ${sel}>${val}</option>`;
                }))
                .join('');
            return `
                <div style="min-width: 180px;">
                    <label class="form-label mb-1" style="font-size:0.8rem;">Divisão</label>
                    <select class="form-select form-select-sm" data-divisao-select="${id}">
                        ${options}
                    </select>
                </div>
            `;
        };

        if (selected.length === 0) {
            selectedEl.innerHTML = '<div class="text-muted text-center py-4">Nenhum selecionado</div>';
        } else {
            selectedEl.innerHTML = selected.map(c => `
                <div class="rr-person">
                    <div class="rr-avatar">${initials(c.nome)}</div>
                    <div class="flex-grow-1">
                        <div class="rr-name">${c.nome}</div>
                        <div class="rr-sub">ID #${c.id}</div>
                    </div>
                    ${renderDivisaoSelect(c.id)}
                    <button class="btn btn-sm btn-outline--danger" data-remove="${c.id}">
                        <i class="la la-times"></i> Remover
                    </button>
                </div>
            `).join('');
        }

        // bind buttons (delegation)
        availableEl.querySelectorAll('[data-add]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.getAttribute('data-add'));
                state.selected.add(id);
                render();
            });
        });
        selectedEl.querySelectorAll('[data-remove]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.getAttribute('data-remove'));
                state.selected.delete(id);
                state.divisaoById.delete(id);
                render();
            });
        });

        selectedEl.querySelectorAll('[data-divisao-select]').forEach(sel => {
            sel.addEventListener('change', () => {
                const id = parseInt(sel.getAttribute('data-divisao-select'));
                const val = String(sel.value || '').trim();
                if (!Number.isNaN(id)) {
                    if (val === '') state.divisaoById.delete(id);
                    else state.divisaoById.set(id, val);
                }
            });
        });
    }

    async function load() {
        document.getElementById('availableList').innerHTML = '<div class="text-muted text-center py-4">Carregando...</div>';
        document.getElementById('selectedList').innerHTML = '<div class="text-muted text-center py-4">Carregando...</div>';

        const r = await fetch(endpoints.list, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
        const data = await r.json();

        state.byId.clear();
        state.selected.clear();
        state.divisaoById.clear();

        const attached = Array.isArray(data.attached) ? data.attached : [];
        const available = Array.isArray(data.available) ? data.available : [];

        attached.forEach(c => {
            state.byId.set(c.id, c);
            state.selected.add(c.id);
            const div = String(c.divisao || '').trim();
            if (div) state.divisaoById.set(c.id, div);
        });
        available.forEach(c => {
            if (!state.byId.has(c.id)) state.byId.set(c.id, c);
        });

        render();
    }

    async function save() {
        if (state.saving) return;
        state.saving = true;

        const btn = document.getElementById('btnSave');
        const oldHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Salvando...';

        try {
            const ids = Array.from(state.selected.values());
            const divMap = {};
            if (modalidadeTemDivisoes && Array.isArray(modalidadeDivisoes) && modalidadeDivisoes.length > 0) {
                for (const id of ids) {
                    const div = String(state.divisaoById.get(id) || '').trim();
                    if (!div) {
                        throw new Error('Selecione a divisão para todos os competidores antes de salvar.');
                    }
                    divMap[String(id)] = div;
                }
            }
            const r = await fetch(endpoints.save, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ competitor_ids: ids, competitor_divisoes: divMap })
            });

            const contentType = (r.headers.get('content-type') || '').toLowerCase();
            if (!r.ok) {
                const text = await r.text();
                throw new Error(`HTTP ${r.status} ${r.statusText} :: ${text}`);
            }

            const resp = contentType.includes('application/json') ? await r.json() : { message: 'Salvo' };
            safeNotify('success', resp.message || 'Salvo');

            // Atualiza badge na tela principal
            try {
                if (window.opener && !window.opener.closed) {
                    window.opener.postMessage({
                        type: 'modalidade-competitors-updated',
                        modalidadeId,
                        count: ids.length,
                    }, '*');
                }
            } catch (e) {}

            // opcional: fechar após salvar
            window.close();
        } catch (err) {
            console.error(err);
            safeNotify('error', err?.message || 'Falha ao salvar');
        } finally {
            state.saving = false;
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        }
    }

    document.getElementById('searchInput').addEventListener('input', (e) => {
        state.lastFilter = String(e.target.value || '').toLowerCase().trim();
        render();
    });

    document.getElementById('btnSelectAllFiltered').addEventListener('click', () => {
        const filter = state.lastFilter;
        const all = Array.from(state.byId.values());
        all.forEach(c => {
            if (!state.selected.has(c.id) && matchesFilter(c.nome, filter)) {
                state.selected.add(c.id);
            }
        });
        render();
    });

    document.getElementById('btnClearSelection').addEventListener('click', () => {
        state.selected.clear();
        state.divisaoById.clear();
        render();
    });

    document.getElementById('btnSave').addEventListener('click', save);

    load().catch(err => {
        console.error(err);
        safeNotify('error', 'Falha ao carregar lista');
    });
})();
</script>
@endpush

@extends('admin.layouts.app')

@section('panel')
    <style>
        .modalidades-edit-wrapper {
            max-width: 1400px;
            margin: 0 auto;
        }

        .modalidades-edit-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(249, 115, 22, 0.2);
            overflow: hidden;
        }

        .modalidades-edit-header {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .modalidades-edit-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .modalidades-edit-header h5 {
            color: #fff;
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modalidades-edit-header h5 i {
            font-size: 1.5rem;
        }

        .modalidades-edit-body {
            padding: 2.5rem;
        }

        .modalidades-section {
            background: rgba(30, 41, 59, 0.5);
            border-radius: 12px;
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(249, 115, 22, 0.15);
            transition: all 0.3s ease;
        }

        .modalidades-section:hover {
            border-color: rgba(249, 115, 22, 0.4);
            box-shadow: 0 4px 16px rgba(249, 115, 22, 0.1);
        }

        .modalidades-section-title {
            color: #f97316;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid rgba(249, 115, 22, 0.3);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modalidades-section-title i {
            font-size: 1rem;
        }

        .modalidades-form-group {
            margin-bottom: 1.5rem;
        }

        .modalidades-form-group label {
            display: block;
            color: #e2e8f0;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .modalidades-form-group label i {
            color: #f97316;
            margin-right: 0.35rem;
            font-size: 0.85rem;
        }

        .modalidades-form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(15, 23, 42, 0.6);
            border: 2px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .modalidades-form-control:focus {
            outline: none;
            border-color: #f97316;
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
        }

        .modalidades-form-control::placeholder {
            color: #64748b;
        }

        .modalidades-form-control option {
            background: #1e293b;
            color: #e2e8f0;
            padding: 0.5rem;
        }

        .modalidades-form-help {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: #94a3b8;
            font-style: italic;
        }

        .modalidades-form-help i {
            margin-right: 0.25rem;
            color: #f97316;
        }

        .modalidades-radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .modalidades-radio-group label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: rgba(15, 23, 42, 0.5);
            border: 2px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .modalidades-radio-group input[type=\"radio\"] {
            accent-color: #f97316;
        }

        .modalidades-radio-group label:hover {
            border-color: rgba(249, 115, 22, 0.4);
        }

        .modalidades-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(249, 115, 22, 0.15);
            color: #f97316;
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            margin: 0 0.4rem 0.4rem 0;
        }

        .divisoes-cards-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .divisao-card {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(249, 115, 22, 0.3);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            min-width: 220px;
            position: relative;
            transition: all 0.3s ease;
        }

        .divisao-card:hover {
            border-color: #f97316;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.2);
        }

        .divisao-card__nome {
            font-weight: 700;
            color: #f97316;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .divisao-card__premio {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .divisao-card__premio strong {
            color: #22c55e;
        }

        .divisao-card__remove {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            transition: all 0.2s;
        }

        .divisao-card__remove:hover {
            background: #ef4444;
            color: #fff;
        }

        .modalidades-submit-footer {
            padding: 2rem;
            background: rgba(15, 23, 42, 0.3);
            border-top: 1px solid rgba(249, 115, 22, 0.2);
        }

        .modalidades-submit-btn {
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(249, 115, 22, 0.3);
        }

        .modalidades-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(249, 115, 22, 0.5);
        }

        .modalidades-submit-btn:active {
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .modalidades-edit-body {
                padding: 1.5rem;
            }

            .modalidades-section {
                padding: 1.25rem;
            }

            .modalidades-edit-header h5 {
                font-size: 1.35rem;
            }
        }
    </style>

    <div class="modalidades-edit-wrapper">
        <div class="modalidades-edit-card">
            <div class="modalidades-edit-header">
                <h5><i class="las la-edit"></i> @lang('Editar Modalidade')</h5>
            </div>

            <form method="post" action="{{ route('admin.modalidades.update', $modalidade->id) }}">
                @csrf
                @method('PUT')
                <div class="modalidades-edit-body">
                    <div class="modalidades-section">
                        <div class="modalidades-section-title">
                            <i class="las la-info-circle"></i>
                            @lang('Informações da Modalidade')
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="modalidades-form-group">
                                    <label><i class="las la-flag"></i> @lang('Rodeio')</label>
                                    <select name="rodeio_id" class="modalidades-form-control" required>
                                        <option value="">@lang('Selecione o Rodeio')</option>
                                        @foreach($rodeios as $rodeio)
                                            <option value="{{ $rodeio->id }}" @selected(old('rodeio_id', $modalidade->rodeio_id) == $rodeio->id)>{{ $rodeio->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="modalidades-form-group">
                                    <label><i class="las la-tag"></i> @lang('Nome')</label>
                                    <input type="text" name="nome" value="{{ old('nome', $modalidade->nome) }}" class="modalidades-form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="modalidades-form-group">
                                    <label><i class="las la-users"></i> @lang('Tipo da Modalidade')</label>
                                    <select name="tipo_participacao" class="modalidades-form-control" required>
                                        @foreach(($tipoOptions ?? []) as $tipoKey => $tipoData)
                                            <option value="{{ $tipoKey }}" @selected(old('tipo_participacao', $modalidade->tipo_participacao ?? 'individual') === $tipoKey)>
                                                {{ $tipoData['label'] }} ({{ $tipoData['size'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="modalidades-form-help"><i class="las la-info-circle"></i> @lang('Define o tamanho do time (1 a 10)')</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="modalidades-form-group">
                                    <label><i class="las la-clock"></i> @lang('Data/Hora Início')</label>
                                    <input type="datetime-local" name="inicio" value="{{ old('inicio', \Carbon\Carbon::parse($modalidade->inicio)->format('Y-m-d\\TH:i')) }}" class="modalidades-form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modalidades-section">
                        <div class="modalidades-section-title">
                            <i class="las la-award"></i>
                            @lang('Premiação')
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="modalidades-form-group">
                                    <label><i class="las la-gift"></i> @lang('Tipo de Prêmio')</label>
                                    <select name="tipo_premio" class="modalidades-form-control" id="tipo_premio" required>
                                        <option value="dinheiro" @selected(old('tipo_premio', $modalidade->tipo_premio) == 'dinheiro')>@lang('Dinheiro')</option>
                                        <option value="fisico" @selected(old('tipo_premio', $modalidade->tipo_premio) == 'fisico')>@lang('Físico')</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6" id="valor_premio_div" style="{{ old('tipo_premio', $modalidade->tipo_premio) == 'fisico' ? 'display: none;' : '' }}">
                                <div class="modalidades-form-group">
                                    <label><i class="las la-money-bill-wave"></i> @lang('Valor do Prêmio (R$)')</label>
                                    <input type="number" step="0.01" name="valor_premio" value="{{ old('valor_premio', $modalidade->valor_premio) }}" class="modalidades-form-control">
                                </div>
                            </div>

                            <div class="col-md-6" id="descricao_premio_div" style="{{ old('tipo_premio', $modalidade->tipo_premio) == 'dinheiro' ? 'display: none;' : '' }}">
                                <div class="modalidades-form-group">
                                    <label><i class="las la-align-left"></i> @lang('Descrição do Prêmio')</label>
                                    <input type="text" name="descricao_premio" value="{{ old('descricao_premio', $modalidade->descricao_premio) }}" class="modalidades-form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="modalidades-form-group">
                                    <label><i class="las la-toggle-on"></i> @lang('Status')</label>
                                    <select name="status" class="modalidades-form-control" required>
                                        <option value="ativo" @selected(old('status', $modalidade->status) == 'ativo')>@lang('Ativo')</option>
                                        <option value="inativo" @selected(old('status', $modalidade->status) == 'inativo')>@lang('Inativo')</option>
                                        <option value="finalizado" @selected(old('status', $modalidade->status) == 'finalizado')>@lang('Finalizado')</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modalidades-section">
                        <div class="modalidades-section-title">
                            <i class="las la-layer-group"></i>
                            @lang('Divisões')
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="modalidades-form-group">
                                    <label><i class="las la-sitemap"></i> @lang('Existe divisões nessa modalidade?')</label>
                                    <div class="modalidades-radio-group">
                                        <label>
                                            <input type="radio" name="tem_divisoes" value="0" @checked(old('tem_divisoes', ($modalidade->tem_divisoes ? '1' : '0')) == '0')>
                                            @lang('Não')
                                        </label>
                                        <label>
                                            <input type="radio" name="tem_divisoes" value="1" @checked(old('tem_divisoes', ($modalidade->tem_divisoes ? '1' : '0')) == '1')>
                                            @lang('Sim')
                                        </label>
                                    </div>
                                    <small class="modalidades-form-help"><i class="las la-info-circle"></i> @lang('Se sim, adicione as divisões com premiação individual')</small>
                                </div>
                            </div>

                            <div class="col-md-12" id="divisoesBox" style="display:none;">
                                <div class="modalidades-form-group">
                                    <label><i class="las la-plus"></i> @lang('Adicionar Divisão')</label>
                                    <div class="row g-2 mb-3">
                                        <div class="col-md-3">
                                            <input type="text" id="divisaoNomeInput" class="modalidades-form-control" placeholder="@lang('Nome da divisão (ex: Força A)')">
                                        </div>
                                        <div class="col-md-2">
                                            <select id="divisaoTipoPremio" class="modalidades-form-control">
                                                <option value="dinheiro">@lang('Dinheiro')</option>
                                                <option value="fisico">@lang('Físico')</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3" id="divisaoValorWrapper">
                                            <input type="number" id="divisaoValorPremio" class="modalidades-form-control" placeholder="@lang('Valor do prêmio (R$)')" step="0.01" min="0">
                                        </div>
                                        <div class="col-md-3" id="divisaoDescricaoWrapper" style="display:none;">
                                            <input type="text" id="divisaoDescricaoPremio" class="modalidades-form-control" placeholder="@lang('Descrição do prêmio')">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn--primary w-100" id="addDivisaoBtn" style="height: 100%;">
                                                <i class="las la-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="divisoes-cards-container" id="divisoesList"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modalidades-submit-footer">
                    <button type="submit" class="modalidades-submit-btn">
                        <i class="las la-save"></i> @lang('Salvar Alterações')
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
<script>
// Toggle tipo prêmio geral
document.getElementById('tipo_premio').addEventListener('change', function() {
    const valor = this.value;
    const valorDiv = document.getElementById('valor_premio_div');
    const descricaoDiv = document.getElementById('descricao_premio_div');

    if (valor === 'dinheiro') {
        valorDiv.style.display = 'block';
        descricaoDiv.style.display = 'none';
    } else {
        valorDiv.style.display = 'none';
        descricaoDiv.style.display = 'block';
    }
});

function setupDivisoesUI() {
    const radios = document.querySelectorAll('input[name="tem_divisoes"]');
    const box = document.getElementById('divisoesBox');
    const nomeInput = document.getElementById('divisaoNomeInput');
    const tipoPremioSelect = document.getElementById('divisaoTipoPremio');
    const valorPremioInput = document.getElementById('divisaoValorPremio');
    const descricaoPremioInput = document.getElementById('divisaoDescricaoPremio');
    const valorWrapper = document.getElementById('divisaoValorWrapper');
    const descricaoWrapper = document.getElementById('divisaoDescricaoWrapper');
    const addBtn = document.getElementById('addDivisaoBtn');
    const list = document.getElementById('divisoesList');
    
    if (!box || !nomeInput || !addBtn || !list) return;

    // Dados existentes - pode ser array de strings (legado) ou array de objetos (novo)
    const rawExisting = @json(old('divisoes_data') ?? $modalidade->divisoes ?? []);
    const state = { items: [] };

    // Normalizar dados existentes
    if (Array.isArray(rawExisting)) {
        rawExisting.forEach(item => {
            if (typeof item === 'string') {
                // Formato antigo
                state.items.push({
                    nome: item,
                    tipo_premio: 'dinheiro',
                    valor_premio: '',
                    descricao_premio: ''
                });
            } else if (typeof item === 'object' && item.nome) {
                // Formato novo
                state.items.push({
                    nome: item.nome || '',
                    tipo_premio: item.tipo_premio || 'dinheiro',
                    valor_premio: item.valor_premio || '',
                    descricao_premio: item.descricao_premio || ''
                });
            }
        });
    }

    // Toggle tipo prêmio da divisão
    tipoPremioSelect.addEventListener('change', function() {
        if (this.value === 'dinheiro') {
            valorWrapper.style.display = 'block';
            descricaoWrapper.style.display = 'none';
        } else {
            valorWrapper.style.display = 'none';
            descricaoWrapper.style.display = 'block';
        }
    });

    function render() {
        list.innerHTML = '';
        // Remove hidden inputs antigos
        box.querySelectorAll('input[type="hidden"][name^="divisoes_data"]').forEach(n => n.remove());

        state.items.forEach((item, index) => {
            // Card
            const card = document.createElement('div');
            card.className = 'divisao-card';
            
            const nomeEl = document.createElement('div');
            nomeEl.className = 'divisao-card__nome';
            nomeEl.textContent = item.nome;
            card.appendChild(nomeEl);

            const premioEl = document.createElement('div');
            premioEl.className = 'divisao-card__premio';
            if (item.tipo_premio === 'dinheiro' && item.valor_premio) {
                premioEl.innerHTML = '<i class="las la-dollar-sign"></i> <strong>R$ ' + parseFloat(item.valor_premio).toLocaleString('pt-BR', {minimumFractionDigits: 2}) + '</strong>';
            } else if (item.tipo_premio === 'fisico' && item.descricao_premio) {
                premioEl.innerHTML = '<i class="las la-trophy"></i> ' + item.descricao_premio;
            } else {
                premioEl.innerHTML = '<i class="las la-gift"></i> Prêmio não definido';
            }
            card.appendChild(premioEl);

            // Remove button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'divisao-card__remove';
            removeBtn.innerHTML = '×';
            removeBtn.title = '@lang('Remover')';
            removeBtn.addEventListener('click', () => {
                state.items.splice(index, 1);
                render();
                if (state.items.length === 0) {
                    const radioNao = document.querySelector('input[name="tem_divisoes"][value="0"]');
                    if (radioNao) {
                        radioNao.checked = true;
                        syncBox();
                    }
                }
            });
            card.appendChild(removeBtn);

            list.appendChild(card);

            // Hidden inputs
            ['nome', 'tipo_premio', 'valor_premio', 'descricao_premio'].forEach(field => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = `divisoes_data[${index}][${field}]`;
                hidden.value = item[field] || '';
                box.appendChild(hidden);
            });
        });
    }

    function addDivisao() {
        const nome = nomeInput.value.trim();
        if (!nome) {
            nomeInput.focus();
            return;
        }
        // Check duplicate
        if (state.items.some(x => x.nome.toLowerCase() === nome.toLowerCase())) {
            alert('@lang('Divisão já existe')');
            return;
        }

        const tipoPremio = tipoPremioSelect.value;
        const valorPremio = tipoPremio === 'dinheiro' ? valorPremioInput.value : '';
        const descricaoPremio = tipoPremio === 'fisico' ? descricaoPremioInput.value : '';

        state.items.push({
            nome: nome,
            tipo_premio: tipoPremio,
            valor_premio: valorPremio,
            descricao_premio: descricaoPremio
        });

        render();

        // Clear inputs
        nomeInput.value = '';
        valorPremioInput.value = '';
        descricaoPremioInput.value = '';
        nomeInput.focus();
    }

    addBtn.addEventListener('click', addDivisao);
    nomeInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            addDivisao();
        }
    });

    function syncBox() {
        const val = document.querySelector('input[name="tem_divisoes"]:checked')?.value;
        box.style.display = (val === '1') ? 'block' : 'none';
    }
    radios.forEach(r => r.addEventListener('change', syncBox));

    // Init
    render();
    syncBox();
}

setupDivisoesUI();
</script>
@endpush

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.modalidades.index') }}" />
@endpush

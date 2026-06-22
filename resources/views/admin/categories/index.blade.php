<?php

<!-- Modal header -->
<h5 class="modal-title">Adicionar novo rodeio</h5>
<!-- ...existing code... -->

<form method="POST" action="{{ route('admin.category.store') }}" enctype="multipart/form-data">
    @csrf
    <!-- Form labels -->
    <label for="name">Nome do Rodeio *</label>
    <input id="name" name="name" class="form-control" required />

    <label for="start">Data e hora de início *</label>
    <input id="start" name="start" type="datetime-local" class="form-control" required />

    <label for="end">Data e hora de término *</label>
    <input id="end" name="end" type="datetime-local" class="form-control" required />

    <label for="logo">Logo do Rodeio (PNG) *</label>
    <input id="logo" name="logo" type="file" class="form-control" accept="image/png" required />

    <!-- Submit button -->
    <button type="submit" class="btn btn-primary">Enviar</button>
</form>

<!-- ...existing code... -->

<table class="table">
    <thead>
        <tr>
            <th>Nome</th>
            <th>Início</th>
            <th>Término</th>
            <th>Logo</th>
            <th>Ação</th>
        </tr>
    </thead>
    <tbody>
        @forelse($categories as $rodeio)
            <tr>
                <td>{{ $rodeio->name }}</td>
                <td>{{ $rodeio->start }}</td>
                <td>{{ $rodeio->end }}</td>
                <td>
                    @if($rodeio->logo)
                        <img src="{{ asset('assets/images/logo_rodeio/' . $rodeio->logo) }}" alt="Logo" style="height:40px;max-width:80px;object-fit:contain;">
                    @else
                        <span class="text-muted">Sem logo</span>
                    @endif
                </td>
                <td>
                    <!-- Botões de ação -->
                    <a href="{{ route('admin.category.edit', $rodeio->id) }}" class="btn btn-sm btn-warning">Editar</a>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalidadeModal-{{ $rodeio->id }}">Adicionar modalidade</button>
                    <!-- Modal de cadastro de modalidade -->
                    <div class="modal fade" id="modalidadeModal-{{ $rodeio->id }}" tabindex="-1" aria-labelledby="modalidadeModalLabel-{{ $rodeio->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalidadeModalLabel-{{ $rodeio->id }}">Cadastrar Modalidade</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST" action="{{ route('admin.modalidade.store') }}">
                                    @csrf
                                    <input type="hidden" name="rodeio_id" value="{{ $rodeio->id }}">
                                    <div class="modal-body">
                                        <label for="nome">Nome da Modalidade *</label>
                                        <input id="nome" name="nome" class="form-control" required />
                                        <label for="inicio">Data e hora de início *</label>
                                        <input id="inicio" name="inicio" type="datetime-local" class="form-control" required />
                                        <label for="tipo_premio">Tipo de Prêmio *</label>
                                        <select id="tipo_premio" name="tipo_premio" class="form-control" required onchange="togglePremioFields(this, {{ $rodeio->id }})">
                                            <option value="valor">Valor</option>
                                            <option value="descricao">Descrição</option>
                                        </select>
                                        <div id="valor_premio_field_{{ $rodeio->id }}" style="display:block;">
                                            <label for="valor_premio">Valor do Prêmio</label>
                                            <input id="valor_premio" name="valor_premio" type="number" step="0.01" class="form-control" />
                                        </div>
                                        <div id="descricao_premio_field_{{ $rodeio->id }}" style="display:none;">
                                            <label for="descricao_premio">Descrição do Prêmio</label>
                                            <input id="descricao_premio" name="descricao_premio" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Salvar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </td>
                </tr>
                @if($rodeio->modalidades && count($rodeio->modalidades))
                <tr>
                    <td colspan="5" style="background:#f8f9fa;">
                        <strong>Modalidades:</strong>
                        <ul style="margin-bottom:0;">
                            @foreach($rodeio->modalidades as $modalidade)
                                <li>
                                    <b>{{ $modalidade->nome }}</b> - Início: {{ date('d/m/Y H:i', strtotime($modalidade->inicio)) }} - Prêmio: 
                                    @if($modalidade->tipo_premio == 'valor')
                                        R$ {{ number_format($modalidade->valor_premio, 2, ',', '.') }}
                                    @else
                                        {{ $modalidade->descricao_premio }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">Data not found</td>
            </tr>
        @endforelse
    </tbody>
</table>
<script>
function togglePremioFields(select, rodeioId) {
    var valorField = document.getElementById('valor_premio_field_' + rodeioId);
    var descricaoField = document.getElementById('descricao_premio_field_' + rodeioId);
    if (select.value === 'valor') {
        valorField.style.display = 'block';
        descricaoField.style.display = 'none';
    } else {
        valorField.style.display = 'none';
        descricaoField.style.display = 'block';
    }
}
</script>
        @extends('admin.layouts.app')
@section('panel')

<div class="alert alert-info p-3" role="alert">
    <p>
        Modalidades podem ser adicionadas manualmente e vinculadas a rodeios.
    </p>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table--light style--two table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Rodeio</th>
                                <th>Início</th>
                                <th>Prêmio</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($modalidades as $modalidade)
                                <tr>
                                    <td>{{ $modalidade->nome }}</td>
                                    <td>{{ $modalidade->rodeio ? $modalidade->rodeio->name : '-' }}</td>
                                    <td>{{ date('d/m/Y H:i', strtotime($modalidade->inicio)) }}</td>
                                    <td>
                                        @if($modalidade->tipo_premio == 'valor')
                                            R$ {{ number_format($modalidade->valor_premio, 2, ',', '.') }}
                                        @else
                                            {{ $modalidade->descricao_premio }}
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline--primary cuModalBtn editBtn" data-resource='@json($modalidade)' data-modal_title="Editar Modalidade" type="button">
                                            <i class="la la-pencil"></i>Editar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">Nenhuma modalidade cadastrada</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($modalidades->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($modalidades) }}
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="cuModal" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form action="{{ route('admin.modalidade.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="rodeio_id">Rodeio</label>
                                <select name="rodeio_id" id="rodeio_id" class="form-control" required>
                                    @foreach($rodeios as $rodeio)
                                        <option value="{{ $rodeio->id }}">{{ $rodeio->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nome">Nome da Modalidade</label>
                                <input type="text" class="form-control" name="nome" id="nome" required />
                            </div>
                            <div class="form-group">
                                <label for="inicio">Data e hora de início</label>
                                <input type="datetime-local" class="form-control" name="inicio" id="inicio" required />
                            </div>
                            <div class="form-group">
                                <label for="tipo_premio">Tipo de Prêmio</label>
                                <select name="tipo_premio" id="tipo_premio" class="form-control" required onchange="togglePremioFieldsModal()">
                                    <option value="valor">Valor</option>
                                    <option value="descricao">Descrição</option>
                                </select>
                            </div>
                            <div class="form-group" id="valor_premio_field_modal" style="display:block;">
                                <label for="valor_premio">Valor do Prêmio</label>
                                <input type="number" step="0.01" class="form-control" name="valor_premio" id="valor_premio" />
                            </div>
                            <div class="form-group" id="descricao_premio_field_modal" style="display:none;">
                                <label for="descricao_premio">Descrição do Prêmio</label>
                                <input type="text" class="form-control" name="descricao_premio" id="descricao_premio" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn--primary w-100 h-45">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function togglePremioFieldsModal() {
    var tipo = document.getElementById('tipo_premio').value;
    var valorField = document.getElementById('valor_premio_field_modal');
    var descricaoField = document.getElementById('descricao_premio_field_modal');
    if (tipo === 'valor') {
        valorField.style.display = 'block';
        descricaoField.style.display = 'none';
    } else {
        valorField.style.display = 'none';
        descricaoField.style.display = 'block';
    }
}
</script>
                                <div class="form-group">
                                    <label>Image</label>
                                    <x-image-uploader image="{{ getImage(getFilePath('team'), getFileSize('team')) }}" class="w-100" type="team" :required=false />
                                </div>
                            </div>
                            <div class="col-lg-6">

                                <div class="form-group">
                                    <label>Rodeio</label>
                                    <select class="form-control select2" name="rodeio_id" required>
                                        <option value="">Selecione um rodeio</option>
                                        @foreach ($rodeios as $rodeio)
                                            <option value="{{ $rodeio->id }}">{{ __($rodeio->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Name</label>
                                    <input class="form-control makeSlug" name="name" type="text" value="{{ old('name') }}" required />
                                </div>

                                <div class="form-group">
                                    <label>Short Name</label>
                                    <input class="form-control" name="short_name" type="text" value="{{ old('short_name') }}" required />
                                </div>

                                <div class="form-group">
                                    <label>Slug</label>
                                    <input class="form-control checkSlug" name="slug" type="text" value="{{ old('slug') }}" required />
                                    <code>Spaces are not allowed</code>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary w-100 h-45" type="submit">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <button class="btn btn-sm btn-outline--primary h-45 cuModalBtn" data-modal_title="Add New Team" type="button">
        <i class="las la-plus"></i>Adicionar Novo
    </button>
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/cu-modal.js') }}"></script>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            let modal = $('#cuModal');
            $('.editBtn').on('click', function() {
                modal.find('select[name=rodeio_id]').val($(this).data('rodeio_id')).change();
                modal.find('[name=image]').removeAttr('required');
                modal.find('[name=image]').closest('.form-group').find('label').first().removeClass('required');
                modal.find('.image-upload-preview').attr('style', `background-image: url(${$(this).data('image')})`);
            });

            var placeHolderImage = "{{ getImage(getFilePath('team'), getFileSize('team')) }}";
            $('#cuModal').on('hidden.bs.modal', function() {
                modal.find('select[name=rodeio_id]').val('').change();
                modal.find('.image-upload-preview').attr('style', `background-image: url(${placeHolderImage})`);
                modal.find('[name=image]').attr('required', 'required');
                modal.find('[name=image]').closest('.form-group').find('label').first().addClass('required');
                $('#cuModal form')[0].reset();
            });

        })(jQuery);
    </script>
@endpush

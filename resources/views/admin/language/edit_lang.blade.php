@extends('admin.layouts.app')
@section('panel')
    <div id="app">
        <div class="row">

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row justify-content-between mt-3">
                            <div class="col-md-7">
                                <ul>
                                    <li>
                                        <h5>Language Keywords of {{ __($lang->name) }}</h5>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-5 mt-md-0 mt-3">
                                <button type="button" data-bs-toggle="modal" data-bs-target="#addModal" class="btn btn-sm btn-outline--primary float-end"><i class="fas fa-plus"></i> Adicionar Nova Chave </button>
                            </div>
                        </div>
                        <hr>
                        <div class="table-responsive--sm table-responsive">
                            <table class="table table--light tabstyle--two custom-data-table white-space-wrap" id="myTable">
                                <thead>
                                    <tr>
                                        <th>
                                            Chave
                                        </th>
                                        <th>
                                            {{ __($lang->name) }}
                                        </th>

                                        <th class="w-85">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @forelse($json as $k => $language)
                                        <tr>
                                            <td class="white-space-wrap">{{ $k }}</td>
                                            <td class="text-left white-space-wrap">{{ $language }}</td>


                                            <td>
                                                <div class="button--group">
                                                    <button type="button" data-bs-target="#editModal" data-bs-toggle="modal" data-title="{{ $k }}" data-key="{{ $k }}" data-value="{{ $language }}" class="editModal btn btn-sm btn-outline--primary">
                                                        <i class="la la-pencil"></i> Editar
                                                    </button>

                                                    <button type="button" data-key="{{ $k }}" data-value="{{ $language }}" data-bs-toggle="modal" data-bs-target="#DelModal" class="btn btn-sm btn-outline--danger deleteKey">
                                                        <i class="la la-trash"></i> Remove
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="100%" class="text-center">{{ __($emptyMessage) }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($json->hasPages())
                        <div class="card-footer py-4">
                            @php
                                echo paginateLinks($json);
                            @endphp
                        </div>
                    @endif
                </div>
            </div>
        </div>


        <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="addModalLabel"> Adicionar Novo</h4>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>

                    <form action="{{ route('admin.language.store.key', $lang->id) }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="key">Chave</label>
                                <input type="text" class="form-control" id="key" name="key" value="{{ old('key') }}" required>

                            </div>
                            <div class="form-group">
                                <label for="value">Valor</label>
                                <input type="text" class="form-control" id="value" name="value" value="{{ old('value') }}" required>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn--primary w-100 h-45"> Enviar</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>


        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="editModalLabel">Editar</h4>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><i class="las la-times"></i></button>
                    </div>

                    <form action="{{ route('admin.language.update.key', $lang->id) }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group ">
                                <label for="inputName" class="form-title"></label>
                                <input type="text" class="form-control" name="value" required>
                            </div>
                            <input type="hidden" name="key">
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn--primary w-100 h-45">Enviar</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>


        <!-- Modal for DELETE -->
        <div class="modal fade" id="DelModal" tabindex="-1" role="dialog" aria-labelledby="DelModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="DelModalLabel"> Confirmation Alert!</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><i class="las la-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure to delete this key from this language?</p>
                    </div>
                    <form action="{{ route('admin.language.delete.key', $lang->id) }}" method="post">
                        @csrf
                        <input type="hidden" name="key">
                        <input type="hidden" name="value">
                        <div class="modal-footer">
                            <button type="button" class="btn btn--dark" data-bs-dismiss="modal">Não</button>
                            <button type="submit" class="btn btn--primary">Sim</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    {{-- Import Modal --}}
    <div class="modal fade" id="importModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Importar Palavras-chave</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><i class="las la-times"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Importar de</label>
                        <select class="form-control select_lang select2" data-minimum-results-for-search="-1" required>
                            <option value="">Select One</option>
                            <option value="999">Sistema</option>
                            @foreach ($list_lang as $data)
                                @if ($data->id != $lang->id)
                                    <option value="{{ $data->id }}">{{ __($data->name) }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn--primary import_lang"> Import Now</button>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('breadcrumb-plugins')
    <button type="button" class="btn btn-sm btn-outline--primary importBtn"><i class="la la-download"></i>Importar Palavras-chave</button>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $(document).on('click', '.deleteKey', function() {
                var modal = $('#DelModal');
                modal.find('input[name=key]').val($(this).data('key'));
                modal.find('input[name=value]').val($(this).data('value'));
            });
            $(document).on('click', '.editModal', function() {
                var modal = $('#editModal');
                modal.find('.form-title').text($(this).data('title'));
                modal.find('input[name=key]').val($(this).data('key'));
                modal.find('input[name=value]').val($(this).data('value'));
            });


            $(document).on('click', '.importBtn', function() {
                $('#importModal').modal('show');
            });
            $(document).on('click', '.import_lang', function(e) {
                var id = $('.select_lang').val();

                if (id == '') {
                    notify('error', 'Invalide selection');

                    return 0;
                } else {
                    $.ajax({
                        type: "post",
                        url: "{{ route('admin.language.import.lang') }}",
                        data: {
                            id: id,
                            toLangid: "{{ $lang->id }}",
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(data) {
                            if (data == 'success') {
                                notify('success', 'Import Data Successfully');
                                window.location.href = "{{ url()->current() }}"
                            }
                        }
                    });
                }

            });
        })(jQuery);
    </script>
@endpush

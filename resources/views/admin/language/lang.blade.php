@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-md-12 mb-30">
            <div class="card bl--5 border--primary">
                <div class="card-body">
                    <p class="text--primary">Ao adicionar uma nova palavra-chave, ela será adicionada apenas a este idioma atual. Digite com cuidado, sem espaços extras. Precisa ser exato e com maiúsculas/minúsculas corretas.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two custom-data-table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Código</th>
                                    <th>Padrão</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($languages as $item)
                                    <tr>
                                        <td>
                                            <div class="user">
                                                <div class="thumb">
                                                    <img src="{{ getImage(getFilePath('language') . '/' . @$item->image, getFileSize('language')) }}"
                                                         alt="{{ $item->name }}" class="plugin_bg">
                                                    <span class="name">{{ __($item->name) }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><strong>{{ __($item->code) }}</strong></td>
                                        <td>
                                            @if ($item->is_default == Status::YES)
                                                <span class="badge badge--success">Padrão</span>
                                            @else
                                                <span class="badge badge--warning">Selecionável</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.language.key', $item->id) }}" class="btn btn-sm btn-outline--success">
                                                    <i class="la la-language"></i> Traduzir
                                                </a>
                                                <a href="javascript:void(0)" class="btn btn-sm btn-outline--primary ms-1 editBtn" data-url="{{ route('admin.language.manage.update', $item->id) }}" data-lang="{{ json_encode($item->only('name', 'text_align', 'is_default', 'image')) }}" data-image="{{ getImage(getFilePath('language') . '/' . $item->image, getFileSize('language')) }}">
                                                    <i class="la la-pen"></i> Editar
                                                </a>
                                                @if ($item->id != 1)
                                                    <button class="btn btn-sm btn-outline--danger confirmationBtn" data-question="Tem certeza que deseja remover este idioma do sistema?" data-action="{{ route('admin.language.manage.delete', $item->id) }}">
                                                        <i class="la la-trash"></i> Remover
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
            </div><!-- card end -->
        </div>
    </div>



    {{-- NEW MODAL --}}
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="createModalLabel">Adicionar novo idioma</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><i class="las la-times"></i></button>
                </div>
                <form class="form-horizontal" method="post" action="{{ route('admin.language.manage.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-12">
                                <label> Bandeira</label>
                                <x-image-uploader :imagePath="getImage(null, getFileSize('language'))" :size="getFileSize('language')" class="w-100" id="imageCreate"
                                                  :required="true" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <label>Nome do Idioma</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" value="{{ old('name') }}" name="name" required>
                            </div>
                        </div>

                        <div class="row form-group">
                            <label>Código do Idioma</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" value="{{ old('code') }}" name="code" required>
                            </div>
                        </div>

                        <div class="row form-group">
                            <div class="col-md-12">
                                <label for="inputName">Idioma Padrão</label>
                                <input type="checkbox" data-width="100%" data-height="40px" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-on="DEFINIR" data-off="REMOVER" name="is_default">
                            </div>

                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45" id="btn-save" value="add">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="editModalLabel">Editar idioma</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><i class="las la-times"></i></button>
                </div>
                <form method="post" class="disableSubmission" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label> Bandeira</label>
                            <x-image-uploader :imagePath="getImage(null, getFileSize('language'))" :size="getFileSize('language')" class="w-100" id="imageEdit"
                                              :required="false" />
                        </div>
                        <div class="form-group">
                            <label>Nome do Idioma</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" value="{{ old('name') }}" name="name" required>
                            </div>
                        </div>

                        <div class="form-group mt-2">
                            <label for="inputName">Idioma Padrão</label>
                            <input type="checkbox" data-width="100%" data-height="40px" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-on="DEFINIR" data-off="REMOVER" name="is_default">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45" id="btn-save" value="add">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade" id="getLangModal" tabindex="-1" role="dialog" aria-labelledby="getLangModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="getLangModalLabel">Palavras-chave do Idioma</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><i class="las la-times"></i></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Todas as possíveis palavras-chave de idioma estão disponíveis aqui. Algumas podem faltar por variações no banco de dados. Caso falte algo, você pode adicionar manualmente.</p>
                    <p class="text--primary mb-3">Você também pode importar essas palavras-chave da página de tradução de qualquer idioma.</p>
                    <div class="form-group">
                        <textarea name="" class="form-control langKeys key-added" id="langKeys" rows="25" readonly></textarea>
                        <button type="button" class="btn btn--primary w-100 h-45 mt-3 copyBtn"><i class="las la-copy"></i> <span class="text-white copy-text">Copiar</span></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection


@push('breadcrumb-plugins')
    <button type="button" class="btn btn-sm btn-outline--primary" data-bs-toggle="modal" data-bs-target="#createModal"><i class="las la-plus"></i>Adicionar Novo</button>
    <button type="button" class="btn btn-sm btn-outline--info keyBtn" data-bs-toggle="modal" data-bs-target="#getLangModal"><i class="las la-code"></i>Palavras-chave do Idioma</button>
@endpush

@push('style')
    <style>
        .key-added {
            pointer-events: unset !important;
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.editBtn').on('click', function() {
                var modal = $('#editModal');
                var url = $(this).data('url');
                var lang = $(this).data('lang');

                modal.find('form').attr('action', url);
                modal.find('input[name=name]').val(lang.name);
                modal.find('select[name=text_align]').val(lang.text_align);
                modal.find('.image-upload-preview').css('background-image', `url(${$(this).data('image')})`);
                if (lang.is_default == 1) {
                    modal.find('input[name=is_default]').bootstrapToggle('on');
                } else {
                    modal.find('input[name=is_default]').bootstrapToggle('off');
                }
                modal.modal('show');
            });

            $('.keyBtn').on('click', function(e) {
                e.preventDefault();
                $.get("{{ route('admin.language.get.key') }}", {}, function(data) {
                    $('.langKeys').text(data);
                });
            });

            $('.copyBtn').on('click', function() {
                var copyText = document.getElementById("langKeys");
                copyText.select();
                document.execCommand("copy");
                $('.copy-text').text('Copiado');
                setTimeout(() => {
                    $('.copy-text').text('Copiar');
                }, 2000);

            });

        })(jQuery);
    </script>
@endpush

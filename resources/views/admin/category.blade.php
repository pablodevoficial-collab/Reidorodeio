@extends('admin.layouts.app')

@php /* Rotas padrão de categorias (mantemos os textos: Rodeios) */ @endphp

@section('panel')
    <div class="alert alert-info p-3 flex-column" role="alert">
        <strong>Rodeios</strong>
        <span>Gerencie nome, nome na API, slug, ícone e status dos rodeios.</span>
    </div>

    <div class="row gy-3">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
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
                                @forelse ($rodeios as $rodeio)
                                    <tr>
                                        <td>{{ __($rodeio->name) }}</td>
                                        <td>{{ $rodeio->start ? date('d/m/Y H:i', strtotime($rodeio->start)) : '-' }}</td>
                                        <td>{{ $rodeio->end ? date('d/m/Y H:i', strtotime($rodeio->end)) : '-' }}</td>
                                        <td>
                                            @if($rodeio->logo)
                                                <img src="{{ asset('assets/images/logo_rodeio/' . $rodeio->logo) }}?t={{ strtotime($rodeio->updated_at) }}" alt="Logo" style="height:40px;max-width:80px;object-fit:contain;">
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn editBtn" data-resource='@json($rodeio)' data-modal_title="Editar rodeio">
                                                    <i class="la la-pencil"></i>Editar
                                                </button>
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
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($rodeios->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($rodeios) }}
                    </div>
                @endif

            </div>
        </div>
    </div>

    <div id="cuModal" class="modal modal-lg fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.category.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">

                        <div class="alert alert-info p-3 flex-column" role="alert">
                            <h4>Cadastro manual de rodeio</h4>
                            <p>Preencha as informações do rodeio abaixo. Todos os campos são obrigatórios.</p>
                        </div>


                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="name">Nome do Rodeio</label>
                                    <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}" required />
                                </div>
                                <div class="form-group">
                                    <label for="start">Data e hora de início</label>
                                    <input type="datetime-local" class="form-control" name="start" id="start" value="{{ old('start') }}" required />
                                </div>
                                <div class="form-group">
                                    <label for="end">Data e hora de término</label>
                                    <input type="datetime-local" class="form-control" name="end" id="end" value="{{ old('end') }}" required />
                                </div>
                                <div class="form-group">
                                    <label for="logo">Logo do Rodeio</label>
                                    <input type="file" class="form-control" name="logo" id="logo" accept="image/*" required />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />

    {{-- Modal “Buscar da API” --}}
    <script>
        const fetchCategoriesUrl = "{{ route('admin.category.fetch') }}";
    </script>
    <div class="modal fade" id="fetchCategoriesModal" tabindex="-1" aria-labelledby="fetchCategoriesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fetchCategoriesModalLabel">Adicionar rodeios da API</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><i class="la la-times"></i></button>
                </div>

                <div class="modal-body">
                    <form id="addCategoriesForm" method="post" action="{{ route('admin.category.fetched.save') }}">
                        @csrf
                        <div class="categories-list"></div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn--primary w-100 h-45" form="addCategoriesForm">Adicionar rodeios selecionados</button>
                </div>
            </div>
        </div>
    </div>

    
@endsection

@push('breadcrumb-plugins')
    <button type="button" class="btn btn-outline--dark" data-bs-toggle="modal" data-bs-target="#fetchCategoriesModal">
    <i class="la la-sync"></i> Buscar rodeios
    </button>

    <button type="button" class="btn btn-sm btn-outline--primary h-45 cuModalBtn" data-modal_title="Adicionar novo rodeio">
        <i class="las la-plus"></i>Adicionar novo rodeio
    </button>
@endpush

@push('style-lib')
    <link href="{{ asset('assets/admin/css/sports-iconpicker.css') }}" rel="stylesheet">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/sport-icons-picker.js') }}"></script>
    <script src="{{ asset('assets/admin/js/cu-modal.js') }}"></script>
@endpush

@push('style')
    <style>
        td .custom-icon {
            font-size: 1.5rem;
        }

        input:focus {
            box-shadow: none !important;
        }
    </style>
@endpush


@push('script')
    <script>
        (function($) {
            "use strict";
            const categoryModal = $('#fetchCategoriesModal');

            categoryModal.on('show.bs.modal', function(e) {
                categoryModal.find('.categories-list').html(`<div class="text-center p-5"><i class="la la-circle-notch la-spin la-3x text-muted"></i></div>`);

                fetchCategories();
            });


            function fetchCategories() {
                if (!window.fetchCategoriesUrl) {
                    return;
                }
                $.get(window.fetchCategoriesUrl,
                    function(response) {
                        if (response.status == 'error') {
                            categoryModal.find('.categories-list').html(`<h6 class="p-3 text-center text--danger">${response.message}</h6>`);
                        } else {
                            if (response.categories) {
                                if (response.categories.length) {
                                    let result = `<h6 class="text-center mb-3">Escolha os rodeios para adicionar a partir da lista abaixo</h6>`;
                                    response.categories.forEach((category) => {
                                        result += `
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input my-0" name="categories[]" id="category-slug" value="${category}">
                                                    ${category}
                                                </label>
                                            </div>`
                                    });
                                    categoryModal.find('.categories-list').html(result);
                                } else {
                                    categoryModal.find('.categories-list').html(`<p class="p-3 text-center">Nenhum rodeio disponível para adicionar</p>`);
                                }

                            }
                        }
                    }
                );
            }

        })(jQuery);
    </script>
@endpush

@extends('admin.layouts.app')
@section('panel')
    <div class="row gy-3">
        <div class="col-12 d-flex justify-content-end gap-2 flex-wrap">
            <div class="bulkStatusBtnArea pointer-events-none">
                <button class="btn btn-outline--info h-100" data-bs-toggle="dropdown" type="button" aria-expanded="true">
                    <i class="las la-ellipsis-v"></i>Ação em Massa
                </button>

                <div class="dropdown-menu" data-popper-placement="bottom-end">
                    <button class="dropdown-item confirmationBtn enableAction" data-question="Are you sure to enable the selected leagues?" data-action="">
                        <i class="la la-eye"></i> Enable
                    </button>
                    <button class="dropdown-item confirmationBtn disableAction" data-question="Are you sure to disable the selected leagues?" data-action="">
                        <i class="la la-eye-slash"></i> Disable
                    </button>
                </div>
            </div>

            <button class="btn btn--dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterData" aria-controls="filterData"> <i class="la la-sliders"></i> Filtrar</button>
        </div>

        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="checkbox" class="form-check-input m-0" id="bulkSelection">
                                            Name
                                        </div>
                                    </th>
                                    <th>Category</th>
                                    <th>Has Outrights</th>
                                    <th>In Season</th>
                                    <th>Status</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($leagues as $league)
                                    <tr>
                                        <td>
                                            <div class="d-flex gap-2 justify-content-end justify-content-lg-start">
                                                <input type="checkbox" value="{{ $league->id }}" class="bulkStatus">
                                                <div class="user gap-2">
                                                    <div class="thumb">
                                                        <img src="{{ $league->logo() }}" alt="image">
                                                    </div>
                                                    <div>{{ __($league->name) }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <td>{{ __(@$league->category->name) }}</td>

                                        <td>
                                            {{ __($league->has_outrights ? 'Yes' : 'No') }}
                                        </td>

                                        <td>
                                            @php echo $league->apiStatusBadge @endphp
                                        </td>
                                        <td>
                                            @php echo $league->statusBadge @endphp
                                        </td>
                                        <td>
                                            @php
                                                $league->image_with_path = getImage(getFilePath('league') . '/' . $league->image, getFileSize('league'));
                                            @endphp
                                            <div class="button--group">
                                                <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn editBtn" data-category_id="{{ $league->rodeio_id }}" data-image="{{ $league->image_with_path }}" data-resource="{{ $league }}" data-modal_title="Editar League">
                                                    <i class="la la-pencil"></i>Editar
                                                </button>

                                                @if ($league->status == Status::DISABLE)
                                                    <button class="btn btn-sm btn-outline--success confirmationBtn" data-question="Are you sure to enable this league?" data-action="{{ route('admin.league.status', $league->id) }}">
                                                        <i class="la la-eye"></i> Enable
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-outline--danger confirmationBtn" data-question="Are you sure to disable this league?" data-action="{{ route('admin.league.status', $league->id) }}">
                                                        <i class="la la-eye-slash"></i> Disable
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
                        </table>
                    </div>
                </div>

                @if ($leagues->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($leagues) }}
                    </div>
                @endif
            </div>
        </div>
    </div>


    <div class="offcanvas offcanvas-end" tabindex="-1" id="filterData" aria-labelledby="filterDataLabel">
        <div class="offcanvas-header">
            <h5 id="filterDataLabel">Filtrar Data</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form action="" method="get" id="filterForm">
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select name="category_id" id="category_id" class="form-control">
                        <option value="" selected>Todos</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>


                <div class="form-group">
                    <label for="has_outrights">Has Outrights</label>
                    <select name="has_outrights" id="has_outrights" class="form-control">
                        <option value="" selected>Todos</option>
                        <option value="{{ Status::YES }}" @selected(request('has_outrights') == strval(Status::YES))>Sim</option>
                        <option value="{{ Status::NO }}" @selected(request('has_outrights') == strval(Status::NO))>Não</option>
                    </select>
                </div>

                @if (!(Route::is('admin.league.api.enabled') || Route::is('admin.league.manual.enabled')))
                    <div class="form-group">
                        <label>Has API Sport Chave</label>
                        <select name="odds_api_sport_key" class="form-control">
                            <option value="">...</option>
                            <option value="{{ Status::YES }}" @selected(request('odds_api_sport_key') == strval(Status::YES))>Sim</option>
                            <option value="{{ Status::NO }}" @selected(request('odds_api_sport_key') == strval(Status::NO))>Não</option>

                        </select>
                    </div>
                @endif

                @if (Route::is('admin.league.index'))
                    <div class="form-group">
                        <label for="status">In Season</label>
                        <select name="api_status" id="api_status" class="form-control">
                            <option value="">Any</option>
                            <option value="{{ Status::YES }}" @selected(request('api_status') == strval(Status::YES))>Sim</option>
                            <option value="{{ Status::NO }}" @selected(request('api_status') == strval(Status::NO))>Não</option>
                        </select>
                    </div>
                @endif

                @if (!(Route::is('admin.league.inseason.enabled') || Route::is('admin.league.api.enabled') || Route::is('admin.league.manual.enabled') || Route::is('admin.league.inseason.disabled')))
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="{{ Status::ENABLE }}" @selected(request('status') == strval(Status::ENABLE))>Ativado</option>
                            <option value="{{ Status::DISABLE }}" @selected(request('status') == strval(Status::DISABLE))>Desativado</option>
                        </select>
                    </div>
                @endif
            </form>
        </div>

        <div class="position-sticky bottom-0 p-3">
            <button type="submit" class="btn btn--primary w-100 h-45" form="filterForm">Aplicar Filtro</button>
        </div>
    </div>

    {{-- Create or Update Modal --}}
    <div id="cuModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.league.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">

                        <div class="alert alert-info p-3 flex-column" role="alert">
                            <h4>Automatic Add Info</h4>
                            <p>
                                Bolão pode ser adicionados e gerenciados manualmente.
                            </p>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Image</label>
                                    <x-image-uploader image="{{ getImage(getFilePath('league'), getFileSize('league')) }}" class="w-100" type="league" :required=false />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Category</label>
                                    <select name="category_id" class="form-control select2" required>
                                        <option value="">Select One</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ __($category->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" class="form-control makeSlug" name="name" value="{{ old('name') }}" required />
                                </div>

                                <div class="form-group">
                                    <label>Short Name</label>
                                    <input type="text" class="form-control" name="short_name" value="{{ old('short_name') }}" required />
                                </div>

                                <div class="form-group">
                                    <label>API Sport Chave</label>
                                    <input type="text" class="form-control" name="odds_api_sport_key" value="{{ old('odds_api_sport_key') }}" />
                                </div>

                                <div class="form-group">
                                    <label>Slug</label>
                                    <input type="text" class="form-control checkSlug" name="slug" value="{{ old('slug') }}" required />
                                    <code>Spaces are not allowed</code>
                                </div>

                                <div class="form-group">
                                    <label>Has Outrights</label> <small class="text-muted" title="Final outcome of a tournament or competition."><i class="la la-info-circle"></i></small>
                                    <select name="has_outrights" class="form-control">
                                        <option value="{{ Status::NO }}">Não</option>
                                        <option value="{{ Status::YES }}">Sim</option>
                                    </select>

                                    <small class="text--danger outrights-info d-none"><i class="la la-info-circle"></i> You cant change the value of outrights for automatically added league.</small>
                                </div>


                                <div class="form-group">
                                    <label>In Season</label>
                                    <select name="api_status" class="form-control">
                                        <option value="{{ Status::YES }}">Sim</option>
                                        <option value="{{ Status::NO }}">Não</option>
                                    </select>

                                    <small class="text--danger"><i class="la la-info-circle"></i> Gerenciado manualmente.</small>
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
@endsection

@push('breadcrumb-plugins')
    <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn" data-modal_title="Add New League">
        <i class="las la-plus"></i>Adicionar Novo
    </button>
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/cu-modal.js') }}"></script>
@endpush

@push('style')
    <style>
        .bulkStatusBtnArea.pointer-events-none {
            opacity: 0.5;
        }

        .form-check-input {
            border: 1px solid #fff;
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

            let modal = $('#cuModal');

            $('.editBtn').on('click', function() {

                modal.find('select[name=category_id]').val($(this).data('category_id')).change();
                modal.find('[name=image]').removeAttr('required');
                modal.find('[name=image]').closest('.form-group').find('label').first().removeClass('required');
                modal.find('.image-upload-preview').attr('style', `background-image: url(${$(this).data('image')})`);

                if ($(this).data('resource').manually_added == '{{ Status::NO }}') {
                    $('.outrights-info').removeClass('d-none');
                    modal.find('[name=has_outrights]').attr('readonly', true);
                } else {
                    modal.find('[name=has_outrights]').removeAttr('readonly');
                }

                modal.find('[name=odds_api_sport_key]').attr('readonly', $(this).data('resource').manually_added == '{{ Status::NO }}');
            });

            var placeHolderImage = "{{ getImage(getFilePath('league'), getFileSize('league')) }}";

            $('#cuModal').on('hidden.bs.modal', function() {
                $('.outrights-info').addClass('d-none');
                $('#cuModal form')[0].reset();
                modal.find('select[name=category_id]').val('').change();
                modal.find('.image-upload-preview').attr('style', `background-image: url(${placeHolderImage})`);
                modal.find('[name=image]').attr('required', 'required');
                modal.find('[name=image]').closest('.form-group').find('label').first().addClass('required');
            });

            $('#bulkSelection').on('click', function() {
                $('.bulkStatus').prop('checked', this.checked);
                generateBulkStatusUrl();
            });


            $('.bulkStatus').on('click', function() {
                generateBulkStatusUrl();
            });


            function generateBulkStatusUrl() {
                let status = $('.bulkStatus:checked').map(function() {
                    return $(this).val();
                }).get().join(',');

                const bulkSelection = $('.bulkStatusBtnArea');

                if (status.length > 0) {
                    bulkSelection.removeClass('pointer-events-none');
                    let enableUrl = '{{ url('admin/leagues/bulk/status') }}' + '/{{ Status::ENABLE }}/' + status;
                    let disableUrl = '{{ url('admin/leagues/bulk/status') }}' + '/{{ Status::DISABLE }}/' + status;
                    bulkSelection.find(`.enableAction`).attr('data-action', enableUrl);
                    bulkSelection.find(`.disableAction`).attr('data-action', disableUrl);
                } else {
                    bulkSelection.addClass('pointer-events-none');
                    $('#bulkSelection').prop('checked', false);
                    bulkSelection.find(`.enableAction`).attr('data-action', '');
                    bulkSelection.find(`.disableAction`).attr('data-action', '');
                }
            }

        })(jQuery);
    </script>
@endpush

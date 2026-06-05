<div id="cronModal" class="modal fade cron-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="exampleModalLongTitle"><i class="las la-clock text--primary"></i>
                        Comandos de Cron
                    </h5>
                    <a href="{{ route('admin.cron.index') }}" class="text--primary text-decoration-underline">Ver instruções detalhadas</a>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <div class="modal-body px-0">

                <div class="list-group list-group-flush">
                    @foreach (App\Models\CronJob::get() as $cron)
                        <div class="list-group-item">
                            <label class="fw-semibold">{{ $cron->name }}</label>
                            <div class="input-group mb-1">
                                <input type="text" class="form-control form-control-lg" value="{{ route('home') . '/' . $cron->url }}" readonly>
                            </div>
                            <small><span>Recomendação de intervalo</span>: <span class="fw-semibold text--info">{{ $cron->interval_info }}</span></small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('style')
    <style>
        .form-control[readonly],
        .form-control[disabled] {
            background-color: rgba(246, 246, 246, 1);
            pointer-events: none;
            border: none;
            border-radius: 5px !important;
        }

        .form-control:focus {
            box-shadow: none;
        }
    </style>
@endpush

@push('script')
@endpush

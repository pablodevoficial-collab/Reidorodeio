@extends('admin.layouts.master')
@section('content')
    <div class="container-fluid py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <div class="fw-semibold" style="font-size:1.05rem;">{{ $pageTitle ?? 'Popout' }}</div>
                @isset($pageSubtitle)
                    <small class="text-muted">{{ $pageSubtitle }}</small>
                @endisset
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline--dark" onclick="window.close()">
                    <i class="la la-times"></i> Fechar
                </button>
            </div>
        </div>

        @yield('panel')
    </div>
@endsection

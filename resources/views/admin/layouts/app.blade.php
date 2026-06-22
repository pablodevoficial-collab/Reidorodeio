@extends('admin.layouts.master')
@section('content')
    @php
        $sidenav = file_get_contents(resource_path('views/admin/partials/sidenav.json'));
    @endphp
    
    <!-- Filtros SVG para efeitos visuais -->
    <svg width="0" height="0" aria-hidden="true" focusable="false" style="position:fixed">
        <filter id="rr-glow-0" x="-50%" y="-50%" width="200%" height="200%">
            <feGaussianBlur stdDeviation="4" result="blur"></feGaussianBlur>
            <feMerge>
                <feMergeNode in="blur"></feMergeNode>
                <feMergeNode in="SourceGraphic"></feMergeNode>
            </feMerge>
        </filter>

        <filter id="rr-glow-1" x="-50%" y="-50%" width="200%" height="200%">
            <feTurbulence type="fractalNoise" baseFrequency="0.015" numOctaves="2" result="turb"></feTurbulence>
            <feDisplacementMap in="SourceGraphic" in2="turb" scale="12" xChannelSelector="R" yChannelSelector="G" result="distort"></feDisplacementMap>
            <feGaussianBlur in="distort" stdDeviation="3" result="blur"></feGaussianBlur>
            <feMerge>
                <feMergeNode in="blur"></feMergeNode>
                <feMergeNode in="SourceGraphic"></feMergeNode>
            </feMerge>
        </filter>
    </svg>

    <!-- page-wrapper start -->
    <div class="page-wrapper default-version">
        @include('admin.partials.sidenav')
        @include('admin.partials.topnav')

        <div class="container-fluid">
            <div class="body-wrapper">
                <div class="bodywrapper__inner">
                    @include('admin.partials.breadcrumb')

                    @yield('panel')
                </div><!-- bodywrapper__inner end -->
            </div><!-- body-wrapper end -->
        </div>
    </div>
@endsection

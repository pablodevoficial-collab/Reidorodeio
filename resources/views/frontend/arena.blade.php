@extends('frontend.layouts.app')

@section('body-class', 'front-shell')

@section('page-style')
<link rel="stylesheet" href="{{ versionedAsset('assets/frontend/css/arena-hub.css', (string) @filemtime(public_path('assets/frontend/css/arena-hub.css'))) }}">
<link rel="stylesheet" href="{{ versionedAsset('assets/frontend/css/arena-hub-mobile.css', (string) @filemtime(public_path('assets/frontend/css/arena-hub-mobile.css'))) }}">
@endsection

@section('content')
<main
    class="arena-shell"
    data-arena-app
    data-has-event="{{ $hasArenaEvent ? 'true' : 'false' }}"
    data-event-id="{{ $arenaEvent['id'] ?? '' }}"
    data-event-name="{{ $arenaEvent['label'] ?? '' }}"
    data-event-status="{{ $arenaEvent['status_label'] ?? '' }}"
    data-event-start="{{ $arenaEvent['start_label'] ?? '' }}"
    data-event-end="{{ $arenaEvent['end_label'] ?? '' }}"
    data-arena-status-url="{{ route('arena.status') }}"
    data-leagues-url="{{ url('/api/fantasy/leagues') }}"
    data-login-url="{{ route('user.login') }}"
    data-register-url="{{ route('user.register') }}"
    data-check-user-url="{{ route('user.checkUser') }}"
    data-profile-url="{{ route('user.profile.update') }}"
    data-profile-api-url="{{ url('/api/fantasy/user/profile') }}"
    data-support-url="{{ $supportUrl }}"
    data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
>
    @include('frontend.partials.arena.utility-bar')
    @include('frontend.partials.arena.hero')
    @include('frontend.partials.arena.leagues')
</main>

@include('frontend.partials.arena.modals.rules')
@include('frontend.partials.arena.modals.profile')
@include('frontend.partials.arena.modals.pix')
@guest
@include('frontend.partials.arena.modals.register')
@endguest
@endsection

@section('page-script')
<script src="{{ versionedAsset('assets/frontend/js/arena-live.js', (string) @filemtime(public_path('assets/frontend/js/arena-live.js'))) }}"></script>
<script src="{{ versionedAsset('assets/frontend/js/arena-hub.js', (string) @filemtime(public_path('assets/frontend/js/arena-hub.js'))) }}"></script>
<script src="{{ versionedAsset('assets/frontend/js/arena-profile.js', (string) @filemtime(public_path('assets/frontend/js/arena-profile.js'))) }}"></script>
<script src="{{ versionedAsset('assets/frontend/js/app.js', (string) @filemtime(public_path('assets/frontend/js/app.js'))) }}"></script>
@endsection

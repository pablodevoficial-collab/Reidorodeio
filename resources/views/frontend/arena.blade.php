@extends('frontend.layouts.app')

@section('body-class', 'front-shell')

@section('page-style')
<link rel="stylesheet" href="{{ versionedAsset('assets/frontend/css/arena-hub.css', (string) @filemtime(public_path('assets/frontend/css/arena-hub.css'))) }}">
<link rel="stylesheet" href="{{ versionedAsset('assets/frontend/css/arena-hub-mobile.css', (string) @filemtime(public_path('assets/frontend/css/arena-hub-mobile.css'))) }}">
<link rel="stylesheet" href="{{ versionedAsset('assets/frontend/css/arena-entry.css', (string) @filemtime(public_path('assets/frontend/css/arena-entry.css'))) }}">
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
    data-fantasy-base-url="{{ url('/api/fantasy') }}"
    data-support-url="{{ $supportUrl }}"
    data-logout-url="{{ auth()->check() ? route('user.logout') : '' }}"
    data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
>
    <section class="arena-layout">
        @include('frontend.partials.arena.utility-bar')
        <div class="arena-main">
            @include('frontend.partials.arena.leagues')
        </div>
    </section>
</main>

@include('frontend.partials.arena.modals.rules')
@include('frontend.partials.arena.modals.profile')
@include('frontend.partials.arena.modals.pix')
@include('frontend.partials.arena.modals.entry')
@include('frontend.partials.arena.modals.ranking')
@guest
@include('frontend.partials.arena.modals.register')
@endguest
@endsection

@section('page-script')
<script src="{{ versionedAsset('assets/frontend/js/arena-live.js', (string) @filemtime(public_path('assets/frontend/js/arena-live.js'))) }}"></script>
<script src="{{ versionedAsset('assets/frontend/js/arena-hub.js', (string) @filemtime(public_path('assets/frontend/js/arena-hub.js'))) }}"></script>
<script src="{{ versionedAsset('assets/frontend/js/arena-entry.js', (string) @filemtime(public_path('assets/frontend/js/arena-entry.js'))) }}"></script>
<script src="{{ versionedAsset('assets/frontend/js/arena-profile.js', (string) @filemtime(public_path('assets/frontend/js/arena-profile.js'))) }}"></script>
@endsection

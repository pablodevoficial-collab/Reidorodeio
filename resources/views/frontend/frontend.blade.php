@php
    $pageTitle = $pageTitle ?? 'BolÃ£o Rei do Rodeio';
    $hideChrome = true;
    $bodyClass = trim(($bodyClass ?? '') . ' rr-bolao-shell');
    $currentUser = auth()->user();
    $remindersDB = [];
    $fantasyReminderSlotsDB = [];
    $totalWon = 0;
    $totalBalance = 0;

    if ($currentUser) {
        try {
            $remindersDB = app(\App\Services\RodeioEmailReminderService::class)
                ->subscribedRodeioIdsFor($currentUser, $currentUser->email ?? null);
        } catch (\Throwable $e) {
            $remindersDB = [];
        }
        try {
            $fantasyReminderSlotsDB = app(\App\Services\FantasyLeagueOpeningReminderService::class)
                ->subscribedSlotsFor($currentUser, $currentUser->email ?? null);
        } catch (\Throwable $e) {
            $fantasyReminderSlotsDB = [];
        }
        try {
            $totalWon = \App\Models\FantasyTeam::where('user_id', $currentUser->id)->sum('prize_won');
        } catch (\Throwable $e) {}
        try {
            $totalBalance = (float)($currentUser->balance ?? 0);
        } catch (\Throwable $e) {}
    }

    $authPayload = [
        'authenticated' => auth()->check(),
        'name' => $currentUser ? ($currentUser->username ?: ($currentUser->firstname ?? 'Competidor')) : null,
        'username' => $currentUser->username ?? null,
        'has_real_email' => $currentUser && method_exists($currentUser, 'hasRealEmail') ? $currentUser->hasRealEmail() : false,
        'profile_complete' => $currentUser
            && ((method_exists($currentUser, 'hasRealEmail') ? $currentUser->hasRealEmail() : !empty($currentUser->email)))
            && trim((string) ($currentUser->username ?? '')) !== ''
            && trim((string) ($currentUser->mobile ?? '')) !== ''
            && !empty($currentUser->birthdate)
            && trim((string) ($currentUser->pix_key ?? '')) !== '',
        'reminders' => $remindersDB,
        'fantasy_reminder_slots' => $fantasyReminderSlotsDB,
        'total_won' => $totalWon,
        'balance' => $totalBalance,
    ];
@endphp

@extends('frontend.layouts.app')

@include('frontend.partials.bolao.styles')

@push('style')
<style>
    body.rr-bolao-shell {
        margin: 0 !important;
        padding: 0 !important;
    }

    .rr-main {
        width: 100% !important;
        min-height: 100dvh !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .rr-main > .rr-site-shell {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .rr-app {
        gap: 0 !important;
        min-height: 100dvh !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .rr-app,
    .rr-main,
    .rr-main > .rr-site-shell {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
</style>
@endpush

@section('content')
<div class="rr-app" id="rrBolaoFrontend" data-authenticated="{{ $authPayload['authenticated'] ? '1' : '0' }}">
    @include('frontend.partials.bolao.auth-loader')
    @include('frontend.partials.bolao.arena-gateway')
    @include('frontend.partials.bolao.live-stage')
    @include('frontend.partials.bolao.modals')
</div>
@endsection

@include('frontend.partials.bolao.scripts')


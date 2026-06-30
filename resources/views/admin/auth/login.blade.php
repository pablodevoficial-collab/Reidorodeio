@extends('admin.layouts.master')

@section('content')
<div class="rr-admin-login">
    <canvas id="rrParticlesCanvas" class="rr-admin-login__canvas" aria-hidden="true"></canvas>

    <main class="rr-admin-login__shell" aria-labelledby="admin-login-title">
        <section class="rr-admin-login__panel rr-admin-login__panel--brand">
            <div class="rr-admin-login__badge">Admin Bolão</div>
            <img src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="Rei do Rodeio" class="rr-admin-login__logo">
            <h1 id="admin-login-title" class="rr-admin-login__title">Operação central do bolão</h1>
            <p class="rr-admin-login__text">Acesso rápido para gerir bolões, premiações, competidores, rodeios e pagamentos.</p>
            <div class="rr-admin-login__pills">
                <span>Bolões</span>
                <span>Premiações</span>
                <span>Pagamento</span>
            </div>
        </section>

        <section class="rr-admin-login__panel rr-admin-login__panel--form">
            <form action="{{ route('admin.login.submit') }}" method="POST" class="rr-admin-login__form verify-gcaptcha" novalidate>
                @csrf

                <div class="rr-admin-login__heading">
                    <span>Entrar</span>
                    <strong>Administração principal</strong>
                </div>

                @if (session('error'))
                    <div class="alert alert-danger py-2 px-3 mb-3">{{ session('error') }}</div>
                @endif

                @if (session()->has('notify'))
                    @foreach (session('notify') as $msg)
                        @if (is_array($msg) && count($msg) >= 2)
                            <div class="alert alert-{{ $msg[0] === 'success' ? 'success' : 'danger' }} py-2 px-3 mb-3">{{ __($msg[1]) }}</div>
                        @endif
                    @endforeach
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger py-2 px-3 mb-3">{{ $errors->first() }}</div>
                @endif

                <div class="form-group">
                    <label for="username">Usuário</label>
                    <input id="username" type="text" class="form-control" value="{{ old('username') }}" name="username" required autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password">Senha</label>
                    <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password">
                </div>

                <x-captcha />

                <div class="form-group mt-3">
                    <button type="submit" class="btn cmn-btn w-100">Entrar</button>
                </div>
            </form>
        </section>
    </main>
</div>
@endsection

@push('style')
<style>
    .rr-admin-login {
        min-height: 100vh;
        display: grid;
        place-items: center;
        padding: 20px;
        background:
            radial-gradient(circle at top, rgba(249, 115, 22, 0.18), transparent 24%),
            radial-gradient(circle at bottom right, rgba(37, 99, 235, 0.12), transparent 28%),
            linear-gradient(180deg, #050816 0%, #020617 100%);
        overflow: hidden;
    }

    .rr-admin-login__canvas {
        position: fixed;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
        opacity: .55;
    }

    .rr-admin-login__shell {
        position: relative;
        z-index: 1;
        width: min(100%, 1040px);
        display: grid;
        grid-template-columns: 1.05fr .95fr;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 28px;
        overflow: hidden;
        background: rgba(8, 12, 24, 0.86);
        box-shadow: 0 28px 80px rgba(0,0,0,.42);
        backdrop-filter: blur(18px);
    }

    .rr-admin-login__panel {
        padding: 34px;
    }

    .rr-admin-login__panel--brand {
        display: grid;
        align-content: center;
        gap: 16px;
        background:
            radial-gradient(circle at top left, rgba(255,183,86,.2), transparent 36%),
            linear-gradient(180deg, rgba(249,115,22,.92), rgba(234,88,12,.92));
        color: #fff8ea;
    }

    .rr-admin-login__badge {
        width: fit-content;
        padding: 8px 12px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.18);
        background: rgba(255,255,255,.08);
        font-size: .82rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .rr-admin-login__logo {
        width: 124px;
        height: auto;
        filter: drop-shadow(0 12px 18px rgba(0,0,0,.22));
    }

    .rr-admin-login__title {
        margin: 0;
        font-family: 'Teko', sans-serif;
        font-size: clamp(3rem, 6vw, 5.2rem);
        line-height: .88;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .rr-admin-login__text {
        margin: 0;
        max-width: 32ch;
        font-size: 1rem;
        line-height: 1.65;
        color: rgba(255,255,255,.9);
    }

    .rr-admin-login__pills {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .rr-admin-login__pills span {
        padding: 9px 12px;
        border-radius: 999px;
        background: rgba(9, 16, 30, .22);
        border: 1px solid rgba(255,255,255,.15);
        font-size: .82rem;
        font-weight: 700;
    }

    .rr-admin-login__panel--form {
        background: linear-gradient(180deg, rgba(11, 17, 33, .96), rgba(6, 10, 20, .98));
    }

    .rr-admin-login__heading {
        display: grid;
        gap: 4px;
        margin-bottom: 18px;
    }

    .rr-admin-login__heading span {
        color: #fdba74;
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .rr-admin-login__heading strong {
        font-family: 'Teko', sans-serif;
        font-size: 2.6rem;
        line-height: .9;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #fff7ed;
    }

    .rr-admin-login__form {
        display: grid;
        gap: 14px;
    }

    .rr-admin-login__form label {
        color: #e2e8f0;
        font-weight: 800;
        margin-bottom: .45rem;
    }

    .rr-admin-login__form .form-control {
        min-height: 52px;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,.08);
        background: rgba(15,23,42,.92) !important;
        color: #fff !important;
        padding: 0 16px;
    }

    .rr-admin-login__form .form-control:focus {
        border-color: rgba(249,115,22,.65) !important;
        box-shadow: 0 0 0 4px rgba(249,115,22,.12);
    }

    .rr-admin-login__form .cmn-btn {
        min-height: 54px;
        border-radius: 14px;
        border: 0;
        background: linear-gradient(135deg, #fbbf24, #f97316 60%, #ea580c) !important;
        color: #231403 !important;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
        box-shadow: 0 18px 36px rgba(249,115,22,.22);
    }

    @media (max-width: 900px) {
        .rr-admin-login__shell {
            grid-template-columns: 1fr;
        }

        .rr-admin-login__panel {
            padding: 24px;
        }
    }

    @media (max-width: 520px) {
        .rr-admin-login {
            padding: 12px;
        }

        .rr-admin-login__panel {
            padding: 18px;
        }

        .rr-admin-login__logo {
            width: 96px;
        }
    }
</style>
@endpush

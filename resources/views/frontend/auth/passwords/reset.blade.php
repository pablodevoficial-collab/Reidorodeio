@extends('frontend.layouts.app')

@section('content')
<style>
    .rr-reset-section {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        background-image: radial-gradient(circle at 50% 0%, rgba(249, 115, 22, 0.15), transparent 70%);
    }
    .rr-reset-card {
        width: 100%;
        max-width: 420px;
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 2.5rem 2rem;
        backdrop-filter: blur(10px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
    .rr-reset-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    .rr-reset-logo {
        width: 60px;
        height: auto;
        margin-bottom: 1rem;
        filter: drop-shadow(0 0 15px rgba(249, 115, 22, 0.3));
    }
    .rr-reset-title {
        font-family: 'Ethnocentric', sans-serif;
        color: #fff;
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
    }
    .rr-reset-subtitle {
        color: #94a3b8;
        font-size: 0.9rem;
    }
    .rr-form-group {
        margin-bottom: 1.25rem;
    }
    .rr-form-label {
        display: block;
        color: #e2e8f0;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .rr-input-wrapper {
        position: relative;
    }
    .rr-input-wrapper i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
    }
    .rr-form-input {
        width: 100%;
        padding: 0.875rem 1rem 0.875rem 2.75rem;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        color: #fff;
        font-size: 1rem;
        transition: all 0.2s;
    }
    .rr-form-input:focus {
        outline: none;
        border-color: #f97316;
        background: rgba(249, 115, 22, 0.05);
    }
    .rr-btn-submit {
        width: 100%;
        padding: 0.875rem;
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        border: none;
        border-radius: 12px;
        color: #fff;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 1rem;
        box-shadow: 0 4px 6px -1px rgba(249, 115, 22, 0.2);
    }
    .rr-btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(249, 115, 22, 0.3);
    }
    .invalid-feedback {
        color: #ef4444;
        font-size: 0.8rem;
        margin-top: 0.25rem;
        display: block;
    }
</style>

<div class="rr-reset-section">
    <div class="rr-reset-card">
        <div class="rr-reset-header">
            <img src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="Rei do Rodeio" class="rr-reset-logo">
            <h1 class="rr-reset-title">REDEFINIR SENHA</h1>
            <p class="rr-reset-subtitle">Crie uma nova senha para sua conta</p>
        </div>

        <form method="POST" action="{{ route('user.password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="rr-form-group">
                <label class="rr-form-label">Email</label>
                <div class="rr-input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="rr-form-input @error('email') is-invalid @enderror" value="{{ $email ?? old('email') }}" required autofocus placeholder="Seu email cadastrado">
                </div>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="rr-form-group">
                <label class="rr-form-label">Nova Senha</label>
                <div class="rr-input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="rr-form-input @error('password') is-invalid @enderror" required placeholder="Mínimo 6 caracteres">
                </div>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="rr-form-group">
                <label class="rr-form-label">Confirmar Senha</label>
                <div class="rr-input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password_confirmation" class="rr-form-input" required placeholder="Repita a nova senha">
                </div>
            </div>

            <button type="submit" class="rr-btn-submit">
                REDEFINIR SENHA
            </button>
        </form>
    </div>
</div>
@endsection

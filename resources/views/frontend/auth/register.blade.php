@extends('frontend.layouts.app')

@section('content')
<section class="rr-auth-screen">
    <div class="rr-auth-screen__card rr-panel">
        <div class="rr-auth-screen__intro">
            <span class="rr-auth-screen__eyebrow">Cadastro</span>
            <h1>Criar conta no bolão</h1>
            <p>Cadastro direto com CPF, data de nascimento e senha.</p>
        </div>

        @if($errors->any())
            <ul class="rr-errors">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('user.register') }}" class="rr-auth-form">
            @csrf

            <div class="rr-field">
                <label for="cpf">CPF</label>
                <input id="cpf" class="rr-input" type="text" name="cpf" value="{{ old('cpf') }}" placeholder="000.000.000-00" inputmode="numeric" required>
            </div>

            <div class="rr-field">
                <label for="birthdate">Data de nascimento</label>
                <input id="birthdate" class="rr-input" type="date" name="birthdate" value="{{ old('birthdate') }}" max="{{ now()->subYears(18)->format('Y-m-d') }}" required>
            </div>

            <div class="rr-field">
                <label for="password">Senha</label>
                <input id="password" class="rr-input" type="password" name="password" placeholder="Mínimo 6 caracteres" required>
            </div>

            <div class="rr-field">
                <label for="password_confirmation">Confirmar senha</label>
                <input id="password_confirmation" class="rr-input" type="password" name="password_confirmation" placeholder="Repita a senha" required>
            </div>

            <label class="rr-auth-form__check">
                <input type="checkbox" name="agree" value="1" {{ old('agree') ? 'checked' : '' }}>
                <span>Li e aceito os termos de uso.</span>
            </label>

            <button type="submit" class="rr-btn rr-btn--primary rr-auth-form__submit">Criar conta</button>
        </form>

        <div class="rr-auth-screen__footer">
            <a href="{{ route('home') }}">Voltar para o início</a>
            <a href="{{ route('user.login') }}">Já tenho conta</a>
        </div>
    </div>
</section>

<style>
    .rr-auth-screen {
        min-height: calc(100vh - 220px);
        display: grid;
        place-items: center;
    }

    .rr-auth-screen__card {
        width: min(100%, 520px);
        display: grid;
        gap: 22px;
        padding: 28px;
    }

    .rr-auth-screen__intro {
        display: grid;
        gap: 8px;
    }

    .rr-auth-screen__eyebrow {
        color: #fb923c;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
    }

    .rr-auth-screen__intro h1 {
        margin: 0;
        color: #fff7ed;
        font-size: clamp(2rem, 5vw, 2.6rem);
        line-height: 1;
        font-weight: 900;
    }

    .rr-auth-screen__intro p {
        margin: 0;
        color: #94a3b8;
        font-size: 0.98rem;
        line-height: 1.6;
    }

    .rr-auth-form {
        display: grid;
        gap: 16px;
    }

    .rr-auth-form__check {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #cbd5e1;
        font-size: 0.92rem;
        font-weight: 600;
    }

    .rr-auth-form__submit {
        width: 100%;
    }

    .rr-auth-screen__footer {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .rr-auth-screen__footer a {
        color: #fb923c;
        font-size: 0.92rem;
        font-weight: 700;
    }
</style>
@endsection

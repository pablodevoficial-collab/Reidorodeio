@extends('frontend.layouts.app')

@php
    $pageTitle = $pageTitle ?? 'Bolão Rei do Rodeio';
@endphp

@section('content')
<style>
  .rr-bolao-launch {
    min-height: calc(100vh - 220px);
    display: grid;
    place-items: center;
    padding: 0 0 1rem;
  }
  .rr-bolao-launch__card {
    width: min(100%, 960px);
    display: grid;
    gap: 1.5rem;
    padding: 2.1rem 1.35rem;
    border-radius: 32px;
    border: 1px solid rgba(249, 115, 22, .18);
    background:
      radial-gradient(circle at top right, rgba(37, 99, 235, .14), transparent 28%),
      radial-gradient(circle at bottom left, rgba(249, 115, 22, .18), transparent 34%),
      linear-gradient(180deg, rgba(15, 23, 42, .98), rgba(8, 12, 24, .99));
    box-shadow: 0 28px 70px rgba(0, 0, 0, .28), inset 0 1px 0 rgba(255,255,255,.06);
    text-align: center;
  }
  .rr-bolao-launch__logo {
    width: min(180px, 46vw);
    margin: 0 auto;
    filter: drop-shadow(0 18px 34px rgba(249,115,22,.28));
  }
  .rr-bolao-launch__title {
    margin: 0;
    color: #fff7ed;
    font-size: clamp(2rem, 5vw, 4rem);
    line-height: .96;
    letter-spacing: -.06em;
    font-weight: 900;
  }
  .rr-bolao-launch__copy {
    margin: 0 auto;
    max-width: 700px;
    color: rgba(255, 237, 213, .82);
    font-size: clamp(.96rem, 2vw, 1.08rem);
    line-height: 1.6;
    font-weight: 700;
  }
  .rr-bolao-launch__highlights {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: .65rem;
    width: min(100%, 720px);
    margin: 0 auto;
  }
  .rr-bolao-launch__pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    padding: 0 .95rem;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, .14);
    background: rgba(15, 23, 42, .58);
    color: #f8fafc;
    font-size: .8rem;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
  }
  .rr-bolao-launch__actions {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .9rem;
    width: min(100%, 460px);
    margin: 0 auto;
  }
  .rr-bolao-launch__btn {
    min-height: 56px;
    border: 0;
    border-radius: 18px;
    font-size: .92rem;
    font-weight: 900;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: #fff;
    box-shadow: 0 16px 30px rgba(0,0,0,.2);
  }
  .rr-bolao-launch__btn--primary {
    background: linear-gradient(135deg, #f59e0b, #ea580c);
  }
  .rr-bolao-launch__btn--secondary {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
  }
  @media (max-width: 640px) {
    .rr-bolao-launch__card {
      padding: 1.65rem 1rem 1.45rem;
      border-radius: 26px;
    }
    .rr-bolao-launch__actions {
      grid-template-columns: 1fr;
      width: 100%;
    }
  }
</style>

<section class="rr-bolao-launch">
  <div class="rr-bolao-launch__card">
    <img class="rr-bolao-launch__logo" src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="Rei do Rodeio">
    <h1 class="rr-bolao-launch__title">Entre no bolão e dispute o topo.</h1>
    <p class="rr-bolao-launch__copy">
      Monte sua equipe, acompanhe o ranking ao vivo e entre na disputa pelos prêmios do evento.
      Quem entra agora larga na frente.
    </p>
    <div class="rr-bolao-launch__highlights" aria-label="Destaques do bolão">
      <span class="rr-bolao-launch__pill">Prêmio real</span>
      <span class="rr-bolao-launch__pill">Vagas limitadas</span>
      <span class="rr-bolao-launch__pill">Ranking em tempo real</span>
    </div>
    <div class="rr-bolao-launch__actions">
      <a href="{{ route('user.login') }}" class="rr-bolao-launch__btn rr-bolao-launch__btn--primary">
        Entrar e participar
      </a>
      <a href="{{ route('user.register') }}" class="rr-bolao-launch__btn rr-bolao-launch__btn--secondary">
        Criar conta agora
      </a>
    </div>
  </div>
</section>
@endsection

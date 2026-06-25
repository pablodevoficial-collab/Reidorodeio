<header class="hero" id="topo">
    <nav class="topbar">
        <a class="brand" href="{{ route('home') }}">
            <img src="{{ asset('assets/images/logo/logorei.png') }}" alt="Rei do Rodeio">
        </a>
        <div class="topbar__actions">
            <a class="ghost-link" href="#como-funciona">Como funciona</a>
            <a class="ghost-link" href="#ranking">Ranking</a>
            <a class="ghost-link" href="/admin">Admin</a>
        </div>
    </nav>

    <section class="hero__content">
        <div class="hero__copy">
            <span class="eyebrow">Arena pronta para o bol&atilde;o</span>
            <h1>Monte sua equipe com clima de rodeio grande e entrada sem enrola&ccedil;&atilde;o.</h1>
            <p>
                Um front direto ao ponto para o la&ccedil;o comprido: escolha o evento, veja a disputa e entre na montagem do time
                com a sensa&ccedil;&atilde;o de arena ao vivo.
            </p>
            <div class="hero__cta">
                <a class="button button--gold" href="#eventos" data-scroll-target="eventos">Montar equipe agora</a>
                <a class="button button--ghost" href="#como-funciona" data-scroll-target="como-funciona">Ver a jornada</a>
            </div>
            <ul class="hero__stats">
                <li><strong>3 toques</strong><span>para cair na montagem</span></li>
                <li><strong>Ao vivo</strong><span>cara de arena noturna</span></li>
                <li><strong>Mobile first</strong><span>feito para entrar r&aacute;pido</span></li>
            </ul>
        </div>

        <aside class="hero__panel">
            <div class="feature-card">
                <span class="feature-card__label">Evento em destaque</span>
                <h2>Circuito Rei do Rodeio</h2>
                <p>Escolha o bol&atilde;o principal, confira a fase e siga direto para escalar.</p>
                <div class="feature-card__meta">
                    <span>La&ccedil;o comprido</span>
                    <span>Noite decisiva</span>
                    <span>Premia&ccedil;&atilde;o em alta</span>
                </div>
                <a class="button button--panel" href="#eventos" data-scroll-target="eventos">Escolher bol&atilde;o</a>
            </div>
        </aside>
    </section>
</header>

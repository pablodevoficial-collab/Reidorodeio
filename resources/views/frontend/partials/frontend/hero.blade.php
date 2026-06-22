<div class="rr-bolao-hero" x-data x-init="
    setTimeout(() => {
        GsapAnimations.fadeUpEntry(document.querySelector('.rr-bolao-hero__content'), 0.2);
        GsapAnimations.slideInFromRight(document.querySelector('.rr-bolao-hero__center'), 0.4);
        GsapAnimations.slideInFromLeft(document.querySelector('.rr-bolao-hero__aside'), 0.3);
        GsapAnimations.glow(document.querySelector('.rr-bolao-hero__event-glow'));
    }, 100)
">
    <div class="rr-bolao-hero__grid">
        <div class="rr-bolao-hero__content">
            <span class="rr-bolao-pill rr-bolao-pill--soft">
                <i class="fas fa-ticket"></i>
                ganhe dinheiro real
            </span>

            <p class="rr-bolao-hero__copy">
                Bolão oficial com visual de arena e estrutura nova por baixo. A linguagem do hub antigo foi
                reaproveitada aqui, mas agora só para a jornada de bolão.
            </p>

            <div class="rr-bolao-hero__actions">
                <a href="{{ route('user.register') }}" class="rr-btn rr-btn--primary hover-lift btn-animate">Criar conta</a>
                <a href="{{ route('user.login') }}" class="rr-btn rr-btn--secondary hover-lift btn-animate">Entrar</a>
            </div>

            <div class="rr-bolao-hero__badges stagger-container" data-aos="fade-up" data-aos-delay="200">
                <span class="rr-bolao-hero__badge"><i class="fas fa-trophy"></i> prêmio real</span>
                <span class="rr-bolao-hero__badge"><i class="fas fa-ticket"></i> vagas limitadas</span>
                <span class="rr-bolao-hero__badge"><i class="fas fa-chart-line"></i> ranking em tempo real</span>
            </div>
        </div>

        <div class="rr-bolao-hero__center">
            <span class="rr-bolao-pill">janela curta do bolão</span>

            <div class="rr-bolao-hero__event"
                 x-data="{ isHovering: false }"
                 @mouseenter="isHovering = true; GsapAnimations.pulse(document.querySelector('.rr-bolao-hero__event-card'))"
                 @mouseleave="isHovering = false"
                 data-aos="zoom-in"
                 data-aos-duration="800">
                <div class="rr-bolao-hero__event-glow" aria-hidden="true"></div>

                <div class="rr-bolao-hero__event-card">
                    <div class="rr-bolao-hero__event-frame">
                        <img class="rr-bolao-hero__event-logo" src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="CTG os Praianos">
                    </div>
                    <div class="rr-bolao-hero__event-name">CTG os Praianos</div>
                </div>

                <div class="rr-bolao-hero__dots" aria-hidden="true">
                    <span class="rr-bolao-hero__dot is-active"></span>
                    <span class="rr-bolao-hero__dot"></span>
                </div>

                <div class="rr-bolao-hero__countdown">
                    <small>começa em</small>
                    <span>12d 8h 22m</span>
                </div>

                <button type="button" class="rr-bolao-hero__selector">
                    <span><i class="fas fa-filter"></i> Selecione uma modalidade</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>

        <aside class="rr-bolao-hero__aside">
            <span class="rr-bolao-pill rr-bolao-pill--green">
                <i class="fas fa-users"></i>
                monte sua equipe
            </span>

            <div class="rr-bolao-stage">
                <img class="rr-bolao-stage__logo" src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="Rei do Rodeio">

                <div class="rr-bolao-stage__card">
                    <strong>Bolão puro</strong>
                    <span>Visual herdado da arena antiga, sem voltar para premium, stats ou X1.</span>
                </div>

                <div class="rr-bolao-stage__card">
                    <strong>Base nova</strong>
                    <span>Hero, cards e ranking foram quebrados em blades pequenas para destravar evolução.</span>
                </div>
            </div>
        </aside>
    </div>
</div>

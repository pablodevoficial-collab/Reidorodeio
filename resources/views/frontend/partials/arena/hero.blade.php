<section class="arena-stage {{ $hasArenaEvent ? '' : 'is-empty' }}">
    <div class="arena-stage__copy">
        <span class="arena-stage__kicker">{{ $hasArenaEvent ? ($arenaEvent['status_label'] ?? 'Arena aberta') : 'Arena em espera' }}</span>
        <h1>{{ $hasArenaEvent ? ($arenaEvent['label'] ?? 'Evento oficial') : 'Nenhum evento no momento' }}</h1>
        <p>
            {{ $hasArenaEvent ? 'Bolao oficial no ar com visual premium, atalhos rapidos e cards prontos para a disputa.' : 'Assim que o proximo rodeio oficial abrir, a arena libera os botoes e os boloes automaticamente.' }}
        </p>
        <div class="arena-stage__meta">
            <span data-stage-meta="status">{{ $hasArenaEvent ? ($arenaEvent['status_label'] ?? 'Programado') : 'Aguardando evento' }}</span>
            <span data-stage-meta="time">{{ $arenaEvent['start_label'] ?? 'Sem horario definido' }}</span>
            <span data-stage-meta="count">Carregando boloes</span>
        </div>
        <div class="arena-stage__cta">
            @guest
            <button class="arena-button arena-button--solid" type="button" data-open-register>Receber aviso de abertura</button>
            @else
            <button class="arena-button arena-button--solid" type="button" data-open-profile>Completar perfil e premios</button>
            @endguest
        </div>
    </div>
    <div class="arena-stage__crest">
        <div class="arena-stage__halo"></div>
        <img src="{{ asset('assets/images/logo/logorei.png') }}" alt="Logo Rei do Rodeio">
    </div>
</section>

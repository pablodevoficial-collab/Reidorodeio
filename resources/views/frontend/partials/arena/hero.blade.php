<section class="arena-stage {{ $hasArenaEvent ? '' : 'is-empty' }}">
    <span class="arena-stage__kicker">Organizador destacado</span>
    <div class="arena-stage__organizer">
        <div class="arena-stage__organizer-logo" data-organizer-logo>
            <img src="{{ asset('assets/images/logo/logorei.png') }}" alt="Organizador da arena">
        </div>
        <div class="arena-stage__organizer-copy">
            <strong data-organizer-name>{{ $hasArenaEvent ? ($arenaEvent['label'] ?? 'Rei do Rodeio') : 'Rei do Rodeio' }}</strong>
            <span data-organizer-meta>{{ $hasArenaEvent ? ($arenaEvent['status_label'] ?? 'Programado') : 'Nenhum evento no momento' }}</span>
        </div>
    </div>
</section>

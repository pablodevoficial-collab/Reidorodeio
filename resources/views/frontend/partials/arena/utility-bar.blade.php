<section class="arena-utility">
    <div class="arena-utility__brand">
        <img src="{{ asset('assets/images/logo/logorei.png') }}" alt="Rei do Rodeio">
        <div>
            <span>Rei do Rodeio</span>
            <strong>Arena oficial do bolao</strong>
        </div>
    </div>
    <div class="arena-utility__actions">
        <button class="arena-chip" type="button" data-open-rules>Regras</button>
        <a class="arena-chip" href="{{ $supportUrl }}" target="_blank" rel="noopener">Suporte</a>
        <button class="arena-chip" type="button" data-open-profile>{{ auth()->check() ? 'Perfil' : 'Cadastre-se' }}</button>
        <button class="arena-chip arena-chip--accent" type="button" data-open-pix>{{ auth()->check() ? 'Pix' : 'Entrar' }}</button>
    </div>
</section>

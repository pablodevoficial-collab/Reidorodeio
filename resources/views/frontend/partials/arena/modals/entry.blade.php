<div class="rr-modal" hidden data-entry-modal>
    <div class="rr-modal__backdrop" data-entry-close></div>
    <div class="rr-modal__dialog arena-sheet arena-entry">
        <button class="rr-modal__close" type="button" data-entry-close>&times;</button>
        <div class="arena-entry__head">
            <span class="arena-entry__step-badge">Passo 1 de 3</span>
            <span class="arena-board__eyebrow">Monte sua equipe</span>
            <h2 data-entry-title>Entrar na disputa</h2>
            <p data-entry-subtitle>Escolha quatro competidores para liberar sua entrada.</p>
        </div>

        <section class="arena-entry__stage" data-entry-stage="picker">
            <label class="arena-entry__search">
                <span class="arena-entry__search-icon" aria-hidden="true"></span>
                <input type="text" placeholder="Pesquisar competidor" data-entry-search>
            </label>
            <div class="arena-entry__feedback" data-entry-feedback></div>
            <div class="arena-entry__list" data-entry-list></div>
            <div class="arena-entry__picker-footer">
                <div class="arena-entry__selection-head">
                    <strong>Seus 4 slots</strong>
                    <span data-entry-counter>0/4 selecionados</span>
                </div>
                <div class="arena-entry__slots" data-entry-slots></div>
                <button class="arena-button arena-button--solid" type="button" disabled data-entry-pay>
                    <span>Pagar e entrar</span>
                </button>
            </div>
        </section>

        <section class="arena-entry__stage" hidden data-entry-stage="payment">
            <div class="arena-entry__head">
                <span class="arena-entry__step-badge">Passo 2 de 3</span>
            </div>
            <div class="arena-entry__payment-card">
                <strong data-entry-payment-title>Pix gerado com sucesso</strong>
                <p data-entry-payment-copy>Escaneie o QR Code ou copie o código Pix abaixo.</p>
                <div class="arena-entry__payment-status" data-entry-payment-status></div>
                <div class="arena-entry__qr" data-entry-qr-wrap>
                    <img alt="QR Code Pix" hidden data-entry-qr-image>
                    <pre hidden data-entry-qr-text></pre>
                </div>
                <button class="arena-button arena-button--ghost" type="button" data-entry-copy-pix>Copiar Pix</button>
                <div class="arena-entry__feedback" data-entry-payment-feedback></div>
            </div>
        </section>

        <section class="arena-entry__stage" hidden data-entry-stage="success">
            <div class="arena-entry__head">
                <span class="arena-entry__step-badge">Passo 3 de 3</span>
            </div>
            <div class="arena-entry__success">
                <div class="arena-entry__check" aria-hidden="true"></div>
                <strong>Você está participando!</strong>
                <p>Pagamento confirmado e equipe liberada na arena.</p>
                <button class="arena-button arena-button--solid" type="button" hidden data-entry-complete-profile>Completar perfil para receber prêmio</button>
            </div>
        </section>
    </div>
</div>

<section class="rr-mobile-only rr-mobile-footer" aria-label="Regras e suporte">
    <div class="rr-mobile-footer__actions">
        <button type="button" class="rr-mobile-footer__btn rr-mobile-footer__btn--rules" id="rrMobileRulesBtn">
            <i class="fas fa-book-open"></i>
            <span>Regras</span>
        </button>
        <a href="https://wa.me/5547997953323?text={{ urlencode('Olá! Preciso de ajuda com o bolão do Rei do Rodeio.') }}" target="_blank" rel="noopener" class="rr-mobile-footer__btn rr-mobile-footer__btn--support">
            <i class="fab fa-whatsapp"></i>
            <span>Suporte</span>
        </a>
    </div>
</section>

<style>
    .rr-mobile-footer {
        margin-top: 0;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .rr-mobile-footer__actions {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .rr-mobile-footer__btn {
        min-height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 12px;
        color: #f8fafc;
        font-weight: 800;
        font-size: 0.95rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(255, 255, 255, 0.04);
        text-shadow: none;
        box-shadow: none;
    }

    .rr-mobile-footer__btn i {
        font-size: 1em;
    }

    .rr-mobile-footer__btn--rules {
        color: #ffe3a7;
        border-color: rgba(255, 181, 53, 0.24);
        background: linear-gradient(180deg, rgba(255, 181, 53, 0.18), rgba(255, 181, 53, 0.08));
    }

    .rr-mobile-footer__btn--support {
        color: #a8f0c9;
        border-color: rgba(15, 146, 89, 0.26);
        background: linear-gradient(180deg, rgba(15, 146, 89, 0.18), rgba(15, 146, 89, 0.08));
    }

    @media (max-width: 767px) {
        .rr-mobile-footer {
            padding-inline: 0;
        }
    }
</style>

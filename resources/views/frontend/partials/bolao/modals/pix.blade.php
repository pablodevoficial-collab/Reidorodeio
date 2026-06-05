    <div class="rr-modal" id="rrPixModal" aria-hidden="true" style="z-index: 10000;">
        <div class="rr-modal__dialog" style="max-width: 420px; padding: 20px; text-align: center;">
            <p class="rr-meta" id="rrPixModalMeta" style="display:none;">Escaneie o QR Code para concluir sua entrada.</p>
            
            <div id="rrPixModalContent" style="display: flex; flex-direction: column; gap: 14px; align-items: center; margin-bottom: 18px; text-align: center;">
                <!-- QR code and text injected here via JS -->
            </div>

            <textarea id="rrPixRawCode" readonly style="position:absolute; left:-9999px; top:-9999px; opacity:0; pointer-events:none;"></textarea>

            <div id="rrPixModalActions" style="display: flex; gap: 12px;">
                <button class="rr-btn rr-btn--primary" id="rrBtnCopyPix" type="button" style="flex: 1; justify-content: center; font-size: 0.92rem; min-height: 48px; border-radius: 12px; gap: 6px;">
                    <i class="fas fa-copy"></i> Copiar PIX Copia e Cola
                </button>
                <button class="rr-btn" id="rrBtnVerifyPix" type="button" style="flex: 1; justify-content: center; font-size: 0.92rem; min-height: 48px; border-radius: 12px; background: rgba(248, 113, 113, 0.12); color: #fda4af; border: 1px solid rgba(248, 113, 113, 0.45); gap: 6px;">
                    <i class="fas fa-trash"></i> Excluir equipe
                </button>
            </div>
        </div>
    </div>


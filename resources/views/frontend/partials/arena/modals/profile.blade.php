<div class="rr-modal" hidden data-profile-modal>
    <div class="rr-modal__backdrop" data-close-profile></div>
    <div class="rr-modal__dialog arena-sheet">
        <button class="rr-modal__close" type="button" data-close-profile>&times;</button>

        <div class="arena-profile-head">
            <span data-profile-greeting>Ola</span>
            <h2 data-profile-title>Receber premio</h2>
            <p data-profile-subtitle>Complete seus dados para receber premiacoes.</p>
        </div>

        <div class="arena-profile-summary" hidden data-profile-summary>
            <span>Total ganho no site</span>
            <strong data-profile-total-won>R$ 0,00</strong>
            <small>Premios marcados como pagos pela equipe.</small>
        </div>

        <form class="arena-form" data-profile-form-sheet enctype="multipart/form-data">
            <label class="arena-profile-photo">
                <span class="arena-profile-photo__preview" data-profile-photo-preview>
                    <span>Foto</span>
                </span>
                <span class="arena-profile-photo__text">
                    <strong>Foto de perfil</strong>
                    <small>Vai para aprovacao da equipe antes de aparecer.</small>
                </span>
                <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp">
            </label>

            <div class="arena-profile-complete-fields" data-profile-complete-fields>
                <input type="text" name="cpf" placeholder="CPF" inputmode="numeric" required>
                <input type="text" name="fullname" placeholder="Nome completo" required>
            </div>

            <input type="text" name="pix_key" placeholder="Chave Pix para premio" required>
            <button class="arena-button arena-button--solid" type="submit" data-profile-submit>Salvar dados do premio</button>
            <div class="rr-form-step__feedback" data-profile-sheet-feedback></div>
        </form>
    </div>
</div>

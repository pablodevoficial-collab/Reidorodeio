<div class="rr-modal" hidden data-profile-modal>
    <div class="rr-modal__backdrop" data-close-profile></div>
    <div class="rr-modal__dialog arena-sheet">
        <button class="rr-modal__close" type="button" data-close-profile>&times;</button>
        <h2>Receber prêmio</h2>
        <form class="arena-form" data-profile-form-sheet enctype="multipart/form-data">
            <label class="arena-profile-photo">
                <span class="arena-profile-photo__preview" data-profile-photo-preview>
                    <span>Foto</span>
                </span>
                <span class="arena-profile-photo__text">
                    <strong>Foto de perfil</strong>
                    <small>Vai para aprovação da equipe antes de aparecer.</small>
                </span>
                <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp">
            </label>
            <input type="text" name="cpf" placeholder="CPF" inputmode="numeric" required>
            <input type="text" name="fullname" placeholder="Nome completo" required>
            <input type="text" name="pix_key" placeholder="Chave Pix para prêmio" required>
            <button class="arena-button arena-button--solid" type="submit">Salvar dados do prêmio</button>
            <div class="rr-form-step__feedback" data-profile-sheet-feedback></div>
        </form>
    </div>
</div>

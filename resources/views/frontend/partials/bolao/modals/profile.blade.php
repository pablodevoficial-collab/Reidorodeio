<div class="rr-modal" id="rrProfileModal" aria-hidden="true">
    <style>
        #rrProfileForm { gap: 8px !important; }
        #rrProfileModal .rr-modal__dialog { padding: 14px; gap: 10px; max-height: 98vh; }
        .rr-compact-input { min-height: 46px !important; padding: 0 14px !important; font-size: 0.9rem !important; }
        .rr-compact-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        @media (max-width: 400px) {
            .rr-compact-row { grid-template-columns: 1fr; }
            .rr-compact-input { min-height: 42px !important; }
        }
    </style>
    <div class="rr-modal__dialog" style="max-width:500px;">
        <div class="rr-modal__head">
            <div><h3 class="rr-modal__title" style="font-size:1.2rem;">Complete seu perfil</h3><p class="rr-meta" style="font-size:0.75rem;">Para receber notificações e a premiação</p></div>
            <button class="rr-modal__close" type="button" data-close-modal="rrProfileModal" style="width:36px; height:36px;"><i class="fas fa-xmark"></i></button>
        </div>
        <form class="rr-auth__form is-active" id="rrProfileForm">
            <div class="rr-hidden" id="rrProfileFeedback"></div>
            
            <div style="display:flex; align-items:center; gap:10px; padding:10px; margin:0; border-radius:18px; background: linear-gradient(180deg, rgba(15,23,42,.94), rgba(3,7,22,.96)); border:1px solid rgba(255,255,255,.08); box-shadow: inset 0 1px 0 rgba(255,255,255,.04);">
                <div id="rrLiveAvatar" data-status="empty" style="width:54px; height:54px; flex:none; display:grid; place-items:center; overflow:hidden; border-radius:50%; padding:2px; background: linear-gradient(135deg, rgba(249,115,22,.24), rgba(37,99,235,.2)); border:1px solid rgba(255,255,255,.1); box-shadow:0 8px 20px rgba(2,6,23,.42), 0 0 0 3px rgba(249,115,22,.08);"></div>
                <div style="min-width:0; display:grid; gap:2px;">
                    <strong id="rrProfileAvatarName" style="color:#fff7ed; font-size:0.95rem; font-weight:900; line-height:1.2;">Seu perfil</strong>
                    <span id="rrProfileAvatarStatus" data-status="empty" style="color:#94a3b8; font-size:.78rem; line-height:1.2;">Se houver foto, aparecerá aqui.</span>
                </div>
            </div>

            <label style="border:2px solid #4ade80; padding:8px; border-radius:14px; text-align:center; cursor:pointer; color: #4ade80; font-weight: bold; background: rgba(74, 222, 128, 0.1); font-size:0.85rem;" class="rr-meta">
                <i class="fas fa-image"></i> Alterar foto de perfil
                <input type="file" name="avatar" accept="image/*" style="display:none;">
            </label>
            <p class="rr-note" style="margin-top:-4px; font-size:0.7rem; line-height:1.2;">A foto vai para análise e você recebe um email com o resultado.</p>
            
            <div class="rr-field" style="gap:4px;">
                <label for="rrProfileUsername" style="font-size:0.8rem; margin:0;">Como você quer ser chamado</label>
                <input type="text" id="rrProfileUsername" name="username" class="rr-input rr-compact-input" value="{{ $currentUser->username ?? '' }}" maxlength="40" minlength="3" autocomplete="nickname" placeholder="Ex: joao_silva" required>
            </div>

            <div class="rr-compact-row">
                <input type="text" name="email" class="rr-search rr-compact-input" value="{{ $currentUser->email ?? '' }}" placeholder="E-mail (ex: seu@email.com)" required>
                <input type="text" name="whatsapp" class="rr-search rr-compact-input" value="{{ $currentUser->mobile ?? '' }}" placeholder="WhatsApp (ex: 679999)" required>
            </div>
            
            <div class="rr-compact-row">
                <input type="text" inputmode="numeric" name="birth_date" id="rrProfileBirthDate" class="rr-search rr-compact-input" value="{{ !empty($currentUser->birthdate) ? \Carbon\Carbon::parse($currentUser->birthdate)->format('d/m/Y') : '' }}" placeholder="Nascimento (DD/MM/AAAA)" required>
                <input type="text" name="pix_key" class="rr-search rr-compact-input" value="{{ $currentUser->pix_key ?? '' }}" placeholder="Chave PIX (Para prêmios)" required>
            </div>
            
            <button class="rr-choice__btn is-active" type="submit" style="width:100%; margin-top:4px; min-height:46px; font-size:0.95rem;">Salvar Perfil</button>
        </form>
    </div>
</div>

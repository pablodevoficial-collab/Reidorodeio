@extends('admin.layouts.app')
@section('panel')
@php
    $claimedUser = $competitor->claimedUser;
    $defaultNameParts = preg_split('/\s+/', trim((string) $competitor->nome), 2);
    $defaultFirstname = $defaultNameParts[0] ?? '';
    $defaultLastname = $defaultNameParts[1] ?? '';
    $claimChecked = old('profile_claimed', $competitor->profile_claimed);
@endphp
<div class="rr-admin-dark">
@include('admin.partials.rr-admin-dark')

<div class="rr-comp-form">
    <div class="rr-comp-form__header rr-comp-form__header--edit">
        <div class="rr-comp-form__header-icon">
            <i class="las la-user-edit"></i>
        </div>
        <div class="rr-comp-form__header-text">
            <h3>Editar Competidor</h3>
            <p>Atualize os dados de {{ $competitor->nome }}</p>
        </div>
    </div>
    
    <form action="{{ route('admin.competitors.update', $competitor->id) }}" method="POST" enctype="multipart/form-data" class="rr-comp-form__body">
        @csrf
        @method('PUT')
        
        <div class="rr-comp-form__grid">
            {{-- Coluna da foto --}}
            <div class="rr-comp-form__photo-section">
                <div class="rr-comp-form__photo-upload {{ $competitor->foto ? 'has-photo' : '' }}" id="photoUploadArea">
                    <input type="file" name="foto" id="fotoInput" class="rr-comp-form__photo-input" accept="image/*">
                    <div class="rr-comp-form__photo-preview" id="photoPreview" style="{{ $competitor->foto ? 'display: none;' : '' }}">
                        <i class="las la-camera"></i>
                        <span>Clique para adicionar foto</span>
                    </div>
                    @if($competitor->foto)
                        <img src="{{ $competitor->foto_url }}" alt="{{ $competitor->nome }}" class="rr-comp-form__photo-img" id="photoImg">
                        <button type="button" class="rr-comp-form__photo-remove" id="removePhotoBtn" title="Remover foto">
                            <i class="las la-times"></i>
                        </button>
                    @else
                        <img src="" alt="Preview" class="rr-comp-form__photo-img" id="photoImg" style="display: none;">
                    @endif
                </div>
                <input type="hidden" name="delete_foto" id="delete_foto" value="0">
                <small class="rr-comp-form__photo-hint">JPG, PNG ou WEBP. Máx 5MB</small>
            </div>
            
            {{-- Campos do formulário --}}
            <div class="rr-comp-form__fields">
                <div class="rr-comp-form__row">
                    <div class="rr-comp-form__field rr-comp-form__field--full">
                        <label class="rr-comp-form__label">
                            <i class="las la-user"></i> Nome do Competidor <span class="required">*</span>
                        </label>
                        <input type="text" name="nome" class="rr-comp-form__input" value="{{ old('nome', $competitor->nome) }}" placeholder="Ex: João Silva" required>
                    </div>
                </div>
                
                <div class="rr-comp-form__row">
                    <div class="rr-comp-form__field">
                        <label class="rr-comp-form__label">
                            <i class="las la-layer-group"></i> Nível <span class="required">*</span>
                        </label>
                        <div class="rr-comp-form__select-wrapper">
                            <select name="nivel" class="rr-comp-form__select" required>
                                <option value="">Selecione o nível</option>
                                <option value="favorito" {{ old('nivel', $competitor->nivel) == 'favorito' ? 'selected' : '' }}>⭐ Favorito</option>
                                <option value="elite" {{ old('nivel', $competitor->nivel) == 'elite' ? 'selected' : '' }}>🏆 Elite</option>
                                <option value="ascendente" {{ in_array(old('nivel', $competitor->nivel), ['legado', 'ascendente']) ? 'selected' : '' }}>📈 Ascendente</option>
                                <option value="competidor" {{ in_array(old('nivel', $competitor->nivel), ['presilha', 'competidor']) ? 'selected' : '' }}>👤 Competidor</option>
                            </select>
                            <i class="las la-chevron-down rr-comp-form__select-icon"></i>
                        </div>
                    </div>
                    
                    <div class="rr-comp-form__field">
                        <label class="rr-comp-form__label">
                            <i class="las la-toggle-on"></i> Status <span class="required">*</span>
                        </label>
                        <div class="rr-comp-form__toggle-group">
                            <label class="rr-comp-form__toggle">
                                <input type="radio" name="status" value="ativo" {{ old('status', $competitor->status) == 'ativo' ? 'checked' : '' }}>
                                <span class="rr-comp-form__toggle-btn rr-comp-form__toggle-btn--active">
                                    <i class="las la-check-circle"></i> Ativo
                                </span>
                            </label>
                            <label class="rr-comp-form__toggle">
                                <input type="radio" name="status" value="inativo" {{ old('status', $competitor->status) == 'inativo' ? 'checked' : '' }}>
                                <span class="rr-comp-form__toggle-btn rr-comp-form__toggle-btn--inactive">
                                    <i class="las la-pause-circle"></i> Inativo
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="rr-comp-form__row">
                    <div class="rr-comp-form__field rr-comp-form__field--full">
                        <label class="rr-comp-form__label">
                            <i class="las la-align-left"></i> Biografia
                        </label>
                        <textarea name="biografia" class="rr-comp-form__textarea" rows="4" placeholder="Conte um pouco sobre a história e conquistas do competidor...">{{ old('biografia', $competitor->biografia) }}</textarea>
                        <small class="rr-comp-form__hint">Máximo de 1000 caracteres</small>
                    </div>
                </div>

                <div class="rr-comp-form__row">
                    <div class="rr-comp-form__field rr-comp-form__field--full">
                        <label class="rr-comp-form__claim-toggle">
                            <input type="checkbox" name="profile_claimed" id="profileClaimedToggle" value="1" {{ $claimChecked ? 'checked' : '' }}>
                            <span class="rr-comp-form__claim-label">
                                <i class="las la-check-double"></i> Perfil Reivindicado
                            </span>
                        </label>
                        <small class="rr-comp-form__hint" style="display: block; margin-top: 4px;">Marque se o competidor já reivindicou este perfil. Remove o botão "Reivindicar" do modal público.</small>
                    </div>
                </div>

                <div class="rr-comp-form__claim-card" id="claimedUserCard" style="{{ $claimChecked ? '' : 'display: none;' }}">
                    <div class="rr-comp-form__claim-card-header">
                        <div>
                            <h4>Conta vinculada ao competidor</h4>
                            <p>Esse usuário poderá entrar no site normalmente e operar como o perfil reivindicado.</p>
                        </div>
                        @if ($claimedUser)
                            <span class="rr-comp-form__claim-badge">Usuário #{{ $claimedUser->id }}</span>
                        @endif
                    </div>

                    <div class="rr-comp-form__row">
                        <div class="rr-comp-form__field">
                            <label class="rr-comp-form__label">
                                <i class="las la-user"></i> Nome
                            </label>
                            <input type="text" name="claimed_user_firstname" class="rr-comp-form__input rr-claimed-user-input" value="{{ old('claimed_user_firstname', $claimedUser->firstname ?? $defaultFirstname) }}" placeholder="Nome do usuário">
                        </div>

                        <div class="rr-comp-form__field">
                            <label class="rr-comp-form__label">
                                <i class="las la-user-tag"></i> Sobrenome
                            </label>
                            <input type="text" name="claimed_user_lastname" class="rr-comp-form__input rr-claimed-user-input" value="{{ old('claimed_user_lastname', $claimedUser->lastname ?? $defaultLastname) }}" placeholder="Sobrenome do usuário">
                        </div>
                    </div>

                    <div class="rr-comp-form__row">
                        <div class="rr-comp-form__field">
                            <label class="rr-comp-form__label">
                                <i class="las la-at"></i> Username
                            </label>
                            <input type="text" name="claimed_user_username" class="rr-comp-form__input rr-claimed-user-input" value="{{ old('claimed_user_username', $claimedUser->username) }}" placeholder="username">
                        </div>

                        <div class="rr-comp-form__field">
                            <label class="rr-comp-form__label">
                                <i class="las la-envelope"></i> Email
                            </label>
                            <input type="email" name="claimed_user_email" class="rr-comp-form__input rr-claimed-user-input" value="{{ old('claimed_user_email', $claimedUser->email) }}" placeholder="email@dominio.com">
                        </div>
                    </div>

                    <div class="rr-comp-form__row">
                        <div class="rr-comp-form__field">
                            <label class="rr-comp-form__label">
                                <i class="las la-phone"></i> Celular
                            </label>
                            <input type="text" name="claimed_user_mobile" class="rr-comp-form__input" value="{{ old('claimed_user_mobile', $claimedUser->mobile) }}" placeholder="(00) 00000-0000">
                        </div>

                        <div class="rr-comp-form__field">
                            <label class="rr-comp-form__label">
                                <i class="las la-id-card"></i> CPF
                            </label>
                            <input type="text" name="claimed_user_cpf" class="rr-comp-form__input rr-claimed-user-input" value="{{ old('claimed_user_cpf', $claimedUser->cpf) }}" placeholder="00000000000">
                        </div>
                    </div>

                    <div class="rr-comp-form__row">
                        <div class="rr-comp-form__field">
                            <label class="rr-comp-form__label">
                                <i class="las la-calendar"></i> Data de nascimento
                            </label>
                            <input type="date" name="claimed_user_birthdate" class="rr-comp-form__input rr-claimed-user-input" value="{{ old('claimed_user_birthdate', optional($claimedUser?->birthdate)->format('Y-m-d')) }}">
                        </div>

                        <div class="rr-comp-form__field">
                            <label class="rr-comp-form__label">
                                <i class="las la-toggle-on"></i> Status do usuário
                            </label>
                            <div class="rr-comp-form__select-wrapper">
                                <select name="claimed_user_status" class="rr-comp-form__select rr-claimed-user-input">
                                    <option value="1" @selected((int) old('claimed_user_status', $claimedUser->status ?? 1) === 1)>Ativo</option>
                                    <option value="0" @selected((int) old('claimed_user_status', $claimedUser->status ?? 1) === 0)>Banido</option>
                                </select>
                                <i class="las la-chevron-down rr-comp-form__select-icon"></i>
                            </div>
                        </div>
                    </div>

                    <div class="rr-comp-form__row">
                        <div class="rr-comp-form__field">
                            <label class="rr-comp-form__label">
                                <i class="las la-lock"></i> Senha {{ $claimedUser ? '(opcional)' : '' }}
                            </label>
                            <input type="password" name="claimed_user_password" class="rr-comp-form__input {{ $claimedUser ? '' : 'rr-claimed-user-input' }}" placeholder="{{ $claimedUser ? 'Preencha apenas para trocar' : 'Senha de acesso' }}">
                        </div>

                        <div class="rr-comp-form__field">
                            <label class="rr-comp-form__label">
                                <i class="las la-lock"></i> Confirmar senha
                            </label>
                            <input type="password" name="claimed_user_password_confirmation" class="rr-comp-form__input {{ $claimedUser ? '' : 'rr-claimed-user-input' }}" placeholder="Repita a senha">
                        </div>
                    </div>

                    <div class="rr-comp-form__claim-checks">
                        <label><input type="checkbox" name="claimed_user_ev" value="1" @checked(old('claimed_user_ev', (int) ($claimedUser->ev ?? 1)) == 1)> Email verificado</label>
                        <label><input type="checkbox" name="claimed_user_sv" value="1" @checked(old('claimed_user_sv', (int) ($claimedUser->sv ?? 1)) == 1)> Mobile verificado</label>
                        <label><input type="checkbox" name="claimed_user_show_in_listings" value="1" @checked(old('claimed_user_show_in_listings', $claimedUser->show_in_listings ?? true))> Exibir em listagens</label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="rr-comp-form__footer">
            <a href="{{ route('admin.competitors.index') }}" class="rr-comp-btn rr-comp-btn--secondary">
                <i class="las la-times"></i> Cancelar
            </a>
            <button type="submit" class="rr-comp-btn rr-comp-btn--primary">
                <i class="las la-save"></i> Salvar Alterações
            </button>
        </div>
    </form>
</div>

</div>
@endsection

@push('style')
<style>
/* ========================================
   COMPETITOR FORM - MODERN LAYOUT
   ======================================== */

:root {
    --comp-primary: #4f46e5;
    --comp-primary-light: #818cf8;
    --comp-success: #10b981;
    --comp-warning: #f59e0b;
    --comp-danger: #ef4444;
    --comp-gray-50: #f9fafb;
    --comp-gray-100: #f3f4f6;
    --comp-gray-200: #e5e7eb;
    --comp-gray-300: #d1d5db;
    --comp-gray-400: #9ca3af;
    --comp-gray-500: #6b7280;
    --comp-gray-600: #4b5563;
    --comp-gray-700: #374151;
    --comp-gray-800: #1f2937;
    --comp-gray-900: #111827;
    --comp-card-bg: #ffffff;
    --comp-card-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
    --comp-radius: 16px;
    --comp-radius-sm: 12px;
    --comp-radius-xs: 8px;
}

.rr-comp-form {
    max-width: 800px;
    margin: 0 auto;
    background: var(--comp-card-bg);
    border-radius: var(--comp-radius);
    box-shadow: var(--comp-card-shadow);
    overflow: hidden;
}

/* Header */
.rr-comp-form__header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 24px 32px;
    background: linear-gradient(135deg, var(--comp-primary), var(--comp-primary-light));
    color: white;
}

.rr-comp-form__header--edit {
    background: linear-gradient(135deg, #059669, #10b981);
}

.rr-comp-form__header-icon {
    width: 56px;
    height: 56px;
    background: rgba(255,255,255,0.2);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
}

.rr-comp-form__header-text h3 {
    margin: 0 0 4px;
    font-size: 1.25rem;
    font-weight: 600;
}

.rr-comp-form__header-text p {
    margin: 0;
    font-size: 0.875rem;
    opacity: 0.85;
}

/* Body */
.rr-comp-form__body {
    padding: 32px;
}

.rr-comp-form__grid {
    display: grid;
    grid-template-columns: 180px 1fr;
    gap: 32px;
    align-items: start;
}

/* Photo Upload */
.rr-comp-form__photo-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.rr-comp-form__photo-upload {
    position: relative;
    width: 160px;
    height: 160px;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    border: 3px dashed var(--comp-gray-300);
    transition: all 0.3s;
}

.rr-comp-form__photo-upload.has-photo {
    border: 3px solid var(--comp-gray-200);
}

.rr-comp-form__photo-upload:hover {
    border-color: var(--comp-primary);
    background: rgba(79, 70, 229, 0.05);
}

.rr-comp-form__photo-input {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
}

.rr-comp-form__photo-preview {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: var(--comp-gray-400);
    text-align: center;
    padding: 16px;
}

.rr-comp-form__photo-preview i {
    font-size: 2.5rem;
}

.rr-comp-form__photo-preview span {
    font-size: 0.75rem;
}

.rr-comp-form__photo-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.rr-comp-form__photo-remove {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--comp-danger);
    color: white;
    border: 2px solid white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 3;
    font-size: 1rem;
    transition: all 0.2s;
    opacity: 0;
}

.rr-comp-form__photo-upload:hover .rr-comp-form__photo-remove {
    opacity: 1;
}

.rr-comp-form__photo-remove:hover {
    transform: scale(1.1);
    background: #dc2626;
}

.rr-comp-form__photo-hint {
    color: var(--comp-gray-400);
    font-size: 0.75rem;
    text-align: center;
}

/* Fields */
.rr-comp-form__fields {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.rr-comp-form__row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.rr-comp-form__field--full {
    grid-column: 1 / -1;
}

.rr-comp-form__label {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--comp-gray-700);
}

.rr-comp-form__label i {
    color: var(--comp-gray-400);
}

.rr-comp-form__label .required {
    color: var(--comp-danger);
}

.rr-comp-form__input,
.rr-comp-form__select,
.rr-comp-form__textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--comp-gray-200);
    border-radius: var(--comp-radius-xs);
    font-size: 0.9rem;
    color: var(--comp-gray-800);
    background: var(--comp-card-bg);
    transition: all 0.2s;
}

.rr-comp-form__input:focus,
.rr-comp-form__select:focus,
.rr-comp-form__textarea:focus {
    outline: none;
    border-color: var(--comp-primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.rr-comp-form__input::placeholder,
.rr-comp-form__textarea::placeholder {
    color: var(--comp-gray-400);
}

.rr-comp-form__select-wrapper {
    position: relative;
}

.rr-comp-form__select {
    appearance: none;
    padding-right: 40px;
}

.rr-comp-form__select-icon {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--comp-gray-400);
    pointer-events: none;
}

.rr-comp-form__textarea {
    resize: vertical;
    min-height: 100px;
}

.rr-comp-form__hint {
    margin-top: 4px;
    font-size: 0.75rem;
    color: var(--comp-gray-400);
}

/* Toggle Group */
.rr-comp-form__toggle-group {
    display: flex;
    gap: 10px;
}

.rr-comp-form__toggle {
    flex: 1;
    cursor: pointer;
}

.rr-comp-form__toggle input {
    display: none;
}

.rr-comp-form__toggle-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 12px 16px;
    border-radius: var(--comp-radius-xs);
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.2s;
    border: 2px solid var(--comp-gray-200);
    background: var(--comp-gray-50);
    color: var(--comp-gray-500);
}

.rr-comp-form__toggle input:checked + .rr-comp-form__toggle-btn--active {
    border-color: var(--comp-success);
    background: rgba(16, 185, 129, 0.1);
    color: var(--comp-success);
}

.rr-comp-form__toggle input:checked + .rr-comp-form__toggle-btn--inactive {
    border-color: var(--comp-warning);
    background: rgba(245, 158, 11, 0.1);
    color: var(--comp-warning);
}

.rr-comp-form__claim-toggle {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.rr-comp-form__claim-toggle input {
    width: 18px;
    height: 18px;
    accent-color: var(--comp-success);
}

.rr-comp-form__claim-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--comp-gray-700);
}

.rr-comp-form__claim-label i {
    color: var(--comp-success);
}

.rr-comp-form__claim-card {
    border: 1px solid rgba(16, 185, 129, 0.25);
    border-radius: var(--comp-radius-sm);
    padding: 20px;
    background: linear-gradient(180deg, rgba(16, 185, 129, 0.08), rgba(16, 185, 129, 0.02));
}

.rr-comp-form__claim-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 18px;
}

.rr-comp-form__claim-card-header h4 {
    margin: 0 0 4px;
    font-size: 1rem;
    color: var(--comp-gray-800);
}

.rr-comp-form__claim-card-header p {
    margin: 0;
    font-size: 0.82rem;
    color: var(--comp-gray-500);
}

.rr-comp-form__claim-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(16, 185, 129, 0.14);
    color: var(--comp-success);
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.rr-comp-form__claim-checks {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    margin-top: 4px;
    font-size: 0.86rem;
    color: var(--comp-gray-700);
}

.rr-comp-form__claim-checks label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

/* Footer */
.rr-comp-form__footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 24px;
    margin-top: 24px;
    border-top: 1px solid var(--comp-gray-100);
}

/* Buttons */
.rr-comp-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: var(--comp-radius-xs);
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
    border: none;
}

.rr-comp-btn--primary {
    background: var(--comp-primary);
    color: white;
}

.rr-comp-btn--primary:hover {
    background: #4338ca;
    color: white;
    transform: translateY(-1px);
}

.rr-comp-btn--secondary {
    background: var(--comp-gray-100);
    color: var(--comp-gray-700);
}

.rr-comp-btn--secondary:hover {
    background: var(--comp-gray-200);
    color: var(--comp-gray-800);
}

/* Responsive */
@media (max-width: 640px) {
    .rr-comp-form__grid {
        grid-template-columns: 1fr;
    }
    
    .rr-comp-form__photo-section {
        order: -1;
    }
    
    .rr-comp-form__row {
        grid-template-columns: 1fr;
    }
    
    .rr-comp-form__body {
        padding: 24px 20px;
    }
    
    .rr-comp-form__footer {
        flex-direction: column-reverse;
    }
    
    .rr-comp-btn {
        width: 100%;
    }
}

/* Dark mode */
.rr-admin-dark .rr-comp-form {
    background: var(--comp-gray-800);
}

.rr-admin-dark .rr-comp-form__label {
    color: var(--comp-gray-300);
}

.rr-admin-dark .rr-comp-form__claim-label,
.rr-admin-dark .rr-comp-form__claim-checks {
    color: var(--comp-gray-200);
}

.rr-admin-dark .rr-comp-form__claim-card {
    background: rgba(16, 185, 129, 0.08);
    border-color: rgba(16, 185, 129, 0.28);
}

.rr-admin-dark .rr-comp-form__claim-card-header h4 {
    color: var(--comp-gray-100);
}

.rr-admin-dark .rr-comp-form__claim-card-header p {
    color: var(--comp-gray-400);
}

.rr-admin-dark .rr-comp-form__input,
.rr-admin-dark .rr-comp-form__select,
.rr-admin-dark .rr-comp-form__textarea {
    background: var(--comp-gray-700);
    border-color: var(--comp-gray-600);
    color: var(--comp-gray-100);
}

.rr-admin-dark .rr-comp-form__toggle-btn {
    background: var(--comp-gray-700);
    border-color: var(--comp-gray-600);
}

.rr-admin-dark .rr-comp-form__photo-upload {
    border-color: var(--comp-gray-600);
}

.rr-admin-dark .rr-comp-form__footer {
    border-color: var(--comp-gray-700);
}

.rr-admin-dark .rr-comp-btn--secondary {
    background: var(--comp-gray-700);
    color: var(--comp-gray-200);
}
</style>
@endpush

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('fotoInput');
    const preview = document.getElementById('photoPreview');
    const img = document.getElementById('photoImg');
    const removeBtn = document.getElementById('removePhotoBtn');
    const deleteInput = document.getElementById('delete_foto');
    const uploadArea = document.getElementById('photoUploadArea');
    
    // Preview nova foto
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                img.style.display = 'block';
                preview.style.display = 'none';
                uploadArea.classList.add('has-photo');
                deleteInput.value = '0';
                
                // Criar botão de remover se não existir
                if (!removeBtn) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'rr-comp-form__photo-remove';
                    btn.id = 'removePhotoBtn';
                    btn.title = 'Remover foto';
                    btn.innerHTML = '<i class="las la-times"></i>';
                    uploadArea.appendChild(btn);
                    setupRemoveBtn(btn);
                }
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Remover foto
    function setupRemoveBtn(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (confirm('Tem certeza que deseja remover a foto?')) {
                img.style.display = 'none';
                img.src = '';
                preview.style.display = 'flex';
                uploadArea.classList.remove('has-photo');
                deleteInput.value = '1';
                input.value = '';
                this.remove();
            }
        });
    }
    
    if (removeBtn) {
        setupRemoveBtn(removeBtn);
    }

    const profileClaimedToggle = document.getElementById('profileClaimedToggle');
    const claimedUserCard = document.getElementById('claimedUserCard');
    const claimedUserInputs = Array.from(document.querySelectorAll('.rr-claimed-user-input'));

    function syncClaimedUserState() {
        const enabled = !!profileClaimedToggle?.checked;

        if (claimedUserCard) {
            claimedUserCard.style.display = enabled ? '' : 'none';
        }

        claimedUserInputs.forEach((field) => {
            field.required = enabled && !field.name.includes('password');
        });
    }

    if (profileClaimedToggle) {
        profileClaimedToggle.addEventListener('change', syncClaimedUserState);
        syncClaimedUserState();
    }
});
</script>
@endpush

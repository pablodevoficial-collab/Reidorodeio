@extends('admin.layouts.app')
@section('panel')
<div class="rr-admin-dark">
@include('admin.partials.rr-admin-dark')

<div class="rr-comp-form">
    <div class="rr-comp-form__header">
        <div class="rr-comp-form__header-icon">
            <i class="las la-user-plus"></i>
        </div>
        <div class="rr-comp-form__header-text">
            <h3>Novo Competidor</h3>
            <p>Preencha os dados para cadastrar um novo competidor</p>
        </div>
    </div>
    
    <form action="{{ route('admin.competitors.store') }}" method="POST" enctype="multipart/form-data" class="rr-comp-form__body">
        @csrf
        
        <div class="rr-comp-form__grid">
            {{-- Coluna da foto --}}
            <div class="rr-comp-form__photo-section">
                <div class="rr-comp-form__photo-upload" id="photoUploadArea">
                    <input type="file" name="foto" id="fotoInput" class="rr-comp-form__photo-input" accept="image/*">
                    <div class="rr-comp-form__photo-preview" id="photoPreview">
                        <i class="las la-camera"></i>
                        <span>Clique para adicionar foto</span>
                    </div>
                    <img src="" alt="Preview" class="rr-comp-form__photo-img" id="photoImg" style="display: none;">
                </div>
                <small class="rr-comp-form__photo-hint">JPG, PNG ou WEBP. Máx 5MB</small>
            </div>
            
            {{-- Campos do formulário --}}
            <div class="rr-comp-form__fields">
                <div class="rr-comp-form__row">
                    <div class="rr-comp-form__field rr-comp-form__field--full">
                        <label class="rr-comp-form__label">
                            <i class="las la-user"></i> Nome do Competidor <span class="required">*</span>
                        </label>
                        <input type="text" name="nome" class="rr-comp-form__input" value="{{ old('nome') }}" placeholder="Ex: João Silva" required>
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
                                <option value="favorito" {{ old('nivel') == 'favorito' ? 'selected' : '' }}>⭐ Favorito</option>
                                <option value="elite" {{ old('nivel') == 'elite' ? 'selected' : '' }}>🏆 Elite</option>
                                <option value="ascendente" {{ old('nivel') == 'ascendente' ? 'selected' : '' }}>📈 Ascendente</option>
                                <option value="competidor" {{ old('nivel', 'competidor') == 'competidor' ? 'selected' : '' }}>👤 Competidor</option>
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
                                <input type="radio" name="status" value="ativo" {{ old('status', 'ativo') == 'ativo' ? 'checked' : '' }}>
                                <span class="rr-comp-form__toggle-btn rr-comp-form__toggle-btn--active">
                                    <i class="las la-check-circle"></i> Ativo
                                </span>
                            </label>
                            <label class="rr-comp-form__toggle">
                                <input type="radio" name="status" value="inativo" {{ old('status') == 'inativo' ? 'checked' : '' }}>
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
                        <textarea name="biografia" class="rr-comp-form__textarea" rows="4" placeholder="Conte um pouco sobre a história e conquistas do competidor...">{{ old('biografia') }}</textarea>
                        <small class="rr-comp-form__hint">Máximo de 1000 caracteres</small>
                    </div>
                </div>

                <div class="rr-comp-form__row">
                    <div class="rr-comp-form__field rr-comp-form__field--full">
                        <label class="rr-comp-form__claim-toggle">
                            <input type="checkbox" name="profile_claimed" value="1" {{ old('profile_claimed') ? 'checked' : '' }}>
                            <span class="rr-comp-form__claim-label">
                                <i class="las la-check-double"></i> Perfil Reivindicado
                            </span>
                        </label>
                        <small class="rr-comp-form__hint" style="display: block; margin-top: 4px;">Marque se o competidor já reivindicou este perfil. Remove o botão "Reivindicar" do modal público.</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="rr-comp-form__footer">
            <a href="{{ route('admin.competitors.index') }}" class="rr-comp-btn rr-comp-btn--secondary">
                <i class="las la-times"></i> Cancelar
            </a>
            <button type="submit" class="rr-comp-btn rr-comp-btn--primary">
                <i class="las la-save"></i> Salvar Competidor
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

.rr-admin-dark .rr-comp-form__claim-label {
    color: var(--comp-gray-200);
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
    
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                img.style.display = 'block';
                preview.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endpush

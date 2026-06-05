@php
    $isEdit = isset($sponsor);
    $currentLogo = $isEdit && $sponsor->logo ? asset('storage/' . $sponsor->logo) : null;
@endphp

<style>
    .sponsor-form-page {
        max-width: 980px;
        margin: 0 auto;
    }

    .sponsor-form-card {
        overflow: hidden;
        border: 1px solid rgba(249, 115, 22, 0.2);
        border-radius: 16px;
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }

    .sponsor-form-header {
        padding: 1.8rem 2rem;
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    }

    .sponsor-form-header h5 {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0;
        color: #fff;
        font-size: 1.65rem;
        font-weight: 900;
    }

    .sponsor-form-body {
        display: grid;
        gap: 1.4rem;
        padding: 2rem;
    }

    .sponsor-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .sponsor-field {
        display: grid;
        gap: 0.5rem;
    }

    .sponsor-field.full {
        grid-column: 1 / -1;
    }

    .sponsor-field label,
    .sponsor-toggle span {
        color: #e2e8f0;
        font-weight: 800;
    }

    .sponsor-field label i {
        margin-right: 0.35rem;
        color: #f97316;
    }

    .sponsor-input {
        width: 100%;
        min-height: 48px;
        padding: 0 1rem;
        border: 2px solid rgba(148, 163, 184, 0.2);
        border-radius: 8px;
        background: rgba(15, 23, 42, 0.62);
        color: #e2e8f0;
        font-weight: 700;
    }

    .sponsor-input:focus {
        outline: none;
        border-color: #f97316;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
    }

    .sponsor-help {
        color: #94a3b8;
        font-size: 0.84rem;
        font-style: italic;
    }

    .sponsor-upload {
        display: grid;
        gap: 0.75rem;
        padding: 1rem;
        border: 2px dashed rgba(249, 115, 22, 0.35);
        border-radius: 12px;
        background: rgba(15, 23, 42, 0.42);
    }

    .sponsor-upload input {
        color: #cbd5e1;
    }

    .sponsor-preview {
        width: 160px;
        height: 110px;
        object-fit: contain;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        background: rgba(3, 7, 18, 0.58);
        padding: 10px;
    }

    .sponsor-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        min-height: 48px;
        padding: 0 1rem;
        border: 1px solid rgba(34, 197, 94, 0.18);
        border-radius: 8px;
        background: rgba(6, 95, 70, 0.16);
    }

    .sponsor-toggle input {
        width: 18px;
        height: 18px;
        accent-color: #22c55e;
    }

    .sponsor-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding-top: 0.5rem;
    }

    .sponsor-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        min-height: 46px;
        padding: 0 1.35rem;
        border: 0;
        border-radius: 8px;
        color: #fff;
        font-weight: 900;
        text-decoration: none;
    }

    .sponsor-btn.primary {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.28);
    }

    .sponsor-btn.secondary {
        background: rgba(71, 85, 105, 0.9);
        color: #e2e8f0;
    }

    .sponsor-errors {
        padding: 1rem;
        border: 1px solid rgba(248, 113, 113, 0.24);
        border-radius: 12px;
        background: rgba(127, 29, 29, 0.28);
        color: #fecaca;
        font-weight: 700;
    }

    @media (max-width: 760px) {
        .sponsor-grid {
            grid-template-columns: 1fr;
        }

        .sponsor-form-body,
        .sponsor-form-header {
            padding: 1.35rem;
        }
    }
</style>

<div class="sponsor-form-page">
    <div class="sponsor-form-card">
        <div class="sponsor-form-header">
            <h5><i class="las la-handshake"></i> {{ $pageTitle }}</h5>
        </div>

        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="sponsor-form-body">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            @if ($errors->any())
                <div class="sponsor-errors">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="sponsor-grid">
                <div class="sponsor-field">
                    <label for="sponsor-name"><i class="las la-signature"></i> Nome</label>
                    <input class="sponsor-input" id="sponsor-name" name="name" type="text" maxlength="120" value="{{ old('name', $sponsor->name ?? '') }}" placeholder="Ex: Pampsul" required>
                </div>

                <div class="sponsor-field">
                    <label for="sponsor-sort-order"><i class="las la-sort-numeric-down"></i> Ordem</label>
                    <input class="sponsor-input" id="sponsor-sort-order" name="sort_order" type="number" min="0" max="999999" value="{{ old('sort_order', $sponsor->sort_order ?? 0) }}">
                    <span class="sponsor-help">Menor numero aparece primeiro no carrossel.</span>
                </div>

                <div class="sponsor-field full">
                    <label for="sponsor-url"><i class="las la-link"></i> Link de acesso</label>
                    <input class="sponsor-input" id="sponsor-url" name="url" type="url" maxlength="500" value="{{ old('url', $sponsor->url ?? '') }}" placeholder="https://site-do-patrocinador.com.br" required>
                </div>

                <div class="sponsor-field full">
                    <label for="sponsor-logo"><i class="las la-image"></i> Logo</label>
                    <div class="sponsor-upload">
                        @if ($currentLogo)
                            <img class="sponsor-preview" src="{{ $currentLogo }}" alt="Logo atual de {{ $sponsor->name }}">
                            <span class="sponsor-help">Envie uma nova imagem somente se quiser trocar a logo atual.</span>
                        @else
                            <span class="sponsor-help">Use PNG, JPG ou WEBP, de preferencia com fundo transparente.</span>
                        @endif
                        <input id="sponsor-logo" name="logo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" {{ $isEdit ? '' : 'required' }}>
                    </div>
                </div>

                <div class="sponsor-field full">
                    <label class="sponsor-toggle">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $sponsor->is_active ?? true))>
                        <span>Exibir no carrossel do frontend</span>
                    </label>
                </div>
            </div>

            <div class="sponsor-actions">
                <button type="submit" class="sponsor-btn primary">
                    <i class="las la-save"></i>
                    {{ $isEdit ? 'Salvar patrocinador' : 'Criar patrocinador' }}
                </button>
                <a href="{{ route('admin.sponsors.index') }}" class="sponsor-btn secondary">
                    <i class="las la-arrow-left"></i>
                    Voltar
                </a>
            </div>
        </form>
    </div>
</div>

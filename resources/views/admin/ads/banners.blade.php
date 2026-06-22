@extends('admin.layouts.app')
@section('panel')
<div class="row gy-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">@lang('Cadastrar Banner')</h5>
            </div>
            <div class="card-body">
                <form id="bannerForm" method="POST" action="{{ route('admin.ads.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="banner_id" id="banner_id">
                    
                    <div class="form-group">
                        <label>@lang('Título') *</label>
                        <input type="text" name="titulo" id="titulo" class="form-control" placeholder="Ex: Promoção de abertura" required>
                    </div>
                    
                    <div class="form-group">
                        <label>@lang('Link (URL)') *</label>
                        <input type="url" name="link" id="link" class="form-control" placeholder="https://" required>
                    </div>
                    
                    <div class="form-group">
                        <label>@lang('Posição')</label>
                        <select name="posicao" id="posicao" class="form-control">
                            <option value="home_top">@lang('Home • Topo')</option>
                            <option value="home_middle">@lang('Home • Meio')</option>
                            <option value="home_bottom">@lang('Home • Rodapé')</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Início')</label>
                                <input type="datetime-local" name="inicio" id="inicio" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Fim')</label>
                                <input type="datetime-local" name="fim" id="fim" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>@lang('Status')</label>
                        <select name="status" id="status" class="form-control">
                            <option value="ativo">@lang('Ativo')</option>
                            <option value="inativo">@lang('Inativo')</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>@lang('Imagem Web (Desktop)') *</label>
                        <input type="file" name="imagem_web" id="imagem_web" class="form-control" accept="image/*">
                        <small class="text-muted">@lang('Recomendado: 1200x400px • Máximo 10MB (JPG/PNG/WebP)')</small>
                        <div id="preview_web" class="mt-2" style="display: none;">
                            <img id="preview_web_img" class="img-fluid" style="max-height: 100px; border-radius: 6px;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>@lang('Imagem Mobile') *</label>
                        <input type="file" name="imagem_mobile" id="imagem_mobile" class="form-control" accept="image/*">
                        <small class="text-muted">@lang('Recomendado: 600x300px • Máximo 10MB (JPG/PNG/WebP)')</small>
                        <div id="preview_mobile" class="mt-2" style="display: none;">
                            <img id="preview_mobile_img" class="img-fluid" style="max-height: 100px; border-radius: 6px;">
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <button type="button" id="submitBtn" class="btn btn--primary w-100 h-45">
                    <i class="las la-save"></i> <span id="submitText">@lang('Salvar Banner')</span>
                </button>
                <button type="button" id="cancelBtn" class="btn btn--secondary w-100 h-45 mt-2" style="display: none;">
                    <i class="las la-times"></i> @lang('Cancelar Edição')
                </button>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card b-radius--10">
            <div class="card-header">
                <h5 class="card-title mb-0">@lang('Banners cadastrados')</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive table-responsive--sm">
                    <table class="table--light style--two table rr-table-banners">
                        <thead>
                            <tr>
                                <th>@lang('Banner')</th>
                                <th>@lang('Imagens')</th>
                                <th>@lang('Posição')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Ação')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($banners as $banner)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img class="rr-banner-thumb" src="{{ $banner->image_web_url ?? $banner->image_url ?? getImage('public/assets/images/default.png') }}" alt="banner">
                                            <div>
                                                <div class="fw-bold">{{ $banner->title ?? '-' }}</div>
                                                <div class="small text-muted">{{ Str::limit($banner->link ?? '-', 40) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            @if($banner->image_web_url)
                                                <img class="rr-banner-preview" src="{{ $banner->image_web_url }}" alt="Web" title="Versão Web">
                                            @else
                                                <div class="rr-banner-preview-empty" title="Web não disponível">W</div>
                                            @endif
                                            @if($banner->image_mobile_url)
                                                <img class="rr-banner-preview" src="{{ $banner->image_mobile_url }}" alt="Mobile" title="Versão Mobile">
                                            @else
                                                <div class="rr-banner-preview-empty" title="Mobile não disponível">M</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $banner->position_label ?? '-' }}</td>
                                    <td>
                                        @if (($banner->status ?? 'inativo') === 'ativo')
                                            <span class="badge badge--success">@lang('Ativo')</span>
                                        @else
                                            <span class="badge badge--warning">@lang('Inativo')</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="button--group">
                                            <button type="button" class="btn btn-sm btn-outline--primary edit-banner" 
                                                data-id="{{ $banner->id }}"
                                                data-titulo="{{ $banner->title }}"
                                                data-link="{{ $banner->link }}"
                                                data-posicao="{{ $banner->position }}"
                                                data-inicio="{{ $banner->start_date?->format('Y-m-d\\TH:i') }}"
                                                data-fim="{{ $banner->end_date?->format('Y-m-d\\TH:i') }}"
                                                data-status="{{ $banner->status }}"
                                                data-image-web="{{ $banner->image_web_url }}"
                                                data-image-mobile="{{ $banner->image_mobile_url }}">
                                                <i class="las la-edit"></i>
                                            </button>
                                            <form action="{{ route('admin.ads.delete', $banner->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline--danger" onclick="return confirm('@lang('Tem certeza que deseja excluir este banner?')')">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted text-center">
                                        <div class="py-4">
                                            <img src="{{ getImage('assets/images/empty_list.png') }}" alt="empty" width="120" class="mb-2">
                                            <div>{{ __($emptyMessage) }}</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    .rr-banner-thumb {
        width: 64px;
        height: 40px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    
    .rr-banner-preview {
        width: 40px;
        height: 25px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        cursor: pointer;
    }
    
    .rr-banner-preview-empty {
        width: 40px;
        height: 25px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: #999;
        font-weight: bold;
    }
    
    .rr-table-banners tbody tr td { 
        vertical-align: middle; 
    }
    
    .form-group label {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('bannerForm');
    const submitBtn = document.getElementById('submitBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const submitText = document.getElementById('submitText');
    const cardTitle = document.querySelector('.card-title');
    
    // Preview de imagens
    const imageWebInput = document.getElementById('imagem_web');
    const imageMobileInput = document.getElementById('imagem_mobile');
    
    imageWebInput.addEventListener('change', function() {
        previewImage(this, 'preview_web', 'preview_web_img');
    });
    
    imageMobileInput.addEventListener('change', function() {
        previewImage(this, 'preview_mobile', 'preview_mobile_img');
    });
    
    function previewImage(input, containerId, imgId) {
        const container = document.getElementById(containerId);
        const img = document.getElementById(imgId);
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                container.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            container.style.display = 'none';
        }
    }
    
    // Editar banner
    document.querySelectorAll('.edit-banner').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = this.dataset;
            
            // Preencher formulário
            document.getElementById('banner_id').value = data.id;
            document.getElementById('titulo').value = data.titulo || '';
            document.getElementById('link').value = data.link || '';
            document.getElementById('posicao').value = data.posicao || '';
            document.getElementById('inicio').value = data.inicio || '';
            document.getElementById('fim').value = data.fim || '';
            document.getElementById('status').value = data.status || 'ativo';
            
            // Mostrar previews das imagens existentes
            if (data.imageWeb) {
                document.getElementById('preview_web_img').src = data.imageWeb;
                document.getElementById('preview_web').style.display = 'block';
            }
            if (data.imageMobile) {
                document.getElementById('preview_mobile_img').src = data.imageMobile;
                document.getElementById('preview_mobile').style.display = 'block';
            }
            
            // Alterar interface para modo edição
            form.action = `{{ route('admin.ads.update', ':id') }}`.replace(':id', data.id);
            submitText.textContent = '@lang("Atualizar Banner")';
            cardTitle.textContent = '@lang("Editar Banner")';
            cancelBtn.style.display = 'block';
            
            // Tornar uploads opcionais na edição
            imageWebInput.removeAttribute('required');
            imageMobileInput.removeAttribute('required');
            
            // Scroll para o formulário
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
    
    // Cancelar edição
    cancelBtn.addEventListener('click', function() {
        resetForm();
    });
    
    // Submit do formulário
    submitBtn.addEventListener('click', function() {
        form.submit();
    });
    
    function resetForm() {
        form.reset();
        form.action = '{{ route('admin.ads.store') }}';
        document.getElementById('banner_id').value = '';
        submitText.textContent = '@lang("Salvar Banner")';
        cardTitle.textContent = '@lang("Cadastrar Banner")';
        cancelBtn.style.display = 'none';
        
        // Esconder previews
        document.getElementById('preview_web').style.display = 'none';
        document.getElementById('preview_mobile').style.display = 'none';
        
        // Tornar uploads obrigatórios novamente
        imageWebInput.setAttribute('required', 'required');
        imageMobileInput.setAttribute('required', 'required');
    }
});
</script>
@endpush

@push('breadcrumb-plugins')
    <span class="badge badge--info">@lang('Frontend')</span>
@endpush

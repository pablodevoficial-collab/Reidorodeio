@props([
    'name' => 'image',
    'imagePath' => null,
    'image' => null,
    'type' => null,
    'size' => null,
    'required' => true,
])

@php
    $preview = $imagePath ?: $image;
    if (!$preview) {
        $preview = asset('assets/images/logo_icon/favicon.png');
    }
@endphp

<div {{ $attributes->merge(['class' => 'image-uploader']) }}>
    <div class="mb-2">
        <img src="{{ $preview }}" alt="preview" style="max-width: 100%; height: auto; border-radius: 6px;" onerror="this.style.display='none'">
    </div>

    <input
        type="file"
        name="{{ $name }}"
        accept="image/*"
        @if($required) required @endif
        class="form-control"
    >

    @if(!empty($size))
        <small class="text-muted">@lang('Tamanho recomendado'): {{ $size }}</small>
    @endif
</div>

@props(['text' => 'Rei do Rodeio', 'size' => 'clamp(2.4rem,5vw,4rem)'])
<h1 class="rr-brand-font brand-title" style="font-size:{{ $size }};margin:0;line-height:1.1;display:inline-flex;align-items:center;gap:.75rem;">
    <img src="{{ asset('assets/img/logo.png') }}" alt="Logo Rei do Rodeio" class="brand-logo" style="width:72px;height:auto;display:block;filter:drop-shadow(0 4px 8px rgba(0,0,0,.35));" loading="lazy" />
    <span>{{ $text }}</span>
</h1>
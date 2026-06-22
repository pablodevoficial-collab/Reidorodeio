@props([
    'route' => null,
    'url' => null,
    'label' => null,
])

@php
    $href = $route ?: $url ?: url()->previous();
    $text = $label ?: __('Voltar');
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'btn btn-sm btn-outline--primary']) }}>
    <i class="las la-arrow-left"></i> {{ $text }}
</a>

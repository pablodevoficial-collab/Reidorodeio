@props([
    'form' => null,
])

@php
    // Fallback ultra simples: se $form vier como array/collection, só mostramos um aviso.
    // O sistema completo de form generator pode ser reintroduzido depois.
@endphp

@if(empty($form))
    <div class="alert alert-warning mb-3">@lang('Nenhum campo configurado para este formulário.')</div>
@elseif(is_string($form))
    {!! $form !!}
@else
    <div class="alert alert-warning mb-3">
        @lang('Form generator indisponível neste build. Campos não renderizados automaticamente.')
    </div>
@endif

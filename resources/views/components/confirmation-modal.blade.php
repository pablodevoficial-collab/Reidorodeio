@props([
    'id' => 'confirmationModal',
    'title' => null,
    'confirmText' => null,
    'cancelText' => null,
])

@php
    $title = $title ?: __('Confirmação');
    $confirmText = $confirmText ?: __('Confirmar');
    $cancelText = $cancelText ?: __('Cancelar');
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="{{ $id }}Question">{{ __('Tem certeza?') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--secondary" data-bs-dismiss="modal">{{ $cancelText }}</button>
                <form method="POST" action="#" id="{{ $id }}Form">
                    @csrf
                    <button type="submit" class="btn btn--primary" id="{{ $id }}Submit">{{ $confirmText }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
(function () {
    "use strict";

    function openModal(question, action, method) {
        var q = document.getElementById(@json($id . 'Question'));
        var f = document.getElementById(@json($id . 'Form'));
        if (q) q.textContent = question || @json(__('Tem certeza?'));
        if (f && action) f.setAttribute('action', action);

        if (f) {
            // Support method spoofing (DELETE/PUT/PATCH) via hidden _method input
            var m = (method || 'POST').toUpperCase();
            var existing = f.querySelector('input[name="_method"]');
            if (existing) existing.remove();
            if (m !== 'POST') {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_method';
                input.value = m;
                f.appendChild(input);
            }
        }

        if (window.bootstrap) {
            var el = document.getElementById(@json($id));
            if (!el) return;
            var modal = bootstrap.Modal.getOrCreateInstance(el);
            modal.show();
            return;
        }

        // Fallback: native confirm
        if (action && confirm(question || @json(__('Tem certeza?')))) {
            window.location.href = action;
        }
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest && e.target.closest('.confirmationBtn');
        if (!btn) return;
        e.preventDefault();
        openModal(btn.getAttribute('data-question'), btn.getAttribute('data-action'), btn.getAttribute('data-method'));
    });
})();
</script>
@endpush

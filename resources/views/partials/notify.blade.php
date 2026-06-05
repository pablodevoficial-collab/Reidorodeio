<script src="{{ asset('assets/global/js/iziToast.min.js') }}"></script>

<script>
    "use strict";
    
    function notify(status, message) {
        if (typeof message == 'string') {
            iziToast[status]({
                message: message,
                position: "topRight"
            });
        } else {
            $.each(message, function(i, val) {
                iziToast[status]({
                    message: val,
                    position: "topRight"
                });
            });
        }
    }

    // Notificações do Laravel (sessão)
    @if(session()->has('notify'))
        @foreach(session('notify') as $msg)
            notify('{{ $msg[0] }}', '{{ $msg[1] }}');
        @endforeach
    @endif

    // Notificações de erro de validação
    @if ($errors->any())
        @php
            $collection = collect($errors->all());
            $errors = $collection->unique();
        @endphp
        
        @foreach ($errors as $error)
            notify('error', '{{ $error }}');
        @endforeach
    @endif

    // Script global de notificação
    window.notify = notify;
</script>

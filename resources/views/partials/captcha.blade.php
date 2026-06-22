@php
    $customCaptcha = loadCustomCaptcha();
    $googleCaptcha = loadReCaptcha();
@endphp

@if($googleCaptcha)
    <div class="mb-3">
        {!! $googleCaptcha !!}
        <div id="g-recaptcha-error"></div>
    </div>
@endif

@if($customCaptcha)
    <div class="form-group">
        <div class="mb-2">
            {!! $customCaptcha !!}
        </div>
        <label class="form-label">@lang('Captcha')</label>
        <input type="text" name="captcha" class="form-control form--control" required>
    </div>
@endif

@if($googleCaptcha)
    @push('script')
        <script>
            (function(){
                "use strict";
                const forms = document.querySelectorAll('.verify-gcaptcha');
                forms.forEach((form) => {
                    form.addEventListener('submit', function(event){
                        const response = grecaptcha.getResponse();
                        if(response.length === 0){
                            const target = document.getElementById('g-recaptcha-error');
                            if(target){
                                target.innerHTML = '<span class="text--danger">@lang("Captcha field is required.")</span>';
                            }
                            event.preventDefault();
                        }
                    });
                });
                window.verifyCaptcha = function(){
                    const target = document.getElementById('g-recaptcha-error');
                    if(target){
                        target.innerHTML = '';
                    }
                }
            })();
        </script>
    @endpush
@endif

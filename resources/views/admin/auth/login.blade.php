@extends('admin.layouts.master')
@section('content')
<div class="login-main">
    <canvas id="rrParticlesCanvas" class="rr-particles-canvas" aria-hidden="true"></canvas>
    <!-- Clean login layout: avoid Bootstrap column constraints to prevent narrow/overlapped card -->
    <div class="login-area" role="main" aria-labelledby="admin-login-title">
        <div class="login-wrapper" style="display:flex; width:100%;">

            <!-- Brand panel (left on desktop, top on mobile) -->
            <div class="login-wrapper__top" aria-hidden="false">
                <div class="mb-3 text-center">
                    <img src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="Logo Rei do Rodeio" class="rr-logo rr-logo-enter">
                </div>
                <h3 id="admin-login-title" class="title">Bem-vindo ao <strong class="rr-brand">Rei do rodeio.</strong></h3>
            </div>

            <!-- Form panel (right on desktop, bottom on mobile) -->
            <div class="login-wrapper__body">
                <form action="{{ route('admin.login.submit') }}" method="POST" class="cmn-form mt-0 verify-gcaptcha login-form" novalidate>
                    @csrf

                    @if (session('error'))
                        <div class="alert alert-danger py-2 px-3 mb-3">{{ session('error') }}</div>
                    @endif

                    @if (session()->has('notify'))
                        @foreach (session('notify') as $msg)
                            @if (is_array($msg) && count($msg) >= 2)
                                <div class="alert alert-{{ $msg[0] === 'success' ? 'success' : 'danger' }} py-2 px-3 mb-3">{{ __($msg[1]) }}</div>
                            @endif
                        @endforeach
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger py-2 px-3 mb-3">{{ $errors->first() }}</div>
                    @endif

                    <div class="form-group">
                        <label for="username">Usuário</label>
                        <input id="username" type="text" class="form-control" value="{{ old('username') }}" name="username" required autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label for="password">Senha</label>
                        <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password">
                    </div>

                    <x-captcha />

                    <div class="form-group mt-3">
                        <button type="submit" class="btn cmn-btn w-100">ENTRAR</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    /* Improved admin login layout: two-column on desktop, stacked on mobile */

    /* Ensure the wrapper container used on the login page is wide and centered */
    .login-main .container.custom-container {
        max-width: 600px !important;
        width: 100% !important;
        margin: 0 auto !important;
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }
    .login-main { 
        min-height: 100vh; 
        display: flex; 
        flex-direction: column; 
        justify-content: center; 
        align-items: center; 
        flex-wrap: wrap; 
        background-color: #000 !important; 
        background-size: cover !important; 
        background-position: center center !important;
        padding: 1rem;
        overflow: hidden;
    }
    
    html, body {
        overflow: hidden !important;
        height: 100% !important;
        position: fixed !important;
        width: 100% !important;
    }

    /* container for the card */
    .login-area { 
        width: 100%; 
        max-width: 760px; 
        margin: 0 auto; 
        background: linear-gradient(135deg, #1a1f2e 0%, #0f1419 100%);
        border-radius: 14px; 
        overflow: hidden; 
        display: flex; 
        flex-direction: row;
        box-shadow: 0 12px 40px rgba(0,0,0,0.7);
    }

    /* Wrapper flex container */
    .login-wrapper {
        display: flex !important;
        flex-direction: row !important;
        width: 100% !important;
    }

    /* left brand panel */
    .login-wrapper__top {
        width: 38%;
        min-width: 220px;
        padding: 30px 18px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(180deg,#FF6B35 0%, #F7931E 100%) !important;
        color: #fff !important;
        border-top-left-radius: 14px;
        border-bottom-left-radius: 14px;
        text-align: center;
    }
    .login-wrapper__top .rr-logo { width: 96px; height: auto; display: block; margin: 6px auto 10px; }
    .login-wrapper__top .title { margin: 0; font-size: 1.05rem; font-weight: 700; color: #fff; line-height: 1.2; }

    /* right form panel - make transparent and remove decorative backgrounds */
    .login-wrapper__body {
        width: 62%;
        padding: 28px 26px;
        background: transparent !important;
        background-image: none !important;
        border-top-right-radius: 14px;
        border-bottom-right-radius: 14px;
        position: relative;
        color: #fff;
    }

    /* Hide any decorative pseudo-elements that might add shapes inside the panel */
    .login-wrapper__body::before,
    .login-wrapper__body::after,
    .login-area::before,
    .login-area::after {
        display: none !important;
        background: none !important;
        content: none !important;
    }

    .cmn-form .form-group { margin-bottom: 14px; }
    .cmn-form .form-control { background: #0f0f0f !important; border: 1px solid rgba(255,255,255,0.06) !important; color: #fff !important; padding: 12px 14px; border-radius: 8px; width: 100%; }
    .cmn-form .form-control::placeholder { color: rgba(255,255,255,0.45) !important; }
    .cmn-form .forget-text { color: rgba(255,255,255,0.65) !important; font-size: 0.875rem; }

    .cmn-form .btn.cmn-btn { display: inline-block; width: 100%; padding: 12px 14px; background: linear-gradient(135deg,#FF6B35 0%,#F7931E 100%) !important; color: #111827 !important; border: none !important; border-radius: 8px; font-weight: 700; }
    .cmn-form .btn.cmn-btn:hover { filter: brightness(1.02); box-shadow: 0 8px 24px rgba(255,107,53,0.18); }

    /* make the logo panel content not overflow and keep proportions */
    .login-wrapper__top .title, .login-wrapper__top p { word-break: break-word; }

    /* ========== MOBILE: LAYOUT VERTICAL ========== */
    @media (max-width: 991.98px) {
        .login-area { 
            flex-direction: column !important; 
            max-width: 320px !important;
        }
        .login-wrapper {
            flex-direction: column !important;
            width: 100% !important;
        }
        .login-wrapper__top { 
            width: 100% !important; 
            min-width: unset !important;
            border-radius: 12px 12px 0 0 !important; 
            border-bottom-left-radius: 0 !important;
            padding: 16px 14px !important;
        }
        .login-wrapper__top .rr-logo { 
            width: 56px !important; 
        }
        .login-wrapper__top .title {
            font-size: 0.9rem;
        }
        .login-wrapper__body { 
            width: 100% !important; 
            padding: 16px 14px !important; 
            border-radius: 0 0 12px 12px !important;
            border-top-right-radius: 0 !important;
        }
        .cmn-form .form-group { 
            margin-bottom: 10px; 
        }
        .cmn-form .form-control {
            padding: 10px 12px;
            font-size: 0.9rem;
        }
        .cmn-form .btn.cmn-btn {
            padding: 10px 12px;
            font-size: 0.9rem;
        }
        .cmn-form label {
            font-size: 0.85rem;
            margin-bottom: 4px;
        }
    }

    @media (max-width: 479.98px) {
        .login-main {
            padding: 0.5rem;
        }
        .login-area { 
            max-width: 290px !important;
            margin: 0 auto;
            border-radius: 10px;
        }
        .login-wrapper__top { 
            padding: 14px 12px !important;
            border-radius: 10px 10px 0 0 !important;
        }
        .login-wrapper__top .rr-logo { 
            width: 48px !important; 
        }
        .login-wrapper__top .title {
            font-size: 0.85rem;
        }
        .login-wrapper__body { 
            padding: 14px 12px !important;
            border-radius: 0 0 10px 10px !important;
        }
        .cmn-form .form-control {
            padding: 8px 10px;
            font-size: 0.85rem;
        }
        .cmn-form .btn.cmn-btn {
            padding: 8px 10px;
            font-size: 0.85rem;
        }
    }

    /* Accessibility: focus state */
    .cmn-form .form-control:focus { outline: none; box-shadow: 0 0 0 4px rgba(255,107,53,0.12); border-color: rgba(255,107,53,0.6) !important; }

    /* Particles canvas (copied from frontend layout) */
    .rr-particles-canvas {
        position: fixed;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: -1;
        opacity: 0.9;
        filter: drop-shadow(0 0 12px rgba(249, 115, 22, 0.35));
    }
    @media (max-width: 768px) {
        .rr-particles-canvas { opacity: 0.6; }
    }
</style>
@endpush

@push('script')
<script>
    (function(){
        var canvas = document.getElementById('rrParticlesCanvas');
        if(!canvas || !canvas.getContext){
            return;
        }

        var ctx = canvas.getContext('2d');
        var particles = [];
        var viewportWidth = window.innerWidth;
        var viewportHeight = window.innerHeight;
        var animationFrameId;
        var resizeTimer;
        var reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
        var motionFactor = reduceMotionQuery.matches ? 0.35 : 1;
        var rootStyles = window.getComputedStyle(document.documentElement);
        var primaryHex = (rootStyles.getPropertyValue('--rr-primary') || '#f97316').trim() || '#f97316';

        function hexToRgb(hex) {
            var sanitized = hex.replace('#', '');
            if (sanitized.length === 3) {
                sanitized = sanitized.split('').map(function(c){ return c + c; }).join('');
            }
            var parsed = parseInt(sanitized, 16);
            if (isNaN(parsed)) {
                return { r: 249, g: 115, b: 22 };
            }
            return {
                r: (parsed >> 16) & 255,
                g: (parsed >> 8) & 255,
                b: parsed & 255
            };
        }

        var primaryRgb = hexToRgb(primaryHex);

        function randomBetween(min, max) {
            return Math.random() * (max - min) + min;
        }

        function setCanvasSize() {
            viewportWidth = window.innerWidth;
            viewportHeight = window.innerHeight;
            var ratio = window.devicePixelRatio || 1;
            canvas.width = viewportWidth * ratio;
            canvas.height = viewportHeight * ratio;
            canvas.style.width = viewportWidth + 'px';
            canvas.style.height = viewportHeight + 'px';
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.scale(ratio, ratio);
        }

        function createParticle() {
            var angle = Math.random() * Math.PI * 2;
            var speed = randomBetween(0.3, 1.2) * motionFactor;
            return {
                x: Math.random() * viewportWidth,
                y: Math.random() * viewportHeight,
                vx: Math.cos(angle) * speed,
                vy: Math.sin(angle) * speed,
                radius: randomBetween(0.4, 1.8),
                alpha: randomBetween(0.25, 0.9),
                twinkle: randomBetween(0.003, 0.01) * (motionFactor * 0.8 + 0.4)
            };
        }

        function initParticles() {
            var count;
            if (viewportWidth < 640) {
                count = 40;
            } else if (viewportWidth < 1200) {
                count = 75;
            } else {
                count = 110;
            }
            particles = [];
            for (var i = 0; i < count; i++) {
                particles.push(createParticle());
            }
        }

        function wrapParticle(particle) {
            if (particle.x < -10) particle.x = viewportWidth + 10;
            if (particle.x > viewportWidth + 10) particle.x = -10;
            if (particle.y < -10) particle.y = viewportHeight + 10;
            if (particle.y > viewportHeight + 10) particle.y = -10;
        }

        function drawFrame(staticFrame) {
            ctx.clearRect(0, 0, viewportWidth, viewportHeight);
            particles.forEach(function(particle) {
                if (!staticFrame) {
                    particle.x += particle.vx;
                    particle.y += particle.vy;
                    particle.alpha += particle.twinkle * (Math.random() > 0.5 ? 1 : -1);
                    if (particle.alpha > 0.9 || particle.alpha < 0.15) {
                        particle.alpha = particle.alpha > 0.9 ? 0.9 : 0.15;
                        particle.twinkle *= -1;
                    }
                    wrapParticle(particle);
                }
                ctx.beginPath();
                ctx.fillStyle = 'rgb(' + primaryRgb.r + ', ' + primaryRgb.g + ', ' + primaryRgb.b + ')';
                ctx.globalAlpha = particle.alpha;
                ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
                ctx.fill();
            });
            ctx.globalAlpha = 1;
        }

        function loop() {
            drawFrame(false);
            animationFrameId = window.requestAnimationFrame(loop);
        }

        function restartAnimation() {
            window.cancelAnimationFrame(animationFrameId);
            if (document.hidden) {
                drawFrame(true);
                return;
            }
            loop();
        }

        function handleReduceMotionChange(event) {
            motionFactor = event.matches ? 0.35 : 1;
            initParticles();
            restartAnimation();
        }

        if (reduceMotionQuery.addEventListener) {
            reduceMotionQuery.addEventListener('change', handleReduceMotionChange);
        } else if (reduceMotionQuery.addListener) {
            reduceMotionQuery.addListener(handleReduceMotionChange);
        }

        window.addEventListener('resize', function() {
            window.cancelAnimationFrame(animationFrameId);
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                setCanvasSize();
                initParticles();
                restartAnimation();
            }, 150);
        });

        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                window.cancelAnimationFrame(animationFrameId);
                drawFrame(true);
            } else {
                restartAnimation();
            }
        });

        setCanvasSize();
        initParticles();
        restartAnimation();
    })();

    // CSRF Token Auto-Refresh - evita "Page Expired"
    (function() {
        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        var csrfInput = document.querySelector('input[name="_token"]');
        
        function refreshCsrfToken() {
            fetch('{{ url("/sanctum/csrf-cookie") }}', {
                method: 'GET',
                credentials: 'same-origin'
            }).catch(function() {
                // Se sanctum não estiver disponível, tenta recarregar o token via endpoint customizado
                fetch('{{ url("/csrf-refresh") }}', {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                }).then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.token) {
                        if (csrfToken) csrfToken.setAttribute('content', data.token);
                        if (csrfInput) csrfInput.value = data.token;
                    }
                }).catch(function() {});
            });
        }

        // Atualiza o token a cada 15 minutos para evitar expiração
        setInterval(refreshCsrfToken, 15 * 60 * 1000);

        // Se a página ficar oculta por muito tempo, atualiza o token ao voltar
        var lastVisibleTime = Date.now();
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                var elapsed = Date.now() - lastVisibleTime;
                // Se ficou mais de 10 minutos oculto, atualiza o token
                if (elapsed > 10 * 60 * 1000) {
                    refreshCsrfToken();
                }
            } else {
                lastVisibleTime = Date.now();
            }
        });

        // Intercepta erro 419 no submit e recarrega a página
        var loginForm = document.querySelector('.login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                var form = this;
                // Deixa o submit normal acontecer, mas vamos criar um handler para erro 419
            });
        }
    })();
</script>
@endpush

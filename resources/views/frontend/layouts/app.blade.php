<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? config('app.name', 'Rei do Rodeio') }}</title>
    <meta name="theme-color" content="#f97316">
    <meta name="description" content="Bolão oficial do Rei do Rodeio">

    <link rel="icon" type="image/png" href="{{ siteFavicon() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="{{ asset('assets/fonts/ethnocentric.css') }}">
    <link rel="stylesheet" href="{{ versionedAsset('assets/css/main.css') }}">
    <link rel="stylesheet" href="{{ versionedAsset('assets/css/mobile.css') }}">
    <link rel="stylesheet" href="{{ versionedAsset('assets/css/animations.css') }}">
    <link rel="stylesheet" href="{{ versionedAsset('assets/css/utilities.css') }}">
    <link rel="stylesheet" href="{{ versionedAsset('assets/css/rei-pro.css') }}">
    @stack('style')

    <style>
        :root {
            --rr-bg: #050816;
            --rr-bg-soft: #0f172a;
            --rr-card: rgba(15, 23, 42, 0.84);
            --rr-card-border: rgba(255, 255, 255, 0.08);
            --rr-text: #e2e8f0;
            --rr-text-soft: #94a3b8;
            --rr-accent: #f97316;
            --rr-accent-strong: #ea580c;
            --rr-blue: #2563eb;
            --rr-success: #10b981;
            --rr-shell-width: min(1180px, calc(100vw - 32px));
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
            background: var(--rr-bg);
            scroll-behavior: auto;
            -webkit-text-size-adjust: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            color: var(--rr-text);
            background:
                radial-gradient(circle at top, rgba(249, 115, 22, 0.16), transparent 26%),
                radial-gradient(circle at bottom left, rgba(37, 99, 235, 0.12), transparent 30%),
                linear-gradient(180deg, #050816 0%, #020617 100%);
            overflow-x: hidden;
            overscroll-behavior-y: auto;
            -webkit-overflow-scrolling: touch;
            touch-action: pan-y;
        }

        @media (max-width: 767px) {
            body {
                background: linear-gradient(180deg, #050816 0%, #020617 100%);
            }

            .rr-panel {
                backdrop-filter: none;
                box-shadow: none;
            }
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        textarea,
        select {
            font: inherit;
        }

        .rr-site-shell {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .rr-main {
            width: 100%;
            padding: 0 !important;
            margin: 0 !important;
            min-height: 100dvh !important;
        }

        .rr-panel {
            border: 1px solid var(--rr-card-border);
            background: var(--rr-card);
            backdrop-filter: blur(14px);
            border-radius: 28px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.24);
        }

        .rr-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 52px;
            padding: 0 22px;
            border: 0;
            border-radius: 16px;
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
        }

        :where(
            button,
            [role="button"],
            a.rr-btn,
            .rr-btn,
            .rr-entry-card,
            .rr-card__btn,
            .rr-hero__btn,
            .rr-side__nav-btn,
            .rr-mobile-actions__btn,
            .rr-mobile-footer__btn,
            .rr-arena-back,
            .rr-choice__btn
        ) {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
            user-select: none;
            transform: translateZ(0);
            transition: transform 0.14s ease, filter 0.14s ease, box-shadow 0.14s ease, opacity 0.14s ease;
        }

        :where(
            button,
            [role="button"],
            a.rr-btn,
            .rr-btn,
            .rr-entry-card,
            .rr-card__btn,
            .rr-hero__btn,
            .rr-side__nav-btn,
            .rr-mobile-actions__btn,
            .rr-mobile-footer__btn,
            .rr-arena-back,
            .rr-choice__btn
        ):active:not(:disabled):not([aria-disabled="true"]),
        :where(
            button,
            [role="button"],
            a.rr-btn,
            .rr-btn,
            .rr-entry-card,
            .rr-card__btn,
            .rr-hero__btn,
            .rr-side__nav-btn,
            .rr-mobile-actions__btn,
            .rr-mobile-footer__btn,
            .rr-arena-back,
            .rr-choice__btn
        ).is-pressed {
            transform: scale(0.975);
            filter: brightness(1.04);
        }

        .rr-btn:hover {
            transform: translateY(-1px);
        }

        .rr-btn:disabled {
            cursor: not-allowed;
            opacity: 0.7;
            transform: none;
        }

        .rr-btn--primary {
            color: #fff;
            background: linear-gradient(135deg, var(--rr-accent), var(--rr-accent-strong));
            box-shadow: 0 16px 30px rgba(234, 88, 12, 0.28);
        }

        .rr-btn--secondary {
            color: #fff;
            background: linear-gradient(135deg, #1e293b, #0f172a);
            border: 1px solid rgba(148, 163, 184, 0.16);
        }

        .rr-field {
            display: grid;
            gap: 8px;
        }

        .rr-field label {
            color: #cbd5e1;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .rr-input {
            width: 100%;
            min-height: 52px;
            padding: 0 16px;
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 16px;
            background: rgba(15, 23, 42, 0.78);
            color: #f8fafc;
        }

        .rr-input:focus {
            outline: 0;
            border-color: rgba(249, 115, 22, 0.65);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.14);
        }

        .rr-errors {
            display: grid;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .rr-errors li {
            padding: 12px 14px;
            border-radius: 14px;
            background: rgba(127, 29, 29, 0.36);
            border: 1px solid rgba(248, 113, 113, 0.2);
            color: #fecaca;
            font-size: 0.92rem;
            font-weight: 600;
        }

        .rr-helper {
            color: var(--rr-text-soft);
            font-size: 0.86rem;
        }

        @media (max-width: 767px) {
        }
    </style>
</head>
<body class="{{ trim($bodyClass ?? '') }}">
    @unless(!empty($hideChrome))
        @include('frontend.partials.header')
    @endunless

    <main class="rr-main">
        <div class="rr-site-shell">
            @yield('content')
        </div>
    </main>

    @unless(!empty($hideChrome))
        @include('frontend.partials.footer')
    @endunless

    <!-- Alpine.js - Reactive JavaScript -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- GSAP - Professional Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" integrity="sha512-16esztpgIHSXC7CjMc6PT6v3ssS4pHbnRoleBumf/GmO8MPqMrSmzJkMrWGDIyWBBNtfLBOjqAj+SlJpLmWOQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- GSAP ScrollTo Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollToPlugin.min.js" integrity="sha512-tEXGiDmqBwmXBP7J3TYJVzKqAzLVa8HhRVCg37R5L9H9jEL0/z4dJVc3eLF9gfmXXVDgfqMRVf8IIRAc/SRRQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('assets/js/gsap-animations.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.4/dist/confetti.browser.min.js"></script>
    <script src="{{ versionedAsset('assets/js/rei-pro.js') }}"></script>

    @auth
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            setInterval(function () {
                fetch('{{ route("session.heartbeat") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(function (response) {
                    if (response.status !== 401) {
                        return null;
                    }

                    return response.json().then(function () {
                        Swal.fire({
                            imageUrl: '{{ asset("assets/images/logo_icon/favicon.png") }}',
                            imageHeight: 60,
                            imageAlt: 'Rei do Rodeio',
                            title: 'Sessão Encerrada',
                            text: 'Sua conta foi conectada em outro dispositivo.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            confirmButtonText: 'Recarregar',
                            confirmButtonColor: '#f97316',
                            background: '#1e293b',
                            color: '#fff'
                        }).then(function () {
                            window.location.href = '/';
                        });

                        setTimeout(function () {
                            window.location.href = '/';
                        }, 3000);
                    });
                })
                .catch(function () {});
            }, 5000);
        })();
    </script>
    @endauth

    @stack('script')
</body>
</html>

<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ gs()->siteName($pageTitle ?? '') }}</title>

    <link rel="shortcut icon" type="image/png" href="{{ siteFavicon() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800&family=Teko:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Ícones -->
    <link rel="stylesheet" href="{{ asset('assets/global/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Principal do Admin -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/app.css') }}">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    @stack('style-lib')

    <style>
        :root {
            --rr-font-body: 'Barlow', system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
            --rr-font-display: 'Teko', 'Barlow', system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
        }

        body,
        input,
        button,
        select,
        textarea {
            font-family: var(--rr-font-body) !important;
            font-variant-numeric: tabular-nums;
            font-feature-settings: 'tnum' 1, 'lnum' 1;
        }

        body {
            line-height: 1.55;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1,h2,h3,h4,h5,h6,
        .page-title,
        .breadcrumb,
        .card-header,
        .modal-title {
            font-family: var(--rr-font-display) !important;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        table .user .thumb img {
            border: 2px solid #f3f3f3;
            box-shadow: none;
        }

        .select2-container--default .select2-results__group {
            cursor: default;
            display: block;
            padding: 6px;
            color: #626262;
            font-weight: 500;
        }
        body.admin-shell {
            background:
                radial-gradient(circle at top, rgba(249, 115, 22, 0.16), transparent 22%),
                radial-gradient(circle at right bottom, rgba(37, 99, 235, 0.12), transparent 24%),
                linear-gradient(180deg, #050816 0%, #020617 100%) !important;
        }
        .page-title {
            font-size: 1.7rem;
            letter-spacing: .03em;
        }
        .pointer-events-none {
            pointer-events: none;
        }
        .pointer-events-auto {
            pointer-events: auto;
        }
    </style>

    <style>
    @media (max-width: 767px) {
        .container-fluid,
        .body-wrapper,
        .bodywrapper__inner {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }

        .bodywrapper__inner {
            padding-bottom: 1rem !important;
        }

        .card,
        .modal-content,
        .table-responsive {
            overflow: hidden;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .breadcrumb,
        .page-title,
        .breadcrumb-plugins {
            flex-wrap: wrap;
        }
    }
    </style>

    @stack('style')

    <!-- Tema final do painel admin, alinhado ao frontend -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bolao-admin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/modern-theme.css') }}">
</head>

<body class="admin-shell">
    @yield('content')

    <!-- jQuery (necessário para o painel admin) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Bootstrap Bundle (se necessário) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Scripts do Admin -->
    <script src="{{ asset('assets/admin/js/app.js') }}"></script>
    
    @stack('script-lib')
    @stack('script')

</body>

</html>

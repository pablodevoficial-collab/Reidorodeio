<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'Rei do Rodeio' }}</title>
    <meta name="description" content="Bolao de rodeio com entrada rapida, arena ao vivo e montagem de equipe em poucos toques.">
    <link rel="shortcut icon" type="image/png" href="{{ siteFavicon() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800&family=Teko:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ versionedAsset('assets/frontend/css/app.css', (string) @filemtime(public_path('assets/frontend/css/app.css'))) }}">
    <link rel="stylesheet" href="{{ versionedAsset('assets/frontend/css/arena.css', (string) @filemtime(public_path('assets/frontend/css/arena.css'))) }}">
    <link rel="stylesheet" href="{{ versionedAsset('assets/frontend/css/responsive.css', (string) @filemtime(public_path('assets/frontend/css/responsive.css'))) }}">
</head>
<body class="@yield('body-class', 'front-shell')">
    @yield('content')
    <script src="{{ versionedAsset('assets/frontend/js/app.js', (string) @filemtime(public_path('assets/frontend/js/app.js'))) }}"></script>
</body>
</html>

@php
    $sidenav = json_decode($sidenav);

    $settings = file_get_contents(resource_path('views/admin/setting/settings.json'));
    $settings = json_decode($settings);

    $routesData = [];
    foreach (\Illuminate\Support\Facades\Route::getRoutes() as $route) {
        $name = $route->getName();
        if (strpos($name, 'admin') !== false) {
            $routeData = [
                $name => url($route->uri()),
            ];

            $routesData[] = $routeData;
        }
    }
@endphp

<!-- navbar-wrapper start -->
<nav class="rr-topnav">
    <div class="navbar__left">
        <button type="button" class="res-sidebar-open-btn" id="sidebarOpenBtn" aria-label="Abrir menu">
            <i class="las la-bars"></i>
        </button>
        <span class="rr-topnav-title">Painel Admin</span>
    </div>
    <div class="navbar__right">
        <a href="{{ route('admin.logout') }}" class="rr-topnav-logout" title="Sair">
            <i class="las la-sign-out-alt"></i>
        </a>
    </div>
</nav>
<!-- navbar-wrapper end -->

@push('style')
<style>
/* TopNav Admin */
.rr-topnav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background:
        linear-gradient(135deg, rgba(15, 23, 42, 0.94), rgba(7, 10, 18, 0.98)),
        radial-gradient(circle at 10% 0%, rgba(249, 115, 22, 0.18), transparent 34%);
    border-bottom: 1px solid rgba(249, 115, 22, 0.16);
    box-shadow: 0 14px 30px rgba(0, 0, 0, 0.22);
    backdrop-filter: blur(18px);
    position: sticky;
    top: 0;
    z-index: 100;
}

.navbar__left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

    .navbar__right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-left: auto; /* empurra para a direita mantendo o fluxo flex */
}

/* Botão abrir sidebar */
.res-sidebar-open-btn {
    display: none;
    width: 44px;
    height: 44px;
    border-radius: 8px;
    background: linear-gradient(135deg, #f59e0b, #ea580c);
    border: none;
    color: #0f172a;
    cursor: pointer;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    transition: all 0.2s;
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
}
.res-sidebar-open-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(249, 115, 22, 0.4);
}
.res-sidebar-open-btn:active {
    transform: scale(0.95);
}

@media (max-width: 991.98px) {
    .res-sidebar-open-btn {
        display: flex;
    }
}

.rr-topnav-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #fff7ed;
}

.rr-topnav-logout {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fdba74; /* icone laranja */
    text-decoration: none;
    transition: all 0.2s;
    font-size: 1.25rem;
    background: transparent;
}
.rr-topnav-logout:hover {
    background: rgba(249, 115, 22, 0.14);
    color: #fff7ed;
}

@media (max-width: 480px) {
    /* Em mobile, centraliza o título no topo (centro horizontal) */
    .rr-topnav-title {
        display: block;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        margin: 0;
        pointer-events: none; /* não interfere em cliques do botão de menu */
        font-size: 1rem;
    }

    /* Garante que o container seja relativo para referência do título absoluto */
    .rr-topnav {
        position: relative;
    }

    /* Ajustes para que o botão abrir sidebar e o logout fiquem visíveis */
    .res-sidebar-open-btn {
        z-index: 2;
    }
    .navbar__right {
        z-index: 2;
    }
}

/* Extra responsive helpers: centraliza títulos e plugins do breadcrumb em mobile */
@media (max-width: 767px) {
    .d-flex.mb-30.flex-wrap.gap-3.justify-content-between.align-items-center {
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    h6.page-title {
        width: 100%;
        margin-bottom: 10px;
    }

    .d-flex.flex-wrap.justify-content-end.gap-2.align-items-center.breadcrumb-plugins {
        justify-content: center;
    }
}
</style>
@endpush

@push('script')
    <script>
        "use strict";
        var routes = @json($routesData);
        var settingsData = Object.assign({}, @json($settings), @json($sidenav));
    </script>
@endpush

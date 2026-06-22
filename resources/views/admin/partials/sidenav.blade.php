@php
    $routeExists = static fn (?string $routeName): bool => empty($routeName) || \Illuminate\Support\Facades\Route::has($routeName);
    $currentAdmin = auth('admin')->user();
    $sideBarLinks = collect(json_decode($sidenav) ?: [])
        ->map(function ($item) use ($currentAdmin, $routeExists) {
            if (!$currentAdmin || !method_exists($currentAdmin, 'canAccessAdminRoute')) {
                if (!empty($item->submenu) && is_array($item->submenu)) {
                    $item->submenu = array_values(array_filter($item->submenu, function ($submenu) use ($routeExists) {
                        return $routeExists($submenu->route_name ?? null);
                    }));

                    return !empty($item->submenu) ? $item : null;
                }

                return $routeExists($item->route_name ?? null) ? $item : null;
            }

            if (!empty($item->submenu) && is_array($item->submenu)) {
                $item->submenu = array_values(array_filter($item->submenu, function ($submenu) use ($currentAdmin, $routeExists) {
                    $routeName = $submenu->route_name ?? null;

                    return $routeExists($routeName) && $currentAdmin->canAccessAdminRoute($routeName);
                }));

                return !empty($item->submenu) ? $item : null;
            }

            if (
                !empty($item->route_name)
                && (!$routeExists($item->route_name) || !$currentAdmin->canAccessAdminRoute($item->route_name))
            ) {
                return null;
            }

            return $item;
        })
        ->filter()
        ->values();
    $segments = collect($sideBarLinks)->groupBy('segment');
@endphp

{{-- Overlay para fechar sidebar no mobile --}}
<div class="rr-sidebar-overlay" id="sidebarOverlay"></div>

<div class="sidebar bg--dark" id="adminSidebar">
    <button class="res-sidebar-close-btn" id="sidebarCloseBtn" type="button" aria-label="Fechar menu">
        <i class="las la-times"></i>
    </button>
    <div class="sidebar__inner">
        <div class="sidebar__logo">
            <a href="{{ route('admin.dashboard') }}" class="sidebar__main-logo">
                <img src="{{ asset('assets/images/logo_icon/logo.png') }}" alt="Rei do Rodeio" class="rr-logo rr-logo-enter">
            </a>
        </div>
        <div class="sidebar__menu-wrapper" id="sidebarMenuWrapper">
            <ul class="sidebar__menu">
                @foreach ($segments as $key => $sideBarLinks)
                    @if ($key)
                        <li class="sidebar__menu-header">{{ __($key) }}</li>
                    @endif
                    @foreach ($sideBarLinks as $key => $data)
                        @if (@$data->header)
                            <li class="sidebar__menu-header">{{ __($data->header) }}</li>
                        @endif
                        @if (@$data->submenu)
                            <li class="sidebar-menu-item sidebar-dropdown">
                                <a href="javascript:void(0)" class="{{ menuActive(@$data->menu_active, 3) }}">
                                    <i class="menu-icon {{ @$data->icon }}"></i>
                                    <span class="menu-title">{{ __(@$data->title) }}</span>
                                    @foreach (@$data->counters ?? [] as $counter)
                                        @if ($$counter > 0)
                                            <span class="menu-badge menu-badge-level-one bg--warning ms-auto">
                                                <i class="fas fa-exclamation"></i>
                                            </span>
                                            @break
                                        @endif
                                    @endforeach
                                </a>
                                <div class="sidebar-submenu {{ menuActive(@$data->menu_active, 2) }} ">
                                    <ul>
                                        @foreach ($data->submenu as $menu)
                                            @php
                                                $submenuParams = null;
                                                if (@$menu->params) {
                                                    foreach ($menu->params as $submenuParamVal) {
                                                        $submenuParams[] = array_values((array) $submenuParamVal)[0];
                                                    }
                                                }
                                            @endphp
                                            <li class="sidebar-menu-item {{ menuActive(@$menu->menu_active) }} ">
                                                <a href="{{ route($menu->route_name, $submenuParams) }}" class="nav-link">
                                                    <span class="menu-title">{{ __($menu->title) }}</span>
                                                    @php $counter = @$menu->counter; @endphp
                                                    @if (@$$counter)
                                                        <span class="menu-badge bg--info ms-auto">{{ @$$counter }}</span>
                                                    @endif
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                        @else
                            @php
                                $mainParams = null;
                                if (@$data->params) {
                                    foreach ($data->params as $paramVal) {
                                        $mainParams[] = array_values((array) $paramVal)[0];
                                    }
                                }
                                $href = isset($data->route_name) && $data->route_name
                                    ? route($data->route_name, $mainParams ?? [])
                                    : 'javascript:void(0)';
                            @endphp
                            <li class="sidebar-menu-item {{ menuActive(@$data->menu_active ?? @$data->route_name) }}">
                                <a href="{{ $href }}" class="nav-link">
                                    <i class="{{ @$data->icon }}"></i>
                                    <span class="menu-title">{{ __(@$data->title) }}</span>
                                </a>
                            </li>
                        @endif
                @endforeach
            @endforeach
        </ul>
    </div>
</div>
</div>
<!-- sidebar end -->

@push('style')
<style>
/* ========== SIDEBAR MODERNA ========== */

/* Overlay móvel */
.rr-sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(3, 7, 18, 0.58);
    backdrop-filter: blur(5px);
    z-index: 9998;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.rr-sidebar-overlay.active {
    display: block;
    opacity: 1;
}

/* Sidebar base */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 260px;
    height: 100vh;
    /* leve transparência para ver o conteúdo atrás do menu */
    background:
        linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(7, 10, 18, 0.98)),
        radial-gradient(circle at 50% 0%, rgba(249, 115, 22, 0.17), transparent 34%);
    backdrop-filter: blur(18px);
    border-right: 1px solid rgba(249, 115, 22, 0.16);
    box-shadow: 18px 0 45px rgba(0, 0, 0, 0.28);
    z-index: 9999;
    overflow: hidden;
    transition: transform 0.3s ease, width 0.3s ease;
}

.sidebar__inner {
    display: flex;
    flex-direction: column;
    height: 100%;
}

/* Logo */
.sidebar__logo {
    padding: 1.25rem 1rem;
    border-bottom: 1px solid rgba(249, 115, 22, 0.14);
    background: rgba(249, 115, 22, 0.06);
    display: flex;
    align-items: center;
    justify-content: center;
}
.sidebar__main-logo {
    display: flex;
    align-items: center;
    justify-content: center;
}
.sidebar__main-logo img {
    max-height: 50px;
    width: auto;
    filter: drop-shadow(0 14px 24px rgba(249, 115, 22, 0.3));
}

/* Menu wrapper */
.sidebar__menu-wrapper {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 1rem 0;
    scrollbar-width: thin;
    scrollbar-color: rgba(249, 115, 22, 0.3) transparent;
}
.sidebar__menu-wrapper::-webkit-scrollbar {
    width: 4px;
}
.sidebar__menu-wrapper::-webkit-scrollbar-track {
    background: transparent;
}
.sidebar__menu-wrapper::-webkit-scrollbar-thumb {
    background: rgba(249, 115, 22, 0.3);
    border-radius: 4px;
}

/* Menu */
.sidebar__menu {
    list-style: none !important;
    padding: 0 !important;
    margin: 0 !important;
}

/* Menu header */
.sidebar__menu-header {
    padding: 0.75rem 1.25rem 0.5rem;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #64748b;
}

/* Menu item */
.sidebar-menu-item {
    list-style: none !important;
    margin: 0 !important;
    padding: 0 0.75rem !important;
}
.sidebar-menu-item::before,
.sidebar-menu-item::after {
    display: none !important;
}

.sidebar-menu-item > a,
.sidebar-menu-item .nav-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 0.9rem;
    color: #94a3b8;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s ease;
    font-size: 0.82rem;
    font-weight: 500;
    white-space: nowrap; /* força a linha única */
    overflow: hidden;
    text-overflow: ellipsis;
    position: relative; /* necessário para posicionamento de pseudo-elementos ::before */
}
.sidebar-menu-item > a:hover,
.sidebar-menu-item .nav-link:hover {
    background: rgba(249, 115, 22, 0.12);
    color: #fdba74;
}
.sidebar-menu-item.active > a,
.sidebar-menu-item.active .nav-link {
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.24) 0%, rgba(37, 99, 235, 0.14) 100%);
    color: #fff7ed;
    box-shadow: inset 3px 0 0 rgba(249, 115, 22, 0.95);
    font-weight: 600;
}

/* Ícones do menu */
.sidebar-menu-item i,
.sidebar-menu-item .menu-icon {
    font-size: 1.1rem;
    width: 22px;
    text-align: center;
    flex-shrink: 0;
    display: inline-flex !important;
}
.sidebar-menu-item.active i,
.sidebar-menu-item:hover i {
    color: #fdba74;
}

/* Submenu */
.sidebar-submenu {
    display: none;
    padding-left: 1rem;
}
.sidebar-menu-item > a::after,
.sidebar-menu-item .nav-link::after {
    display: none !important; /* remove quaisquer indicadores decorativos (quadradinhos) */
}
.sidebar-submenu.sidebar-submenu__open,
.sidebar-dropdown.active .sidebar-submenu {
    display: block;
}
.sidebar-submenu ul {
    list-style: none !important;
    padding: 0 !important;
    margin: 0.5rem 0 !important;
}
.sidebar-submenu .sidebar-menu-item {
    padding: 0 !important;
}
.sidebar-submenu .nav-link {
    padding: 0.5rem 1rem !important;
    font-size: 0.85rem !important;
    border-left: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 0 8px 8px 0 !important;
    margin-left: 0.5rem;
}
.sidebar-submenu .sidebar-menu-item.active .nav-link {
    border-left-color: #f97316;
}

/* Botão fechar */
.res-sidebar-close-btn {
    display: none;
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.15);
    border: none;
    color: #ef4444;
    cursor: pointer;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    transition: all 0.2s;
    z-index: 10;
}
.res-sidebar-close-btn:hover {
    background: rgba(239, 68, 68, 0.3);
}

/* ========== MOBILE STYLES ========== */
@media (max-width: 991.98px) {
    .sidebar {
        transform: translateX(-100%);
        width: 280px;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.5);
    }
    .sidebar.open {
        transform: translateX(0);
    }
    .res-sidebar-close-btn {
        display: flex;
    }
}

/* Ocultar badges e decorações desnecessárias */
.sidebar__menu .menu-badge,
.sidebar__menu .menu-badge-level-one {
    display: none !important;
}

/* Remover completamente pseudo-elementos de seta (evita quadradinhos) */
.sidebar-dropdown > a::before,
.sidebar-dropdown .nav-link::before {
    display: none !important;
    content: none !important;
}
</style>
@endpush

@push('script')
<script>
    (function() {
    'use strict';
    
    var sidebar = document.getElementById('adminSidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var closeBtn = document.getElementById('sidebarCloseBtn');
    var menuWrapper = document.getElementById('sidebarMenuWrapper');
    
    // Função para abrir sidebar
    function openSidebar() {
        if (sidebar) {
            sidebar.classList.add('open');
        }
        if (overlay) {
            overlay.classList.add('active');
        }
        document.body.style.overflow = 'hidden';
    }
    
    // Função para fechar sidebar
    function closeSidebar() {
        if (sidebar) {
            sidebar.classList.remove('open');
        }
        if (overlay) {
            overlay.classList.remove('active');
        }
        document.body.style.overflow = '';
    }
    
    // Event listeners para botões de abrir (delegação de eventos)
    document.addEventListener('click', function(e) {
        var openBtn = e.target.closest('.res-sidebar-open-btn');
        if (openBtn) {
            e.preventDefault();
            e.stopPropagation();
            openSidebar();
        }
    });
    
    // Fechar ao clicar no botão X
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeSidebar();
        });
    }
    
    // Fechar ao clicar no overlay
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            e.preventDefault();
            closeSidebar();
        });
    }
    
    // Fechar com ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('open')) {
            closeSidebar();
        }
    });
    
    // Toggle de submenus (delegação: funciona mesmo se o script carregar antes do DOM)
    document.addEventListener('click', function(e) {
        var dropdownLink = e.target.closest('.sidebar-dropdown > a');
        if (!dropdownLink) return;

        e.preventDefault();
        var parent = dropdownLink.parentElement;
        var submenu = parent ? parent.querySelector('.sidebar-submenu') : null;

        if (!submenu) return;

        var isOpen = submenu.classList.contains('sidebar-submenu__open');

        // Fecha outros submenus
        document.querySelectorAll('.sidebar-submenu.sidebar-submenu__open').forEach(function(sm) {
            if (sm !== submenu) {
                sm.classList.remove('sidebar-submenu__open');
                if (sm.parentElement) sm.parentElement.classList.remove('active');
            }
        });

        // Toggle atual
        if (isOpen) {
            submenu.classList.remove('sidebar-submenu__open');
            parent.classList.remove('active');
        } else {
            submenu.classList.add('sidebar-submenu__open');
            parent.classList.add('active');
        }
    });
    
    // Scroll para item ativo
    setTimeout(function() {
        var activeItem = document.querySelector('.sidebar-menu-item.active');
        if (activeItem && menuWrapper) {
            var offset = activeItem.offsetTop - menuWrapper.offsetHeight / 2;
            menuWrapper.scrollTo({ top: offset, behavior: 'smooth' });
        }
    }, 300);
    
    // Touch support - swipe para fechar
    var touchStartX = 0;
    var touchEndX = 0;
    
    if (sidebar) {
        sidebar.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        
        sidebar.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            if (touchStartX - touchEndX > 50) {
                closeSidebar();
            }
        }, { passive: true });
    }
})();
</script>
@endpush

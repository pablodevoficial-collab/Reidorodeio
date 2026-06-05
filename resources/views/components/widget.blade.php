@php
    $bgColors = [
        'primary' => 'bg-primary',
        'success' => 'bg-success',
        'danger' => 'bg-danger',
        'warning' => 'bg-warning',
        'info' => 'bg-info',
        'dark' => 'bg-dark',
        'red' => 'bg-danger',
        'indigo' => 'bg-indigo',
        'cyan' => 'bg-cyan',
        'green' => 'bg-success',
        'teal' => 'bg-teal',
        'deep-purple' => 'bg-purple',
        '1' => 'bg-1',
        '2' => 'bg-2',
        '3' => 'bg-3',
        '4' => 'bg-4',
        '5' => 'bg-5',
        '6' => 'bg-6',
        '7' => 'bg-7',
        '8' => 'bg-8',
        '17' => 'bg-17',
    ];
    $bgClass = $bgColors[$bg] ?? 'bg-primary';
@endphp

@if($style == '6')
    <div class="dashboard-w1 bg--{{ $bg }}">
        <a href="{{ $link }}" class="d-block text-decoration-none">
            <div class="dashboard-w1__icon">
                <i class="{{ $icon }}"></i>
            </div>
            <div class="dashboard-w1__content">
                <h3 class="dashboard-w1__number">{{ $value }}</h3>
                <p class="dashboard-w1__title">{{ $title }}</p>
            </div>
        </a>
    </div>
@elseif($style == '7')
    <div class="card h-100">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-2">{{ $title }}</h6>
                    <h4 class="mb-0">{{ $value }}</h4>
                </div>
                <div class="dashboard-widget__icon {{ $bgClass }}">
                    <i class="{{ $icon }}"></i>
                </div>
            </div>
            @if($link != 'javascript:void(0)' && $link != '#')
                <a href="{{ $link }}" class="btn btn-sm btn--base mt-3 w-100">
                    @lang('View Details')
                </a>
            @endif
        </div>
    </div>
@elseif($style == '2')
    <div class="card h-100 {{ $coverCursor ? 'cursor-pointer' : '' }}">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3">
                <div class="dashboard-widget__icon {{ $bgClass }} {{ $iconStyle }}">
                    <i class="{{ $icon }}"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">{{ $heading ?: $title }}</h6>
                    <p class="mb-0 text-muted small">{{ $subheading }}</p>
                </div>
                @if($link != 'javascript:void(0)' && $link != '#')
                    <a href="{{ $link }}" class="text--base">
                        <i class="las la-angle-right fs-24"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
@else
    {{-- Default widget style --}}
    <div class="card h-100">
        <div class="card-body">
            <div class="text-center">
                <div class="dashboard-widget__icon {{ $bgClass }} mx-auto mb-3">
                    <i class="{{ $icon }}"></i>
                </div>
                <h4 class="mb-2">{{ $value }}</h4>
                <span class="text-muted">{{ $title }}</span>
            </div>
            @if($link != 'javascript:void(0)' && $link != '#' && $viewMoreIcon)
                <a href="{{ $link }}" class="btn btn-sm btn--base mt-3 w-100">
                    @lang('View More')
                </a>
            @endif
        </div>
    </div>
@endif

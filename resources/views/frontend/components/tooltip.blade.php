<div x-data="{ tooltipOpen: false }"
     class="relative inline-block"
     @mouseenter="tooltipOpen = true"
     @mouseleave="tooltipOpen = false">

    <!-- Trigger Element -->
    <div class="inline-block cursor-help">
        {{ $slot }}
    </div>

    <!-- Tooltip -->
    <div x-show="tooltipOpen"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 z-50">

        <div class="bg-rr-accent text-white px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap shadow-lg">
            {{ $text ?? 'Tooltip' }}
            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-rr-accent"></div>
        </div>
    </div>
</div>

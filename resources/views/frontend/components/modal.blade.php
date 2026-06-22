<div x-data="{ open: @js($open ?? false) }"
     @keydown.escape="open = false"
     class="relative">

    <!-- Trigger Button -->
    <button @click="open = true"
            class="rr-btn rr-btn--primary hover-lift">
        {{ $trigger ?? 'Abrir Modal' }}
    </button>

    <!-- Modal Backdrop -->
    <div x-show="open"
         @click="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40"></div>

    <!-- Modal Content -->
    <div x-show="open"
         @click.stop
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90"
         class="fixed inset-0 flex items-center justify-center z-50">

        <div class="rr-panel w-full max-w-md mx-4 shadow-2xl animate-scale-in">
            <!-- Header -->
            <div class="flex justify-between items-center p-6 border-b border-rr-card-border">
                <h2 class="text-xl font-bold text-rr-text">{{ $title ?? 'Modal' }}</h2>
                <button @click="open = false"
                        class="text-rr-text-soft hover:text-rr-text transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6">
                {{ $slot }}
            </div>

            <!-- Footer -->
            @isset($footer)
                <div class="flex gap-3 p-6 border-t border-rr-card-border">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>

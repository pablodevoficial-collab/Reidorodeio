<div class="rr-interactive-card"
     x-data="{ flipped: false }"
     @click="flipped = !flipped"
     :class="{ 'is-flipped': flipped }"
     class="flip"
     style="cursor: pointer; perspective: 1000px;">

    <!-- Front -->
    <div class="front-face" x-show="!flipped" class="glass-card p-8 hover-glow transition-smooth">
        {{ $slot }}
    </div>

    <!-- Back -->
    <div class="back-face" x-show="flipped" class="glass-card p-8 hover-glow transition-smooth" style="transform: rotateY(180deg);">
        <div class="text-center">
            <p class="text-rr-text-soft mb-4">{{ $backContent ?? 'Clique para voltar' }}</p>
            <p class="text-sm text-rr-accent font-bold">← Clique aqui</p>
        </div>
    </div>
</div>

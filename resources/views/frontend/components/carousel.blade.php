<div x-data="carousel()"
     @keydown.left="prev()"
     @keydown.right="next()"
     class="relative w-full">

    <!-- Slides -->
    <div class="relative overflow-hidden rounded-2xl bg-black">
        <div class="flex transition-transform duration-500 ease-out"
             :style="`transform: translateX(-${current * 100}%)`">
            {{ $slot }}
        </div>
    </div>

    <!-- Navigation Dots -->
    <div class="flex justify-center gap-3 mt-6">
        <template x-for="(item, index) in items" :key="index">
            <button @click="current = index"
                    :class="current === index ? 'bg-rr-accent' : 'bg-rr-text-soft'"
                    class="w-3 h-3 rounded-full transition-all duration-300 hover:scale-125"></button>
        </template>
    </div>

    <!-- Arrow Navigation -->
    <button @click="prev()"
            class="absolute left-4 top-1/2 -translate-y-1/2 bg-rr-accent hover:bg-rr-accent-strong text-white p-3 rounded-full transition-all hover-lift z-10">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button @click="next()"
            class="absolute right-4 top-1/2 -translate-y-1/2 bg-rr-accent hover:bg-rr-accent-strong text-white p-3 rounded-full transition-all hover-lift z-10">
        <i class="fas fa-chevron-right"></i>
    </button>
</div>

<script>
function carousel() {
    return {
        current: 0,
        items: [],

        init() {
            this.items = Array.from(this.$el.querySelectorAll('[data-carousel-item]'));
        },

        next() {
            this.current = (this.current + 1) % this.items.length;
        },

        prev() {
            this.current = (this.current - 1 + this.items.length) % this.items.length;
        }
    }
}
</script>

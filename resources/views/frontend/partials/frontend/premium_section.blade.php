<!-- Premium Features Showcase Section -->
<section class="py-12 px-4" data-aos="fade-up">
    <div class="rr-site-shell">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-4xl font-extrabold text-gradient-accent mb-4">
                ✨ Novas Features Premium ✨
            </h2>
            <p class="text-rr-text-soft text-lg">Interatividade e animações que deixam seu projeto único</p>
        </div>

        <!-- Grid of showcases -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card 1: Interactive Card -->
            <div class="rr-panel p-8 hover-lift" data-aos="flip-left" data-aos-duration="800">
                <div class="flex items-center justify-center h-24 mb-6">
                    <i class="fas fa-mouse text-4xl text-rr-accent animate-pulse-soft"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Cards Interativos</h3>
                <p class="text-rr-text-soft text-sm mb-4">Clique para virar e ver mais informações com animações suaves</p>
                <span class="text-xs bg-rr-accent/20 text-rr-accent px-3 py-1 rounded-full">Alpine.js</span>
            </div>

            <!-- Card 2: Smooth Animations -->
            <div class="rr-panel p-8 hover-lift" data-aos="flip-left" data-aos-duration="800" data-aos-delay="100">
                <div class="flex items-center justify-center h-24 mb-6">
                    <i class="fas fa-wand-magic-sparkles text-4xl text-rr-accent animate-pulse-soft"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Animações GSAP</h3>
                <p class="text-rr-text-soft text-sm mb-4">Efeitos profissionais de entrada, saída e interação em tempo real</p>
                <span class="text-xs bg-rr-accent/20 text-rr-accent px-3 py-1 rounded-full">GSAP</span>
            </div>

            <!-- Card 3: Scroll Effects -->
            <div class="rr-panel p-8 hover-lift" data-aos="flip-left" data-aos-duration="800" data-aos-delay="200">
                <div class="flex items-center justify-center h-24 mb-6">
                    <i class="fas fa-scroll text-4xl text-rr-accent animate-pulse-soft"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Scroll Animations</h3>
                <p class="text-rr-text-soft text-sm mb-4">Elementos aparecem com estilo conforme você desce a página</p>
                <span class="text-xs bg-rr-accent/20 text-rr-accent px-3 py-1 rounded-full">AOS</span>
            </div>
        </div>

        <!-- Advanced Examples Row -->
        <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Glass Morphism Card -->
            <div class="glass-hard p-8 rounded-2xl backdrop-blur-xl" data-aos="fade-up" data-aos-delay="300">
                <div class="flex items-center gap-4 mb-4">
                    <i class="fas fa-gem text-2xl text-rr-accent"></i>
                    <h3 class="text-2xl font-bold">Glass Morphism</h3>
                </div>
                <p class="text-rr-text-soft mb-6">Design moderno com efeitos de vidro fosco e desfoque</p>
                <div class="flex gap-3">
                    <button class="rr-btn rr-btn--primary btn-animate hover-lift">
                        Ação Principal
                    </button>
                    <button class="rr-btn rr-btn--secondary btn-animate hover-lift">
                        Ação Secundária
                    </button>
                </div>
            </div>

            <!-- Gradient Text Card -->
            <div class="p-8 rounded-2xl border border-rr-card-border" data-aos="fade-up" data-aos-delay="400">
                <h3 class="text-4xl font-extrabold text-gradient-accent mb-4">
                    Gradientes Premium
                </h3>
                <p class="text-rr-text-soft mb-6">
                    Combine cores com efeitos de gradiente para criar contraste visual impactante
                </p>
                <div class="flex gap-2 flex-wrap">
                    <span class="px-4 py-2 bg-gradient-accent text-white rounded-full text-sm font-bold">
                        accent
                    </span>
                    <span class="px-4 py-2 bg-gradient-success text-white rounded-full text-sm font-bold">
                        success
                    </span>
                    <span class="px-4 py-2 bg-gradient-cool text-white rounded-full text-sm font-bold">
                        cool
                    </span>
                </div>
            </div>
        </div>

        <!-- Stats Section with Counter Animation -->
        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6" data-aos="fade-up">
            <div class="text-center p-8 rr-panel hover-lift" x-data="{ count: 0 }"
                 @intersect="GsapAnimations.countUp(document.querySelector('[data-count-1]'), 0, 99999, 2)">
                <p data-count-1 class="text-4xl font-extrabold text-rr-accent mb-2">0</p>
                <p class="text-rr-text-soft">Linhas de Código</p>
            </div>

            <div class="text-center p-8 rr-panel hover-lift"
                 @intersect="GsapAnimations.countUp(document.querySelector('[data-count-2]'), 0, 100, 2)">
                <p data-count-2 class="text-4xl font-extrabold text-rr-accent mb-2">0</p>
                <p class="text-rr-text-soft">% Otimização</p>
            </div>

            <div class="text-center p-8 rr-panel hover-lift"
                 @intersect="GsapAnimations.countUp(document.querySelector('[data-count-3]'), 0, 50, 2)">
                <p data-count-3 class="text-4xl font-extrabold text-rr-accent mb-2">0</p>
                <p class="text-rr-text-soft">Componentes Novos</p>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="mt-12 text-center" data-aos="zoom-in">
            <h3 class="text-3xl font-extrabold text-white mb-6">
                Pronto para explorar o novo front?
            </h3>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#" class="rr-btn rr-btn--primary btn-animate hover-lift min-w-48">
                    <i class="fas fa-rocket"></i> Começar Agora
                </a>
                <a href="#" class="rr-btn rr-btn--secondary btn-animate hover-lift min-w-48">
                    <i class="fas fa-book"></i> Documentação
                </a>
            </div>
        </div>
    </div>
</section>

<style>
    /* Add margin and padding to section */
    section.py-12 {
        padding: 48px 0;
    }

    /* Enhance stats counters */
    [data-count-1], [data-count-2], [data-count-3] {
        display: block;
        font-variant-numeric: tabular-nums;
    }

    /* Glass card enhanced styles */
    .glass-hard {
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.12);
    }

    .glass-hard:hover {
        background: rgba(15, 23, 42, 0.8);
        border-color: rgba(249, 115, 22, 0.3);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Min width utility for responsive buttons */
    .min-w-48 {
        min-width: 12rem;
    }

    @media (max-width: 767px) {
        .min-w-48 {
            min-width: auto;
            width: 100%;
        }
    }
</style>

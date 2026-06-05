// CARD PARTICLES ANIMATION SCRIPT

(function() {
  'use strict';

  // Cores por nível
  const nivelColors = {
    favorito: { r: 255, g: 215, b: 0 },      // Dourado
    elite: { r: 220, g: 20, b: 60 },         // Vermelho
    legado: { r: 192, g: 192, b: 192 },      // Prata
    presilha: { r: 34, g: 139, b: 34 },      // Verde
    desconhecido: { r: 249, g: 115, b: 22 }  // Laranja (fallback)
  };

  // Classe Partícula
  class Particle {
    constructor(canvas, color) {
      this.canvas = canvas;
      this.color = color;
      this.x = Math.random() * canvas.width;
      this.y = Math.random() * canvas.height;
      this.size = Math.random() * 1.5 + 0.3;
      this.speedX = (Math.random() - 0.5) * 0.3;
      this.speedY = (Math.random() - 0.5) * 0.3;
      this.opacity = Math.random() * 0.4 + 0.1;
    }

    update() {
      this.x += this.speedX;
      this.y += this.speedY;

      if (this.x < 0 || this.x > this.canvas.width) this.speedX *= -1;
      if (this.y < 0 || this.y > this.canvas.height) this.speedY *= -1;
    }

    draw(ctx) {
      ctx.fillStyle = `rgba(${this.color.r}, ${this.color.g}, ${this.color.b}, ${this.opacity})`;
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
      ctx.fill();
    }
  }

  // Classe Animador de Card
  class CardParticleAnimator {
    constructor(card) {
      this.card = card;
      this.canvas = card.querySelector('.card-holo__particles');
      if (!this.canvas) return;

      this.ctx = this.canvas.getContext('2d');
      this.particles = [];
      this.animationId = null;
      this.isAnimating = false;

      // Obter cor baseada no nível
      const nivel = card.dataset.nivel || 'desconhecido';
      this.color = nivelColors[nivel] || nivelColors.desconhecido;

      this.init();
      this.setupObserver();
    }

    init() {
      this.resizeCanvas();
      window.addEventListener('resize', () => this.resizeCanvas());
      this.initParticles();
    }

    resizeCanvas() {
      const rect = this.card.getBoundingClientRect();
      this.canvas.width = rect.width;
      this.canvas.height = rect.height;
    }

    initParticles() {
      this.particles = [];
      // Número de partículas baseado no tamanho do card
      const particleCount = Math.floor((this.canvas.width * this.canvas.height) / 8000);
      const count = Math.min(25, Math.max(8, particleCount));
      
      for (let i = 0; i < count; i++) {
        this.particles.push(new Particle(this.canvas, this.color));
      }
    }

    animate() {
      if (!this.isAnimating) return;

      this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

      // Atualizar e desenhar partículas
      this.particles.forEach(particle => {
        particle.update();
        particle.draw(this.ctx);
      });

      // Conectar partículas próximas
      for (let i = 0; i < this.particles.length; i++) {
        for (let j = i + 1; j < this.particles.length; j++) {
          const dx = this.particles[i].x - this.particles[j].x;
          const dy = this.particles[i].y - this.particles[j].y;
          const distance = Math.sqrt(dx * dx + dy * dy);

          if (distance < 80) {
            const opacity = 0.1 * (1 - distance / 80);
            this.ctx.strokeStyle = `rgba(${this.color.r}, ${this.color.g}, ${this.color.b}, ${opacity})`;
            this.ctx.lineWidth = 0.5;
            this.ctx.beginPath();
            this.ctx.moveTo(this.particles[i].x, this.particles[i].y);
            this.ctx.lineTo(this.particles[j].x, this.particles[j].y);
            this.ctx.stroke();
          }
        }
      }

      this.animationId = requestAnimationFrame(() => this.animate());
    }

    start() {
      if (!this.isAnimating) {
        this.isAnimating = true;
        this.animate();
      }
    }

    stop() {
      this.isAnimating = false;
      if (this.animationId) {
        cancelAnimationFrame(this.animationId);
        this.animationId = null;
      }
    }

    setupObserver() {
      // Usar Intersection Observer para animar apenas cards visíveis
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            this.start();
          } else {
            this.stop();
          }
        });
      }, {
        threshold: 0.1,
        rootMargin: '50px'
      });

      observer.observe(this.card);
    }
  }

  // Inicializar partículas em todos os cards
  function initCardParticles() {
    const cards = document.querySelectorAll('.card-holo');
    cards.forEach(card => {
      if (!card.dataset.particlesInitialized) {
        new CardParticleAnimator(card);
        card.dataset.particlesInitialized = 'true';
      }
    });
  }

  // Inicializar quando DOM estiver pronto
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCardParticles);
  } else {
    initCardParticles();
  }

  // Re-inicializar quando novos cards são adicionados
  if (typeof MutationObserver !== 'undefined') {
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.addedNodes.length) {
          mutation.addedNodes.forEach((node) => {
            if (
              node.nodeType === 1 &&
              (node.classList?.contains('rr-card-item') ||
                node.querySelector?.('.rr-card-item'))
            ) {
              setTimeout(initCardParticles, 100);
            }
          });
        }
      });
    });

    const target = document.querySelector('.rr-cards-list');
    if (target) {
      observer.observe(target, { childList: true, subtree: true });
    }
  }
})();

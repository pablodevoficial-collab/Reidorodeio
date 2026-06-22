// PREMIUM CARDS - TODAS ANIMAÇÕES DESABILITADAS

(function() {
  'use strict';

  // Inicializar cards (mantém apenas clique no mobile)
  function initPremiumCards() {
    const cards = document.querySelectorAll('.premium-card');
    cards.forEach(card => {
      if (!card.dataset.initialized) {
        card.dataset.initialized = 'true';
        
        // Tornar card clicável no mobile
        if (window.innerWidth <= 767) {
          card.addEventListener('click', function(e) {
            const btn = this.querySelector('.verTodasBtn');
            if (btn && e.target !== btn) {
              btn.click();
            }
          });
        }
      }
    });
  }

  // Inicializar quando DOM estiver pronto
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPremiumCards);
  } else {
    initPremiumCards();
  }

  // Observer para novos cards adicionados dinamicamente
  if (typeof MutationObserver !== 'undefined') {
    const observer = new MutationObserver(() => {
      initPremiumCards();
    });

    const target = document.querySelector('.rr-cards-list');
    if (target) {
      observer.observe(target, { childList: true, subtree: true });
    }
  }
})();

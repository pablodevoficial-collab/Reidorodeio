/**
 * ============================================
 * FANTASY LEAGUES - HUB JAVASCRIPT
 * ============================================
 * Extraído de inicial_fantasy_content.blade.php
 * Refatorado em: 2026-01-24
 */

(function() {
  'use strict';
  
  // ============================================
  // CAMPO DE PESQUISA FANTASY (ESTILO X1)
  // ============================================
  const searchForm = document.getElementById('rrFantasySearchForm');
  const searchInput = document.getElementById('rrFantasySearchInput');
  const searchIcon = document.getElementById('rrFantasySearchIcon');
  const leaguesGrid = document.getElementById('rrFantasyLeaguesGrid');
  
  if (!searchForm || !searchInput || !searchIcon || !leaguesGrid) return;
  
  let isOpen = false;
  
  // Toggle search field
  function toggleSearch() {
    isOpen = !isOpen;
    if (isOpen) {
      searchForm.classList.add('is-open');
      setTimeout(() => searchInput.focus(), 350);
    } else {
      searchForm.classList.remove('is-open');
      searchInput.value = '';
      searchInput.blur();
      filterLeagues('');
    }
  }
  
  // Filter leagues by name
  function filterLeagues(query) {
    const cards = leaguesGrid.querySelectorAll('.rr-league-card');
    const normalizedQuery = query.toLowerCase().trim();
    let visibleCount = 0;
    
    cards.forEach(card => {
      const title = card.querySelector('.rr-league-card__title, .rr-fantasy-premium-card__title');
      if (!title) return;
      
      const text = title.textContent.toLowerCase();
      const matches = !normalizedQuery || text.includes(normalizedQuery);
      
      card.style.display = matches ? '' : 'none';
      if (matches) visibleCount++;
    });
    
    // Show empty message if no results
    const emptyMsg = document.getElementById('rrFantasyLeaguesEmpty');
    if (emptyMsg) {
      if (visibleCount === 0 && normalizedQuery) {
        emptyMsg.textContent = 'Nenhuma liga encontrada.';
        emptyMsg.style.display = 'block';
      } else if (visibleCount === 0 && !normalizedQuery) {
        emptyMsg.textContent = 'Nenhuma liga disponível agora.';
        emptyMsg.style.display = 'block';
      } else {
        emptyMsg.style.display = 'none';
      }
    }
  }
  
  // Event listeners
  searchIcon.addEventListener('click', toggleSearch);
  
  searchInput.addEventListener('input', (e) => {
    filterLeagues(e.target.value);
  });
  
  searchInput.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      toggleSearch();
    }
  });
  
  // Close on click outside
  document.addEventListener('click', (e) => {
    if (isOpen && !searchForm.contains(e.target)) {
      toggleSearch();
    }
  });
})();

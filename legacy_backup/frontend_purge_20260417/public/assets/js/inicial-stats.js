// =================================================================
// INICIAL STATS - JAVASCRIPT PARA PÁGINA DE ESTATÍSTICAS
// =================================================================

const COMPETITOR_MODAL_Z_INDEX = '2147483647';
const COMPETITOR_MODAL_WRAPPER_Z_INDEX = '2147483647';

// Aguarda DOM e dados do servidor
document.addEventListener('DOMContentLoaded', function() {
  initStatsPage();
  initMainTabs();
  initEventNavigation();
  initPaginationAndFilters();
  initVerMaisButtons();
});

// ========== INICIALIZAÇÃO GERAL ==========
function initStatsPage() {
}

// ========== BUSCA COM ABERTURA/FECHAMENTO (PADRÃO X1) ==========
function initSearchBar() {
  const searchInput = document.getElementById('rrStatsSearchInput');
  const searchForm = document.getElementById('rrStatsSearchForm');
  const searchIcon = document.getElementById('rrStatsSearchIcon');
  const cardsGrid = document.querySelector('.rr-cards-list');
  
  if (!searchInput || !searchForm || !searchIcon) {
    return;
  }
  
  let isSearchOpen = false;
  let searchTerm = '';
  
  // Abrir busca
  function openSearch() {
    isSearchOpen = true;
    searchForm.classList.add('is-open');
    setTimeout(() => searchInput.focus(), 300);
  }
  
  // Fechar busca
  function closeSearch() {
    isSearchOpen = false;
    searchInput.value = '';
    searchTerm = '';
    searchInput.blur();
    searchForm.classList.remove('is-open');
    applySearch();
  }
  
  // Alternar ao clicar no ícone
  searchIcon.addEventListener('click', function(e) {
    e.stopPropagation();
    if (isSearchOpen) {
      closeSearch();
    } else {
      openSearch();
    }
  });
  
  // Clicar no form fechado abre
  searchForm.addEventListener('click', function(e) {
    if (!isSearchOpen && !searchIcon.contains(e.target)) {
      openSearch();
    }
  });
  
  // Aplicar filtro
  function applySearch() {
    const allCards = document.querySelectorAll('.rr-stats-item, .rr-card-item');
    const normalizedTerm = searchTerm.toLowerCase();
    
    allCards.forEach(card => {
      const name = (card.querySelector('.rr-stats-card__name')?.textContent || card.querySelector('.premium-card__name')?.textContent || '').toLowerCase();
      if (!searchTerm || name.includes(normalizedTerm)) {
        card.style.display = '';
      } else {
        card.style.display = 'none';
      }
    });
  }
  
  // Input change
  searchInput.addEventListener('input', function() {
    searchTerm = this.value.trim();
    applySearch();
  });
  
  // ESC fecha
  searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeSearch();
    }
  });
  
}

// ========== FUNÇÃO GLOBAL PARA ABRIR MODAL ==========
window.abrirModalStats = function(btn) {
  const id = btn.dataset.id;
  const nome = btn.dataset.nome;
  const foto = btn.dataset.foto;
  const isPremium = parseInt(btn.dataset.premium) === 1;

  try {
    openCompetitorModalV2(id, nome, foto, isPremium, btn.dataset);
  } catch (error) {
    alert('ERRO ao abrir modal: ' + error.message);
  }
};

// ========== BOTÃO VER MAIS E MODAL - FALLBACK NO CARD ==========
function initVerMaisButtons() {
}

const RR_COMPETITOR_DETAIL_FIELDS = [
  { field: 'count_dobrada', label: 'Dobrada', tone: 'positive' },
  { field: 'count_duas_voltas', label: 'Duas Voltas', tone: 'positive' },
  { field: 'count_cola', label: 'Cola', tone: 'positive' },
  { field: 'count_cupim', label: 'Cupim', tone: 'positive' },
  { field: 'count_pescou', label: 'Pescou', tone: 'positive' },
  { field: 'count_por_cima', label: 'Por Cima', tone: 'positive' },
  { field: 'count_limpou_garupa', label: 'Limpou Garupa', tone: 'positive' },
  { field: 'count_limpou_cupim_longe', label: 'Limpou Cupim', tone: 'positive' },
  { field: 'count_limpou_top', label: 'Limpou Top', tone: 'positive' },
  { field: 'count_limpou_top_mao', label: 'Limpou Top com a Mão', tone: 'positive' },
  { field: 'count_top', label: 'Top', tone: 'positive' },
  { field: 'count_uma_aspa', label: 'Uma Aspa', tone: 'positive' },
  { field: 'count_cabresteou', label: 'Cabresteou', tone: 'negative' },
  { field: 'count_errou_pescoco', label: 'Errou Pescoço', tone: 'negative' },
  { field: 'count_errou_pata', label: 'Errou Pata', tone: 'negative' },
  { field: 'count_errou_top', label: 'Errou Top', tone: 'negative' },
  { field: 'count_boi_tirou', label: 'Boi Tirou', tone: 'negative' },
  { field: 'count_boi_pulou', label: 'Boi Pulou', tone: 'negative' },
  { field: 'count_queimou_raia', label: 'Queimou a Raia', tone: 'negative' },
  { field: 'count_caiu_do_cavalo', label: 'Caiu do Cavalo', tone: 'negative' },
  { field: 'count_saiu_enrolado', label: 'Saiu Enrolado', tone: 'negative' },
];

function getCompetitorModalState() {
  if (!window.__rrCompetitorModalState) {
    window.__rrCompetitorModalState = {
      competitorId: null,
      competitorName: '',
      competitorPhoto: '',
      competitorLevel: 'competidor',
      claimed: false,
      isPremium: false,
      globalStats: null,
      contexts: [],
      selected: {
        eventId: 'global',
        modalidadeId: '',
        divisao: '',
      },
      activeContext: null,
      compareTarget: null,
      compareData: null,
      compareCandidates: [],
      compareSelectorOpen: false,
      compareLoading: false,
      compareSearchTerm: '',
      currentTab: 'stats',
      followersCount: 0,
      isFollowing: false,
      canFollow: false,
      followLoading: false,
      recentEvents: [],
    };
  }

  return window.__rrCompetitorModalState;
}

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function normalizeCompetitorLevel(level) {
  const normalized = String(level || '').trim().toLowerCase();
  if (normalized === 'legado') return 'ascendente';
  if (normalized === 'presilha') return 'competidor';
  return normalized || 'competidor';
}

function getCompetitorLevelTheme(level) {
  const normalized = normalizeCompetitorLevel(level);
  if (normalized === 'favorito') return { key: normalized, label: 'Favorito', color: '#facc15' };
  if (normalized === 'elite') return { key: normalized, label: 'Elite', color: '#f97316' };
  if (normalized === 'ascendente') return { key: normalized, label: 'Ascendente', color: '#60a5fa' };
  return { key: 'competidor', label: 'Competidor', color: '#22c55e' };
}

function readStatValue(source, field) {
  if (!source) return 0;

  const candidates = [
    source[field],
    source[field.replace(/_/g, '-')],
    source[field.replace(/-([a-z])/g, (_, letter) => letter.toUpperCase())],
  ];

  for (const candidate of candidates) {
    const numeric = Number(candidate);
    if (Number.isFinite(numeric)) {
      return numeric;
    }
  }

  return 0;
}

function buildStatsRecord(source) {
  const boas = readStatValue(source, 'count_boa') || readStatValue(source, 'boas');
  const errors = readStatValue(source, 'count_negativas_total') || readStatValue(source, 'errors') || readStatValue(source, 'erros');
  const attempts = boas + errors;
  let aproveitamento = Number(source?.aproveitamento ?? 0);
  if (!Number.isFinite(aproveitamento)) aproveitamento = 0;
  if (attempts > 0) {
    aproveitamento = Math.round((boas / attempts) * 1000) / 10;
  }

  const details = RR_COMPETITOR_DETAIL_FIELDS.map((meta) => ({
    field: meta.field,
    label: meta.label,
    tone: meta.tone,
    value: readStatValue(source, meta.field),
  }));

  const destrezasFromDetails = details.reduce((sum, item) => {
    if ([
      'count_limpou_garupa',
      'count_cola',
      'count_cupim',
      'count_top',
      'count_pescou',
      'count_limpou_cupim_longe',
      'count_pescou_uma_aspa',
      'count_limpou_top',
      'count_limpou_top_mao',
    ].includes(item.field)) {
      return sum + item.value;
    }
    return sum;
  }, 0);

  const destrezas = Number(source?.destrezas);

  return {
    aproveitamento,
    boas,
    errors,
    attempts,
    armadasLabel: attempts > 0 ? `${boas}/${attempts}` : '0/0',
    destrezas: Number.isFinite(destrezas) ? destrezas : destrezasFromDetails,
    details,
  };
}

function getGlobalContextLabel() {
  return 'Resumo geral de estatísticas';
}

// ========== MODAL DO COMPETIDOR (EPIC REFACTOR) ==========
function openCompetitorModal(competitorId, nome, foto, isPremium, allData) {
  let modal = ensureCompetitorModal();
  if (!modal) return;

  // 1. Preencher Header
  const photoEl = document.getElementById('epicModalPhoto');
  const nameEl = document.getElementById('epicModalName');
  const levelEl = document.getElementById('epicModalLevel');
  const levelBadge = document.getElementById('epicModalLevelBadge');
  const backlightEl = document.getElementById('epicModalBacklight');
  
  if (photoEl) {
      photoEl.src = foto || '/assets/images/logo_icon/favicon.png';
      photoEl.onerror = function() { this.src = '/assets/images/logo_icon/favicon.png'; };
  }
  if (nameEl) nameEl.textContent = nome;
  if (levelEl) levelEl.textContent = (allData.nivel || 'COMPETIDOR').toUpperCase();

  // Colorir o badge e o GLOW de acordo com o nível
  const nivel = (allData.nivel || '').toLowerCase();
  let colorTheme = '#22c55e'; // Default Green (was Orange)
  
  if (nivel.includes('favorito')) colorTheme = '#ffd700';
  else if (nivel.includes('elite')) colorTheme = '#ef4444';
  else if (nivel.includes('ascendente') || nivel.includes('legado')) colorTheme = '#c0c0c0'; // Silver
  else if (nivel.includes('competidor') || nivel.includes('presilha')) colorTheme = '#22c55e';
  
  // Apply colors
  if (levelBadge) levelBadge.style.color = colorTheme;
  if (backlightEl) {
      // Sombra começando 100% do topo da página
      backlightEl.style.background = `radial-gradient(ellipse at center top, ${colorTheme} 0%, transparent 60%)`;
      backlightEl.style.boxShadow = 'none';
      backlightEl.style.opacity = '0.6';
  }
  
  const photoCircle = document.getElementById('epicModalPhotoCircle');
  if (photoCircle) {
      // Set CSS variables for the animation to pick up
      photoCircle.style.setProperty('--pick-color', colorTheme);
      
      // Fallback/Initial styles
      photoCircle.style.borderColor = colorTheme;
  }

  // 2. Stats Calculation (Recalcular aproveitamento para garantir precisão)
  let boasVal = parseInt(allData.countBoa || allData['count-boa'] || 0);
  let errosVal = parseInt(allData.erros || allData['count-negativas-total'] || allData.countNegativasTotal || 0);
  if (isNaN(boasVal)) boasVal = 0;
  if (isNaN(errosVal)) errosVal = 0;
  
  const totalArmadasVal = boasVal + errosVal;
  const aproveitamentoCalc = totalArmadasVal > 0 ? Math.round((boasVal / totalArmadasVal) * 100) : 0;

  // Key Stats (Header)
  updateText('epicKeyAproveitamento', (allData.aproveitamento || '0') + '%');
  updateText('epicKeyAproveitamento', aproveitamentoCalc + '%');

  // 3. Stats Grid (Todas)
  const destrezas = allData.destrezas || '0';
  
  updateText('epicStatBoas', boasVal);
  updateText('epicStatErros', errosVal);
  // Atualiza Armadas com cálculo local (Ex: 6/7)
  updateText('epicStatArmadas', `${boasVal}/${totalArmadasVal}`);
  updateText('epicStatDestrezas', destrezas);
  
  // Detalhamento (Todos os campos)
  updateText('epicStatDobrada', allData['count-dobrada'] || allData.countDobrada || '0');
  updateText('epicStatCabresteou', allData['count-cabresteou'] || allData.countCabresteou || '0');
  updateText('epicStatDuasVoltas', allData['count-duas-voltas'] || allData.countDuasVoltas || '0');
  updateText('epicStatCola', allData['count-cola'] || allData.countCola || '0');
  updateText('epicStatCupim', allData['count-cupim'] || allData.countCupim || '0');
  updateText('epicStatPescou', allData['count-pescou'] || allData.countPescou || '0');
  updateText('epicStatPorCima', allData['count-por-cima'] || allData.countPorCima || '0');
  updateText('epicStatLimpouGarupa', allData['count-limpou-garupa'] || allData.countLimpouGarupa || '0');
  updateText('epicStatLimpouCupimLonge', allData['count-limpou-cupim-longe'] || allData.countLimpouCupimLonge || '0');
  updateText('epicStatLimpouTop', allData['count-limpou-top'] || allData.countLimpouTop || '0');
  updateText('epicStatLimpouTopMao', allData['count-limpou-top-mao'] || allData.countLimpouTopMao || '0');
  updateText('epicStatTop', allData['count-top'] || allData.countTop || '0');
  updateText('epicStatUmaAspa', allData['count-uma-aspa'] || allData.countUmaAspa || '0');
  
  updateText('epicStatErrouPescoco', allData['count-errou-pescoco'] || allData.countErrouPescoco || '0');
  updateText('epicStatErrouPata', allData['count-errou-pata'] || allData.countErrouPata || '0');
  updateText('epicStatErrouTop', allData['count-errou-top'] || allData.countErrouTop || '0');
  updateText('epicStatBoiTirou', allData['count-boi-tirou'] || allData.countBoiTirou || '0');
  updateText('epicStatBoiPulou', allData['count-boi-pulou'] || allData.countBoiPulou || '0');
  updateText('epicStatQueimouRaia', allData['count-queimou-raia'] || allData.countQueimouRaia || '0');
  updateText('epicStatCaiuDoCavalo', allData['count-caiu-do-cavalo'] || allData.countCaiuDoCavalo || '0');
  updateText('epicStatSaiuEnrolado', allData['count-saiu-enrolado'] || allData.countSaiuEnrolado || '0');

  // 4. Premium Logic
  const contentSection = document.getElementById('epicModalContent');
  const lockedOverlay = document.getElementById('epicModalLocked');
  
  if (isPremium) {
      if (lockedOverlay) lockedOverlay.style.display = 'none';
  } else {
      if (lockedOverlay) lockedOverlay.style.display = 'flex';
  }

  // 5. Render Events
  renderCompetitorEvents(competitorId);

  // 6. Reivindicar Perfil - WhatsApp link dinâmico
  const claimWrap = document.getElementById('epicClaimProfileWrap');
  const claimBtn = document.getElementById('epicClaimProfileBtn');
  const isClaimed = parseInt(allData.claimed) === 1;
  if (claimWrap) {
      claimWrap.style.display = isClaimed ? 'none' : 'block';
  }
  if (claimBtn && !isClaimed) {
      const msg = encodeURIComponent('Olá! Eu sou o competidor ' + nome + '. Quero reivindicar meu perfil no Rei do Rodeio.');
      claimBtn.href = 'https://wa.me/5547997953323?text=' + msg;
  }

  // 7. Reset Tabs to "Todas"
  switchEpicTab('todas');

  // 7. Show Modal (Fullscreen overlay)
  document.body.appendChild(modal);
  modal.style.zIndex = COMPETITOR_MODAL_Z_INDEX;
  const modalWrapper = modal.querySelector('.x1-arena-modal__wrapper');
  if (modalWrapper) {
      modalWrapper.style.zIndex = COMPETITOR_MODAL_WRAPPER_Z_INDEX;
  }
  modal.style.display = 'flex';
  modal.scrollTop = 0; // Scroll para o topo
  // Force reflow
  modal.offsetHeight; 
  modal.classList.add('is-open');
  modal.setAttribute('aria-hidden', 'false');
  document.body.classList.add('modal-open');
  document.body.style.overflow = 'hidden';
  
  // Partículas
  if (typeof startModalParticles === 'function') {
      const nivel = allData.nivel || 'desconhecido';
      startModalParticles(nivel);
  }
}

function renderCompetitorEvents(competitorId) {
    const listEl = document.getElementById('epicEventsList');
    if (!listEl) return;
    
    listEl.innerHTML = '';
    
    // Obter dados globais
    const allEvents = window.rrEventStatsByCompetitor || {};
    const eventLookup = window.rrEventLookup || {};
    
    const compEvents = allEvents[competitorId] || [];
    
    if (compEvents.length === 0) {
        listEl.innerHTML = '<div class="text-center text-muted py-4">Nenhum evento registrado.</div>';
        return;
    }
    
    // Agrupar por Rodeio
    const byRodeio = {};
    compEvents.forEach(evt => {
        const rId = evt.rodeio_id;
        if (!byRodeio[rId]) {
            byRodeio[rId] = {
                id: rId,
                name: eventLookup[rId] || 'Rodeio #' + rId,
                participacoes: 0,
                modalidades: new Set()
            };
        }
        byRodeio[rId].participacoes++;
        byRodeio[rId].modalidades.add(evt.modalidade_id || 0);
    });
    
    // Renderizar lista sem expor pontuacao ao frontend
    Object.values(byRodeio)
        .sort((a, b) => {
            if ((b.participacoes || 0) !== (a.participacoes || 0)) {
                return (b.participacoes || 0) - (a.participacoes || 0);
            }
            return String(a.name || '').localeCompare(String(b.name || ''), 'pt-BR');
        })
        .forEach((evt, index) => {
            const modalidadesCount = evt.modalidades instanceof Set ? evt.modalidades.size : 0;
            const html = `
                <div class="rr-stats-event-card">
                    <div class="rr-stats-event-card__left">
                        <span class="rr-stats-event-card__rank">#${index + 1}</span>
                        <div class="rr-stats-event-card__meta">
                            <h4>${evt.name}</h4>
                            <p>${evt.participacoes} participacao(oes)</p>
                        </div>
                    </div>
                    <div class="rr-stats-event-card__right">
                        <strong>${modalidadesCount}</strong>
                        <small>modalidades</small>
                    </div>
                </div>
            `;
            listEl.insertAdjacentHTML('beforeend', html);
        });
}

function updateText(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
}

// Tornar global para acesso via onclick no HTML
window.switchEpicTab = function(tabName) {
    // Buttons (X1 Style)
    document.querySelectorAll('.x1-arena-modal__step').forEach(btn => {
        if (btn.dataset.tab === tabName) btn.classList.add('is-active');
        else btn.classList.remove('is-active');
    });
    
    // Content
    const tabTodas = document.getElementById('tabContentTodas');
    const tabEventos = document.getElementById('tabContentEventos');
    
    if (tabName === 'todas') {
        if (tabTodas) tabTodas.style.display = 'block';
        if (tabEventos) tabEventos.style.display = 'none';
    } else if (tabName === 'eventos') {
        if (tabTodas) tabTodas.style.display = 'none';
        if (tabEventos) tabEventos.style.display = 'block';
    }
};

function ensureCompetitorModal() {
  let el = document.getElementById('competitorModal');
  if (el) return el;
  
  el = document.createElement('div');
  el.className = 'x1-arena-modal rr-stats-epic-modal';
  el.id = 'competitorModal';
  el.setAttribute('aria-hidden', 'true');

  // Estilos base do overlay fullscreen (position fixed para cobrir toda a tela)
  Object.assign(el.style, {
    display: 'none',
    position: 'fixed',
    inset: '0',
    zIndex: COMPETITOR_MODAL_Z_INDEX,
    background: 'rgba(0, 0, 0, 0.94)',
    flexDirection: 'column',
    alignItems: 'center',
    overflowY: 'auto',
    overflowX: 'hidden',
    width: '100vw',
    height: '100vh',
    WebkitOverflowScrolling: 'touch',
    isolation: 'isolate'
  });
  
  // Canvas de partículas
  const canvas = document.createElement('canvas');
  canvas.id = 'competitorModalParticles';
  canvas.className = 'x1-arena-modal__particles';
  canvas.style.cssText = 'position:fixed;inset:0;width:100vw;height:100vh;pointer-events:none;z-index:0;';
  el.appendChild(canvas);
  
  el.innerHTML += `
    <!-- Wrapper com largura máxima para desktop -->
    <div class="x1-arena-modal__wrapper" style="position:relative; z-index:1; width:100%; max-width:600px; margin:0 auto; min-height:100vh; display:flex; flex-direction:column;">
    <!-- Header -->
    <div class="x1-arena-modal__header" style="position: relative; padding: 0; display: grid; justify-items: center; text-align: center;">
         <div class="x1-arena-header-bg"></div>

         <!-- Backlight Glow (Topo da Página) -->
         <div id="epicModalBacklight" style="
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 300px;
            z-index: 0;
            pointer-events: none;
            opacity: 0.5;
            background: radial-gradient(ellipse at center top, #f97316 0%, transparent 80%);
         "></div>
         
         <button type="button" class="x1-arena-modal__close" onclick="closeCompetitorModal()" style="position: absolute; top: 1.5rem; right: 1.5rem; z-index: 50; width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: none; color: white;">
             <i class="fas fa-times"></i>
         </button>
         
         <!-- Foto Container (Wrapper para Pop-out) -->
         <div style="position: relative; width: 120px; height: 120px; margin-top: 4rem; margin-bottom: 0.75rem; z-index: 20;">
             <!-- Círculo de Fundo (Borda) -->
             <div id="epicModalPhotoCircle" style="
                position: absolute;
                inset: 0;
                border-radius: 50%;
                border: 4px solid #fff;
                background: rgba(0,0,0,0.3);
                z-index: 10;
                box-shadow: 0 10px 40px rgba(0,0,0,0.5);
             "></div>
             
             <!-- Container de Recorte (Clip) -->
             <div style="
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 160px; /* Altura para sair pra cima */
                border-radius: 0 0 60px 60px; /* Arredonda base */
                overflow: hidden;
                z-index: 20;
                display: flex;
                align-items: flex-end;
                justify-content: center;
                pointer-events: none;
             ">
                 <img id="epicModalPhoto" src="" style="
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    object-position: top center;
                 ">
             </div>
         </div>
         
         <h2 class="x1-arena-modal__title text-center mb-1" id="epicModalName" style="position: relative; z-index: 2; text-transform: uppercase; letter-spacing: 1px; text-shadow: 0 2px 10px rgba(0,0,0,0.8);">NOME</h2>
         
         <!-- Reivindicar Perfil (abaixo do nome) -->
         <div id="epicClaimProfileWrap" style="text-align: center; position: relative; z-index: 2; margin-bottom: 0.3rem;">
             <a id="epicClaimProfileBtn" href="#" target="_blank" rel="noopener"
                style="display: inline-flex; align-items: center; gap: 0.3rem; color: rgba(255,255,255,0.45); font-size: 0.6rem; text-decoration: none; padding: 0.2rem 0.6rem; border: 1px solid rgba(255,255,255,0.15); border-radius: 20px; transition: all 0.2s;"
                onmouseover="this.style.color='#25d366';this.style.borderColor='rgba(37,211,102,0.5)'"
                onmouseout="this.style.color='rgba(255,255,255,0.45)';this.style.borderColor='rgba(255,255,255,0.15)'">
                 <i class="fab fa-whatsapp" style="font-size: 0.7rem;"></i> Você é este competidor? Reivindique seu perfil aqui.
             </a>
         </div>

         <div class="x1-arena-modal__badge x1-arena-modal__badge--premium mb-2" id="epicModalLevelBadge" style="position: relative; z-index: 2; opacity: 0.9; font-weight: 800; letter-spacing: 0.5px; align-self: center; width: fit-content; margin-left: auto; margin-right: auto; font-size: 0.7rem; padding: 0.3rem 0.8rem;">
             <i class="fas fa-crown"></i> <span id="epicModalLevel">NÍVEL</span>
         </div>

         <p class="text-white-50 small mb-3" style="position: relative; z-index: 2;">Resumo da Temporada</p>

         <div class="x1-arena-stats-row" style="position: relative; z-index: 2;">
             <div class="x1-arena-stat-item">
                 <span class="x1-arena-stat-value text-warning" id="epicKeyAproveitamento">0%</span>
                 <span class="x1-arena-stat-label">APROV.</span>
             </div>
         </div>
    </div>

    <!-- Tabs -->
    <div class="x1-arena-modal__steps d-flex justify-content-center w-100 my-3" style="position: relative; z-index: 20;">
        <button type="button" class="x1-arena-modal__step is-active" data-tab="todas" onclick="window.switchEpicTab('todas')">
           <i class="fas fa-chart-bar me-1"></i> Estatísticas
        </button>
        <button type="button" class="x1-arena-modal__step" data-tab="eventos" onclick="window.switchEpicTab('eventos')">
           <i class="fas fa-trophy me-1"></i> Eventos
        </button>
    </div>

    <!-- Content (Scrollable) -->
    <div class="x1-arena-modal__content pt-0" style="overflow-y: auto; padding-bottom: 4rem;">
         
         <!-- Tab Content: GERAL -->
         <div id="tabContentTodas" class="w-100">
             
             <!-- Main 4 Stats - LINHA ÚNICA COMPACTA -->
             <div class="stats-main-row" style="display: flex; justify-content: center; gap: 6px; padding: 0 0.5rem; margin-bottom: 1rem;">
                  <div class="stats-chip stats-chip--boas" style="flex: 1; max-width: 80px; background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 8px; padding: 0.4rem 0.25rem; text-align: center;">
                      <span class="text-success fw-bold" style="font-size: 1.1rem; display: block; line-height: 1;" id="epicStatBoas">0</span>
                      <small class="text-muted" style="font-size: 0.55rem; text-transform: uppercase;">Boas</small>
                  </div>
                  <div class="stats-chip stats-chip--erros" style="flex: 1; max-width: 80px; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; padding: 0.4rem 0.25rem; text-align: center;">
                      <span class="text-danger fw-bold" style="font-size: 1.1rem; display: block; line-height: 1;" id="epicStatErros">0</span>
                      <small class="text-muted" style="font-size: 0.55rem; text-transform: uppercase;">Erros</small>
                  </div>
                  <div class="stats-chip stats-chip--armadas" style="flex: 1; max-width: 80px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 0.4rem 0.25rem; text-align: center;">
                      <span class="text-warning fw-bold" style="font-size: 1.1rem; display: block; line-height: 1;" id="epicStatArmadas">0/0</span>
                      <small class="text-muted" style="font-size: 0.55rem; text-transform: uppercase;">Armadas</small>
                  </div>
                  <div class="stats-chip stats-chip--destrezas" style="flex: 1; max-width: 80px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 0.4rem 0.25rem; text-align: center;">
                      <span class="text-info fw-bold" style="font-size: 1.1rem; display: block; line-height: 1;" id="epicStatDestrezas">0</span>
                      <small class="text-muted" style="font-size: 0.55rem; text-transform: uppercase;">Destrezas</small>
                  </div>
             </div>

             <!-- Detailed Stats (Premium) -->
             <div id="epicModalContent" class="mt-2" style="position: relative;">
                  
                  <div class="d-flex align-items-center mb-2 px-3 rr-stats-detail-head">
                      <i class="fas fa-layer-group text-primary me-2" style="font-size: 0.85rem;"></i>
                      <h4 class="text-white mb-0" style="font-size: 0.85rem; text-transform: uppercase;">Detalhamento Técnico</h4>
                  </div>

                  <!-- Grid de Detalhes - COMPACTO -->
                  <div class="rr-stats-detail-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; padding: 0 0.75rem;">
                      <!-- Destrezas -->
                      <div class="detail-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Dobrada</small><span class="text-white fw-bold" style="font-size: 0.9rem;" id="epicStatDobrada">0</span></div>
                      <div class="detail-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Duas Voltas</small><span class="text-white fw-bold" style="font-size: 0.9rem;" id="epicStatDuasVoltas">0</span></div>
                      <div class="detail-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Cola</small><span class="text-white fw-bold" style="font-size: 0.9rem;" id="epicStatCola">0</span></div>
                      <div class="detail-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Cupim</small><span class="text-white fw-bold" style="font-size: 0.9rem;" id="epicStatCupim">0</span></div>
                      <div class="detail-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Pescou</small><span class="text-white fw-bold" style="font-size: 0.9rem;" id="epicStatPescou">0</span></div>
                      <div class="detail-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Por Cima</small><span class="text-white fw-bold" style="font-size: 0.9rem;" id="epicStatPorCima">0</span></div>
                      <div class="detail-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Limpou Garupa</small><span class="text-white fw-bold" style="font-size: 0.9rem;" id="epicStatLimpouGarupa">0</span></div>
                      <div class="detail-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Limpou Cupim</small><span class="text-white fw-bold" style="font-size: 0.9rem;" id="epicStatLimpouCupimLonge">0</span></div>
                      <div class="detail-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Limpou Top</small><span class="text-white fw-bold" style="font-size: 0.9rem;" id="epicStatLimpouTop">0</span></div>
                      <div class="detail-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Limpou Top com a Mão</small><span class="text-white fw-bold" style="font-size: 0.9rem;" id="epicStatLimpouTopMao">0</span></div>
                      <div class="detail-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Top</small><span class="text-white fw-bold" style="font-size: 0.9rem;" id="epicStatTop">0</span></div>
                      <div class="detail-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Uma Aspa</small><span class="text-white fw-bold" style="font-size: 0.9rem;" id="epicStatUmaAspa">0</span></div>
                      
                      <!-- Negativas -->
                      <div class="detail-card" style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.15); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Cabresteou</small><span class="text-danger fw-bold" style="font-size: 0.9rem;" id="epicStatCabresteou">0</span></div>
                      <div class="detail-card" style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.15); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Errou Pescoço</small><span class="text-danger fw-bold" style="font-size: 0.9rem;" id="epicStatErrouPescoco">0</span></div>
                      <div class="detail-card" style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.15); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Errou Pata</small><span class="text-danger fw-bold" style="font-size: 0.9rem;" id="epicStatErrouPata">0</span></div>
                      <div class="detail-card" style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.15); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Errou Top</small><span class="text-danger fw-bold" style="font-size: 0.9rem;" id="epicStatErrouTop">0</span></div>
                      <div class="detail-card" style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.15); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Boi Tirou</small><span class="text-danger fw-bold" style="font-size: 0.9rem;" id="epicStatBoiTirou">0</span></div>
                      <div class="detail-card" style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.15); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Boi Pulou</small><span class="text-danger fw-bold" style="font-size: 0.9rem;" id="epicStatBoiPulou">0</span></div>
                      <div class="detail-card" style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.15); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Queimou a Raia</small><span class="text-danger fw-bold" style="font-size: 0.9rem;" id="epicStatQueimouRaia">0</span></div>
                      <div class="detail-card" style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.15); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Caiu do Cavalo</small><span class="text-danger fw-bold" style="font-size: 0.9rem;" id="epicStatCaiuDoCavalo">0</span></div>
                      <div class="detail-card" style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.15); border-radius: 8px; padding: 0.5rem; text-align: center;"><small class="d-block text-muted" style="font-size:0.6rem;">Saiu Enrolado</small><span class="text-danger fw-bold" style="font-size: 0.9rem;" id="epicStatSaiuEnrolado">0</span></div>
                  </div>
                  
                  <!-- LOCKED OVERLAY - APENAS SOBRE DETALHES -->
                  <div id="epicModalLocked" style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.92); display: none; flex-direction: column; align-items: center; justify-content: center; text-align: center; z-index: 10; padding: 2rem; border-radius: 12px;">
                      <i class="fas fa-lock mb-3" style="font-size: 3rem; color: #fbbf24;"></i>
                      <h3 class="text-white fw-bold mb-2">Conteúdo Premium</h3>
                      <p class="mb-4" style="color: #94a3b8; font-size: 0.9rem; max-width: 280px;">Assine para ver todas as estatísticas detalhadas dos competidores</p>
                      <a href="javascript:void(0)" onclick="window.goToPremiumTab && window.goToPremiumTab()" style="background: linear-gradient(135deg, #f97316, #ea580c); color: white; padding: 0.75rem 2rem; border-radius: 25px; font-weight: 700; font-size: 1rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 20px rgba(249, 115, 22, 0.4);">
                         <i class="fas fa-crown"></i> Assinar Premium
                      </a>
                  </div>
             </div>
         </div>

         <!-- Tab Content: EVENTOS -->
         <div id="tabContentEventos" class="w-100" style="max-width: 800px; margin: 0 auto; display: none;">
             <div id="epicEventsList" class="d-flex flex-column gap-2 p-1">
                 <!-- Events injected here -->
             </div>
         </div>

    </div>
    </div>
  `;
  
  document.body.appendChild(el);

  // Fechar ao clicar no backdrop (área fora do wrapper)
  el.addEventListener('click', function(e) {
    if (e.target === el) {
      if (typeof window.closeCompetitorModal === 'function') window.closeCompetitorModal();
    }
  });

  // Fechar com ESC
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && el.style.display !== 'none') {
      if (typeof window.closeCompetitorModal === 'function') window.closeCompetitorModal();
    }
  });

  return el;
}

// ============================================
// PARTICLE SYSTEM (Simple Implementation)
// ============================================
let particlesAnimationId;

window.startModalParticles = function(nivel) {
    const canvas = document.getElementById('competitorModalParticles');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    let width, height;
    
    function resize() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    }
    window.addEventListener('resize', resize);
    resize();
    
    // Colors based on level
    let colors = ['#f97316', '#fbbf24', '#ffffff']; // Default (Laranja/Amarelo)
    const n = (nivel||'').toLowerCase();
    
    if (n.includes('elite')) colors = ['#ef4444', '#b91c1c', '#ffffff']; // Red
    else if (n.includes('favorito')) colors = ['#ffd700', '#f59e0b', '#ffffff']; // Gold
    else if (n.includes('competidor') || n.includes('presilha')) colors = ['#22c55e', '#16a34a', '#ffffff']; // Green
    else if (n.includes('ascendente') || n.includes('legado')) colors = ['#94a3b8', '#64748b', '#ffffff']; // Silver
    
    const particles = [];
    const particleCount = 40; // Leve
    
    for (let i = 0; i < particleCount; i++) {
        particles.push({
            x: Math.random() * width,
            y: Math.random() * height,
            radius: Math.random() * 2 + 0.5,
            color: colors[Math.floor(Math.random() * colors.length)],
            speedX: (Math.random() - 0.5) * 0.5,
            speedY: (Math.random() - 0.5) * 0.5,
            alpha: Math.random() * 0.5 + 0.1
        });
    }
    
    function animate() {
        ctx.clearRect(0, 0, width, height);
        
        particles.forEach(p => {
            p.x += p.speedX;
            p.y += p.speedY;
            
            if (p.x < 0) p.x = width;
            if (p.x > width) p.x = 0;
            if (p.y < 0) p.y = height;
            if (p.y > height) p.y = 0;
            
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
            ctx.fillStyle = p.color;
            ctx.globalAlpha = p.alpha;
            ctx.fill();
        });
        
        particlesAnimationId = requestAnimationFrame(animate);
    }
    
    animate();
};

window.stopModalParticles = function() {
    if (particlesAnimationId) {
        cancelAnimationFrame(particlesAnimationId);
        particlesAnimationId = null;
    }
};

// Tornar global
window.closeCompetitorModal = function() {
  const modal = document.getElementById('competitorModal');
  if (modal) {
    modal.classList.remove('is-open');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    
    if (typeof stopModalParticles === 'function') stopModalParticles();
  }
};

// ========== IR PARA ABA PREMIUM (sem trocar de rota) ==========
window.goToPremiumTab = function() {
  // 1. Fechar o modal de estatísticas do competidor
  if (typeof window.closeCompetitorModal === 'function') {
    window.closeCompetitorModal();
  }

  // 2. Navegar para premium dentro do hub, com fallback para botões existentes
  if (typeof window.switchHubTab === 'function') {
    window.switchHubTab('premium');
    return;
  }

  var premiumBtn = document.querySelector('.hub-mobile-tabbar__btn[data-section="premium"], .hub-header-nav__btn[data-section="premium"], .hub-navbar-tab[data-section="premium"]');
  if (premiumBtn) {
    premiumBtn.click();
  }
};

// ========== CONTROLE DE TABS PRINCIPAIS ==========
function initMainTabs() {
  const mainTabs = document.querySelectorAll('.rr-stats-main-tab');
  const tabContents = document.querySelectorAll('.rr-stats-tab-content');
  
  mainTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      const targetTab = tab.dataset.tab;
      
      // Remove active de todas as tabs
      mainTabs.forEach(t => t.classList.remove('rr-stats-main-tab--active'));
      tabContents.forEach(c => {
        c.classList.remove('rr-stats-tab-content--active');
        c.style.display = 'none';
      });
      
      // Ativa a tab clicada
      tab.classList.add('rr-stats-main-tab--active');
      const targetContent = document.getElementById('rrStatsTab' + targetTab.charAt(0).toUpperCase() + targetTab.slice(1));
      if (targetContent) {
        targetContent.style.display = 'block';
        targetContent.classList.add('rr-stats-tab-content--active');
      }
    });
  });
}

// ========== NAVEGAÇÃO DE EVENTOS ==========
function initEventNavigation() {
  const eventStatsByCompetitor = window.rrEventStatsByCompetitor || {};
  const eventLookup = window.rrEventLookup || {};
  const modalidadeLookup = window.rrModalidadeLookup || {};
  
  // Agrupar dados por evento
  const eventoData = {};
  Object.entries(eventStatsByCompetitor).forEach(([compId, events]) => {
    events.forEach(evt => {
      const rodeioId = evt.rodeio_id;
      const modalidadeId = evt.modalidade_id || 0;
      
      if (!eventoData[rodeioId]) {
        eventoData[rodeioId] = {
          nome: eventLookup[rodeioId] || 'Rodeio #' + rodeioId,
          modalidades: {}
        };
      }
      
      if (!eventoData[rodeioId].modalidades[modalidadeId]) {
        eventoData[rodeioId].modalidades[modalidadeId] = {
          nome: modalidadeLookup[modalidadeId] || 'N/A',
          stats: []
        };
      }
      
      eventoData[rodeioId].modalidades[modalidadeId].stats.push(evt);
    });
  });
  
  // Click em card de evento
  document.querySelectorAll('.rr-event-card').forEach(card => {
    card.addEventListener('click', () => {
      const rodeioId = parseInt(card.dataset.rodeioId);
      const rodeioNome = card.dataset.rodeioNome;
      showEventModalidades(rodeioId, rodeioNome, eventoData);
    });
  });
  
  // Botão voltar aos eventos
  document.getElementById('rrBtnBackToEvents')?.addEventListener('click', () => {
    document.getElementById('rrEventsGrid').style.display = 'grid';
    document.getElementById('rrEventModalidades').style.display = 'none';
  });
  
  // Botão voltar às modalidades
  document.getElementById('rrBtnBackToModalidades')?.addEventListener('click', () => {
    document.getElementById('rrEventModalidades').style.display = 'block';
    document.getElementById('rrModalidadeStats').style.display = 'none';
  });
}

function showEventModalidades(rodeioId, rodeioNome, eventoData) {
  const eventsGrid = document.getElementById('rrEventsGrid');
  const modalidadesSection = document.getElementById('rrEventModalidades');
  const titleEl = document.getElementById('rrEventModalidadesTitle');
  const gridEl = document.getElementById('rrModalidadesGrid');
  
  if (!eventoData[rodeioId]) return;
  
  eventsGrid.style.display = 'none';
  modalidadesSection.style.display = 'block';
  titleEl.textContent = rodeioNome;
  
  // Renderizar modalidades
  const modalidades = eventoData[rodeioId].modalidades;
  gridEl.innerHTML = Object.entries(modalidades).map(([modalidadeId, data]) => {
    const totalParticipacoes = data.stats.length;
    
    return '<div class="rr-modalidade-card" data-rodeio-id="' + rodeioId + '" data-modalidade-id="' + modalidadeId + '">' +
      '<h6 class="rr-modalidade-card__title">' + data.nome + '</h6>' +
      '<div class="rr-modalidade-card__stats">' +
      '<span>' + totalParticipacoes + ' participações</span>' +
      '</div></div>';
  }).join('');
  
  // Adicionar listeners nos cards de modalidade
  gridEl.querySelectorAll('.rr-modalidade-card').forEach(card => {
    card.addEventListener('click', () => {
      const modalidadeId = parseInt(card.dataset.modalidadeId);
      showModalidadeStats(rodeioId, rodeioNome, modalidadeId, eventoData);
    });
  });
}

function showModalidadeStats(rodeioId, rodeioNome, modalidadeId, eventoData) {
  const modalidadesSection = document.getElementById('rrEventModalidades');
  const statsSection = document.getElementById('rrModalidadeStats');
  const titleEl = document.getElementById('rrModalidadeStatsTitle');
  const detailsEl = document.getElementById('rrModalidadeStatsDetails');
  
  const modalidadeData = eventoData[rodeioId]?.modalidades[modalidadeId];
  if (!modalidadeData) return;
  
  modalidadesSection.style.display = 'none';
  statsSection.style.display = 'block';
  titleEl.textContent = rodeioNome + ' - ' + modalidadeData.nome;
  
  // Agregar estatísticas
  const stats = modalidadeData.stats.reduce((acc, s) => ({
    count_boa: acc.count_boa + s.count_boa,
    count_negativas_total: acc.count_negativas_total + s.count_negativas_total,
    participacoes: acc.participacoes + 1
  }), { count_boa: 0, count_negativas_total: 0, participacoes: 0 });
  
  const totalArmadas = Math.max(1, stats.count_boa + stats.count_negativas_total);
  const aproveitamento = Math.round((stats.count_boa / totalArmadas) * 100);
  
  detailsEl.innerHTML = 
    '<div class="rr-stats-detail-card">' +
      '<div class="rr-stats-detail-card__header">' +
        '<span class="rr-stats-detail-card__title">Aproveitamento</span>' +
        '<span class="rr-stats-detail-card__value">' + aproveitamento + '%</span>' +
      '</div>' +
      '<div class="rr-stats-detail-card__meta">' +
        '<span style="color: #00e676;">' + stats.count_boa + ' boas</span>' +
        '<span style="color: #ff5252;">' + stats.count_negativas_total + ' erros</span>' +
      '</div>' +
    '</div>' +
    '<div class="rr-stats-detail-card">' +
      '<div class="rr-stats-detail-card__header">' +
        '<span class="rr-stats-detail-card__title">Participações</span>' +
        '<span class="rr-stats-detail-card__value">' + stats.participacoes + '</span>' +
      '</div>' +
    '</div>';
}

// ========== PAGINAÇÃO E FILTROS ==========
function initPaginationAndFilters() {
  const inHub = !!document.getElementById('hubSection');
  if (inHub || window.__RRStatsBound) return;
  window.__RRStatsBound = true;
  
  const grid = document.getElementById('rrCardsGrid');
  if (!grid) return;
  
  const items = Array.from(grid.querySelectorAll('.rr-stats-item, .rr-card-item'));
  const filterCards = Array.from(document.querySelectorAll('#rrStatsSubmenu .rr-epic-submenu__btn[data-filter]'));
  const resultsMeta = document.getElementById('rrStatsResultsMeta');
  const modalidadeSelect = document.getElementById('rrStatsModalidadeSelect');
  
  // Paginação desktop
  let currentPage = 1;
  let totalPages = 1;
  const isMobile = () => window.matchMedia('(max-width: 575.98px)').matches;
  const isTablet = () => window.matchMedia('(min-width: 576px) and (max-width: 991.98px)').matches;
  let currentFilter = 'todos';
  let currentModalidade = String(modalidadeSelect?.value || 'todos').toLowerCase();
  let searchTerm = '';

  function applyFilter(filter){
    currentFilter = filter;
    items.forEach(el => {
      const niv = (el.getAttribute('data-nivel')||'').toLowerCase();
      const nome = (el.querySelector('.rr-stats-card__name')?.textContent || el.querySelector('.premium-card__name')?.textContent || '').toLowerCase();
      const modalidadeIds = String(el.getAttribute('data-modalidades') || '')
        .split(',')
        .map(value => value.trim())
        .filter(Boolean);

      const normalizedFilter = (filter || '').toLowerCase();
      
      // Mapear filtros para aceitar múltiplos níveis
      let matchesCategory = false;
      if (normalizedFilter === 'todos') {
        matchesCategory = true;
      } else if (normalizedFilter === 'ascendente') {
        matchesCategory = (niv === 'ascendente' || niv === 'legado');
      } else if (normalizedFilter === 'competidor') {
        matchesCategory = (niv === 'competidor' || niv === 'presilha');
      } else {
        matchesCategory = (niv === normalizedFilter);
      }
      
      const matchesModalidade = currentModalidade === 'todos' || modalidadeIds.includes(currentModalidade);
      const matchesSearch = !searchTerm || nome.includes(searchTerm.toLowerCase());
      const visible = matchesCategory && matchesModalidade && matchesSearch;

      el.style.display = visible ? '' : 'none';
    });
    currentPage = 1;
    paginate();
  }

  function applySearch() {
    applyFilter(currentFilter);
  }

  function paginate(){
    const visible = items.filter(el => el.style.display !== 'none');

    const perPage = Math.max(visible.length, 1);

    totalPages = Math.max(1, Math.ceil(visible.length / perPage));
    currentPage = Math.min(currentPage, totalPages);

    visible.forEach((el, i) => {
      const onCurrentPage = i >= (currentPage - 1) * perPage && i < currentPage * perPage;
      el.hidden = !onCurrentPage;
    });

    const prevBtn = document.getElementById('prevPageBtn');
    const nextBtn = document.getElementById('nextPageBtn');
    const pageIndicator = document.getElementById('pageIndicator');

    if (prevBtn && nextBtn && pageIndicator) {
      const currentSpan = pageIndicator.querySelector('.rr-stats-pagination__current');
      const totalSpan = pageIndicator.querySelector('.rr-stats-pagination__total');

      if (currentSpan && totalSpan) {
        currentSpan.textContent = String(currentPage);
        totalSpan.textContent = String(totalPages);
      } else {
        pageIndicator.textContent = currentPage + ' / ' + totalPages;
      }

      prevBtn.disabled = currentPage <= 1;
      nextBtn.disabled = currentPage >= totalPages;

      const pagination = document.querySelector('.rr-pagination');
      if (pagination) {
        pagination.style.display = totalPages > 1 ? 'flex' : 'none';
      }
    }

    if (resultsMeta) {
      resultsMeta.textContent = visible.length + ' competidores visíveis nesta aba.';
    }

    if (grid && currentPage > 1) {
      grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  // Eventos de paginação
  document.getElementById('prevPageBtn')?.addEventListener('click', () => {
    if (currentPage > 1) {
      currentPage--;
      paginate();
    }
  });

  document.getElementById('nextPageBtn')?.addEventListener('click', () => {
    if (currentPage < totalPages) {
      currentPage++;
      paginate();
    }
  });

  // Barra de pesquisa
  const searchInput = document.getElementById('rrStatsSearchInput');
  const searchForm = document.getElementById('rrStatsSearchForm');
  const searchIcon = document.getElementById('rrStatsSearchIcon');
  let isSearchOpen = !!searchForm?.classList.contains('is-open');

  function syncSearchState() {
    if (!searchForm || !searchInput) return;
    const hasValue = !!searchInput.value.trim();
    searchForm.classList.toggle('has-value', hasValue);
    searchForm.classList.toggle('is-open', isSearchOpen || hasValue);
  }

  function openSearch() {
    if (!searchForm || !searchInput) return;
    isSearchOpen = true;
    searchForm.classList.add('is-open');
    setTimeout(() => searchInput.focus(), 300);
  }

  function closeSearch() {
    if (!searchForm || !searchInput) return;
    isSearchOpen = false;
    searchInput.value = '';
    searchTerm = '';
    searchInput.blur();
    searchForm.classList.remove('is-open');
    syncSearchState();
    applySearch();
  }

  if (searchIcon) {
    searchIcon.addEventListener('click', function(e) {
      e.stopPropagation();
      if (isSearchOpen) {
        closeSearch();
      } else {
        openSearch();
      }
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', function() {
      searchTerm = this.value.trim();
      syncSearchState();
      applySearch();
    });

    searchInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        applySearch();
      }
    });

    searchInput.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeSearch();
      }
    });
  }

  if (searchForm) {
    searchForm.addEventListener('click', function(e) {
      if (!isSearchOpen && (!searchIcon || !searchIcon.contains(e.target))) {
        openSearch();
      }
    });
  }

  if (filterCards.length) {
    filterCards.forEach(card => {
      card.addEventListener('click', function() {
        const filter = (card.getAttribute('data-filter') || 'todos').toLowerCase();
        currentFilter = filter;
        currentPage = 1;
        filterCards.forEach(btn => btn.classList.remove('is-active'));
        card.classList.add('is-active');
        applyFilter(currentFilter);
      });
    });
  }

  if (modalidadeSelect) {
    modalidadeSelect.addEventListener('change', function() {
      currentModalidade = String(this.value || 'todos').toLowerCase();
      currentPage = 1;
      applyFilter(currentFilter);
    });
  }

  window.addEventListener('resize', () => {
    paginate();
  });

  syncSearchState();
  applyFilter('todos');
}

function lockCompetitorModalBackground() {
  if (document.body.classList.contains('rr-competitor-modal-lock')) return;

  const scrollY = window.scrollY || window.pageYOffset || 0;
  window.__rrCompetitorModalScrollY = scrollY;
  window.__rrCompetitorModalBodyLock = {
    overflow: document.body.style.overflow || '',
    position: document.body.style.position || '',
    top: document.body.style.top || '',
    left: document.body.style.left || '',
    right: document.body.style.right || '',
    width: document.body.style.width || '',
  };

  document.body.classList.add('modal-open', 'rr-competitor-modal-lock');
  document.body.style.overflow = 'hidden';
  document.body.style.position = 'fixed';
  document.body.style.top = `-${scrollY}px`;
  document.body.style.left = '0';
  document.body.style.right = '0';
  document.body.style.width = '100%';
}

function unlockCompetitorModalBackground() {
  if (!document.body.classList.contains('rr-competitor-modal-lock')) return;

  const previous = window.__rrCompetitorModalBodyLock || {};
  const scrollY = Number(window.__rrCompetitorModalScrollY || 0);

  document.body.classList.remove('rr-competitor-modal-lock', 'modal-open');
  document.body.style.overflow = previous.overflow || '';
  document.body.style.position = previous.position || '';
  document.body.style.top = previous.top || '';
  document.body.style.left = previous.left || '';
  document.body.style.right = previous.right || '';
  document.body.style.width = previous.width || '';
  window.scrollTo(0, scrollY);
}

function aggregateStatsRecords(records) {
  const detailMap = new Map();
  RR_COMPETITOR_DETAIL_FIELDS.forEach((meta) => {
    detailMap.set(meta.field, {
      field: meta.field,
      label: meta.label,
      tone: meta.tone,
      value: 0,
    });
  });

  const totals = { boas: 0, errors: 0, attempts: 0, destrezas: 0 };

  (records || []).forEach((record) => {
    const stats = record?.stats || record;
    totals.boas += Number(stats?.boas || 0);
    totals.errors += Number(stats?.errors || 0);
    totals.attempts += Number(stats?.attempts || 0);
    totals.destrezas += Number(stats?.destrezas || 0);

    (stats?.details || []).forEach((item) => {
      if (!detailMap.has(item.field)) return;
      detailMap.get(item.field).value += Number(item.value || 0);
    });
  });

  if (!totals.attempts) {
    totals.attempts = totals.boas + totals.errors;
  }

  const aproveitamento = totals.attempts > 0
    ? Math.round((totals.boas / totals.attempts) * 1000) / 10
    : 0;

  return {
    aproveitamento,
    boas: totals.boas,
    errors: totals.errors,
    attempts: totals.attempts,
    armadasLabel: totals.attempts > 0 ? `${totals.boas}/${totals.attempts}` : '0/0',
    destrezas: totals.destrezas,
    details: RR_COMPETITOR_DETAIL_FIELDS.map((meta) => detailMap.get(meta.field)),
  };
}

function getCompetitorFetchOptions() {
  return {
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  };
}

function getCompetitorCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function formatFollowersCount(value) {
  return Number(value || 0).toLocaleString('pt-BR');
}

function buildCompetitorContextOptions() {
  const state = getCompetitorModalState();
  const contexts = Array.isArray(state.contexts) ? state.contexts : [];
  const selectedEventId = String(state.selected.eventId || 'global');
  const selectedModalidadeId = String(state.selected.modalidadeId || '');
  const selectedDivisao = String(state.selected.divisao || '');

  const eventOptions = [{ value: 'global', label: 'Todos os eventos' }];
  const eventMap = new Map();
  contexts.forEach((context) => {
    const key = String(context.rodeio_id || 0);
    if (!eventMap.has(key)) {
      eventMap.set(key, { value: key, label: context.rodeio_name || `Evento #${key}` });
    }
  });
  eventOptions.push(...Array.from(eventMap.values()));

  const contextsByEvent = selectedEventId === 'global'
    ? contexts
    : contexts.filter((context) => String(context.rodeio_id || '') === selectedEventId);

  const modalidadeMap = new Map();
  contextsByEvent.forEach((context) => {
    const key = String(context.modalidade_id || 0);
    if (!modalidadeMap.has(key)) {
      modalidadeMap.set(key, { value: key, label: context.modalidade_name || `Modalidade #${key}` });
    }
  });

  let modalidadeId = selectedModalidadeId;
  if (modalidadeId && !modalidadeMap.has(modalidadeId)) {
    modalidadeId = '';
    state.selected.modalidadeId = '';
  }

  const modalidadeOptions = [{ value: '', label: 'Todas as modalidades' }, ...Array.from(modalidadeMap.values())];
  const contextsByModalidade = modalidadeId
    ? contextsByEvent.filter((context) => String(context.modalidade_id || '') === modalidadeId)
    : contextsByEvent;

  const divisaoMap = new Map();
  contextsByModalidade.forEach((context) => {
    const key = String(context.divisao || '');
    if (!divisaoMap.has(key)) {
      divisaoMap.set(key, { value: key, label: context.divisao_label || (key || 'Geral') });
    }
  });

  let divisao = selectedDivisao;
  if (divisao && !divisaoMap.has(divisao)) {
    divisao = '';
    state.selected.divisao = '';
  }

  const divisaoOptions = [{ value: '', label: 'Todas as divisões' }, ...Array.from(divisaoMap.values())];
  const matchingContexts = divisao
    ? contextsByModalidade.filter((context) => String(context.divisao || '') === divisao)
    : contextsByModalidade;

  return {
    eventOptions,
    modalidadeOptions,
    divisaoOptions,
    matchingContexts,
    selectedEventId,
    selectedModalidadeId: modalidadeId,
    selectedDivisao: divisao,
  };
}

function renderSelectOptions(select, options, value) {
  if (!select) return;

  select.innerHTML = (options || []).map((option) => (
    `<option value="${escapeHtml(option.value)}">${escapeHtml(option.label)}</option>`
  )).join('');
  select.value = value ?? '';
}

function buildCompetitorContextLabel() {
  const options = buildCompetitorContextOptions();
  const matching = options.matchingContexts || [];

  if (!matching.length) {
    return getGlobalContextLabel();
  }

  if (matching.length === 1) {
    return matching[0].context_label || getGlobalContextLabel();
  }

  const parts = [];
  const eventLabel = options.eventOptions.find((item) => item.value === options.selectedEventId)?.label;
  const modalidadeLabel = options.modalidadeOptions.find((item) => item.value === options.selectedModalidadeId)?.label;
  const divisaoLabel = options.divisaoOptions.find((item) => item.value === options.selectedDivisao)?.label;

  if (options.selectedEventId !== 'global' && eventLabel) parts.push(eventLabel);
  if (options.selectedModalidadeId && modalidadeLabel) parts.push(modalidadeLabel);
  if (options.selectedDivisao && divisaoLabel) parts.push(divisaoLabel);
  if (!parts.length) parts.push('Todos os eventos');

  return parts.join(' • ');
}

function renderCompetitorFilterControls() {
  const modal = document.getElementById('competitorModal');
  if (!modal) return;

  const state = getCompetitorModalState();
  const options = buildCompetitorContextOptions();
  const eventSelect = modal.querySelector('#epicFilterEvent');
  const modalidadeSelect = modal.querySelector('#epicFilterModalidade');
  const divisaoSelect = modal.querySelector('#epicFilterDivisao');
  const emptyNote = modal.querySelector('#epicFilterEmptyNote');

  renderSelectOptions(eventSelect, options.eventOptions, options.selectedEventId);
  renderSelectOptions(modalidadeSelect, options.modalidadeOptions, options.selectedModalidadeId);
  renderSelectOptions(divisaoSelect, options.divisaoOptions, options.selectedDivisao);

  if (eventSelect) eventSelect.disabled = !state.contexts.length;
  if (modalidadeSelect) modalidadeSelect.disabled = options.modalidadeOptions.length <= 1;
  if (divisaoSelect) divisaoSelect.disabled = options.divisaoOptions.length <= 1;
  if (emptyNote) emptyNote.hidden = options.matchingContexts.length > 0 || !state.contexts.length;
}

function renderCompetitorSummary(stats) {
  const modal = document.getElementById('competitorModal');
  if (!modal) return;

  const summaryMap = {
    epicSummaryAproveitamento: `${Number(stats?.aproveitamento || 0).toLocaleString('pt-BR', { maximumFractionDigits: 1 })}%`,
    epicSummaryArmadas: stats?.armadasLabel || '0/0',
    epicSummaryBoas: Number(stats?.boas || 0).toLocaleString('pt-BR'),
    epicSummaryErros: Number(stats?.errors || 0).toLocaleString('pt-BR'),
    epicSummaryDestrezas: Number(stats?.destrezas || 0).toLocaleString('pt-BR'),
  };

  Object.entries(summaryMap).forEach(([id, value]) => {
    const element = modal.querySelector(`#${id}`);
    if (element) element.textContent = value;
  });
}

function renderCompetitorDetailGrid(stats) {
  const modal = document.getElementById('competitorModal');
  const grid = modal?.querySelector('#epicDetailsGrid');
  const overlay = modal?.querySelector('#epicModalLocked');
  const detailsWrap = modal?.querySelector('#epicDetailsWrap');
  const premiumPreview = modal?.querySelector('#epicPremiumPreview');
  if (!grid || !overlay || !detailsWrap || !premiumPreview) return;

  const detailItems = Array.isArray(stats?.details) ? stats.details : [];
  const positiveItems = detailItems.filter((item) => (item?.tone || 'neutral') === 'positive');
  const negativeItems = detailItems.filter((item) => (item?.tone || 'neutral') === 'negative');

  const renderDetailCards = (items) => items.map((item) => `
    <article class="rr-competitor-modal-v2__detail-card rr-competitor-modal-v2__detail-card--${escapeHtml(item.tone || 'neutral')}">
      <span>${escapeHtml(item.label || 'Métrica')}</span>
      <strong>${Number(item.value || 0).toLocaleString('pt-BR')}</strong>
    </article>
  `).join('');

  const renderSection = (title, subtitle, tone, icon, items) => `
    <section class="rr-competitor-modal-v2__detail-section rr-competitor-modal-v2__detail-section--${tone}">
      <header class="rr-competitor-modal-v2__detail-section-head">
        <span class="rr-competitor-modal-v2__detail-section-icon"><i class="fas ${icon}"></i></span>
        <div>
          <strong>${title}</strong>
          <span>${subtitle}</span>
        </div>
      </header>
      <div class="rr-competitor-modal-v2__details-grid">
        ${renderDetailCards(items)}
      </div>
    </section>
  `;

  const state = getCompetitorModalState();
  if (!state.isPremium) {
    const activeLabel = escapeHtml(state.activeContext?.label || getGlobalContextLabel());
    detailsWrap.hidden = true;
    premiumPreview.hidden = false;
    premiumPreview.innerHTML = `
      <section class="rr-competitor-modal-v2__premium-preview">
        <div class="rr-competitor-modal-v2__premium-preview-head">
          <span class="rr-competitor-modal-v2__premium-preview-badge"><i class="fas fa-crown"></i> Premium</span>
          <strong>Destrave a ficha completa de ${escapeHtml(state.competitorName || 'este competidor')}</strong>
          <p>No Premium você libera a leitura técnica completa do recorte <b>${activeLabel}</b>, sem overlay pesado e com tudo organizado em tempo real.</p>
        </div>
        <div class="rr-competitor-modal-v2__premium-preview-grid">
          <article><i class="fas fa-chart-line"></i><span>Estatísticas técnicas completas por contexto</span></article>
          <article><i class="fas fa-star"></i><span>Destrezas detalhadas e leitura de pontos fortes</span></article>
          <article><i class="fas fa-triangle-exclamation"></i><span>Erros separados por tipo para leitura rápida</span></article>
          <article><i class="fas fa-filter"></i><span>Recorte por evento, modalidade e divisão</span></article>
          <article><i class="fas fa-balance-scale"></i><span>Comparativo premium entre competidores</span></article>
          <article><i class="fas fa-bolt"></i><span>Radar mais limpo para decidir a entrada com mais confiança</span></article>
        </div>
        <div class="rr-competitor-modal-v2__premium-preview-actions">
          <button type="button" onclick="window.goToPremiumTab ? window.goToPremiumTab() : (window.switchHubTab && window.switchHubTab('premium'));">Quero liberar tudo</button>
        </div>
      </section>
    `;
    overlay.hidden = true;
    return;
  }

  detailsWrap.hidden = false;
  premiumPreview.hidden = true;
  premiumPreview.innerHTML = '';
  grid.innerHTML = `
    ${renderSection('Destrezas', 'Manobras especiais e leitura técnica', 'positive', 'fa-star', positiveItems)}
    ${renderSection('Erros', 'Penalizações e ações negativas', 'negative', 'fa-exclamation-triangle', negativeItems)}
  `;
  overlay.hidden = true;
}

function renderCompetitorEventsFallback() {
  const state = getCompetitorModalState();
  const modal = document.getElementById('competitorModal');
  const list = modal?.querySelector('#epicEventsList');
  if (!list) return;

  const oldMap = window.rrEventStatsByCompetitor || {};
  const lookup = window.rrEventLookup || {};
  const competitorEvents = Array.isArray(oldMap[state.competitorId]) ? oldMap[state.competitorId] : [];

  if (!competitorEvents.length) {
    list.innerHTML = '<div class="rr-competitor-modal-v2__empty">Nenhum recorte contextual disponível para este competidor.</div>';
    return;
  }

  const grouped = new Map();
  competitorEvents.forEach((item) => {
    const key = String(item.rodeio_id || 0);
    if (!grouped.has(key)) {
      grouped.set(key, { label: lookup[key] || `Evento #${key}`, total: 0 });
    }
    grouped.get(key).total += 1;
  });

  list.innerHTML = Array.from(grouped.entries()).map(([key, item]) => `
    <article class="rr-competitor-modal-v2__event-card is-static">
      <div>
        <strong>${escapeHtml(item.label)}</strong>
        <span>Participações registradas: ${item.total}</span>
      </div>
      <b>#${escapeHtml(key)}</b>
    </article>
  `).join('');
}

function renderCompetitorEventsPanel() {
  const modal = document.getElementById('competitorModal');
  const list = modal?.querySelector('#epicEventsList');
  if (!list) return;

  const state = getCompetitorModalState();
  if (!state.contexts.length) {
    renderCompetitorEventsFallback();
    return;
  }

  list.innerHTML = state.contexts.map((context) => `
    <button type="button" class="rr-competitor-modal-v2__event-card" data-context-key="${escapeHtml(context.key)}">
      <div>
        <strong>${escapeHtml(context.rodeio_name)}</strong>
        <span>${escapeHtml(context.modalidade_name)}${context.divisao_label ? ` • ${escapeHtml(context.divisao_label)}` : ''}</span>
      </div>
      <div class="rr-competitor-modal-v2__event-meta">
        <b>${Number(context.stats?.aproveitamento || 0).toLocaleString('pt-BR', { maximumFractionDigits: 1 })}%</b>
        <span>${escapeHtml(context.stats?.armadas_label || '0/0')}</span>
      </div>
    </button>
  `).join('');

  list.querySelectorAll('[data-context-key]').forEach((button) => {
    button.addEventListener('click', () => {
      const context = state.contexts.find((item) => String(item.key) === String(button.dataset.contextKey || ''));
      if (!context) return;
      state.selected.eventId = String(context.rodeio_id || 'global');
      state.selected.modalidadeId = String(context.modalidade_id || '');
      state.selected.divisao = String(context.divisao || '');
      renderCompetitorFilterControls();
      applySelectedCompetitorContext();
      setCompetitorModalTab('stats');
    });
  });
}

function renderCompetitorHistoryPanel() {
  const modal = document.getElementById('competitorModal');
  const list = modal?.querySelector('#epicHistoryList');
  if (!list) return;

  const state = getCompetitorModalState();
  if (!Array.isArray(state.recentEvents) || !state.recentEvents.length) {
    list.innerHTML = '<div class="rr-competitor-modal-v2__empty">Nenhuma movimentação registrada para este competidor.</div>';
    return;
  }

  list.innerHTML = state.recentEvents.map((event) => {
    const contextTrail = [
      event.rodeio_name,
      event.modalidade_name,
      event.divisao,
    ].filter(Boolean).join(' • ');

    const detailTrail = [
      event.group_name ? `Grupo ${event.group_name}` : '',
      event.prize_label ? `Prêmio ${event.prize_label}` : '',
    ].filter(Boolean).join(' • ');

    return `
      <a class="rr-competitor-modal-v2__event-card rr-competitor-modal-v2__event-card--timeline" href="${escapeHtml(event.cta_url || '/estatisticas')}" ${event.cta_url ? 'target="_self"' : ''}>
        <div>
          <strong>${escapeHtml(event.title || 'Nova movimentação')}</strong>
          <span>${escapeHtml(event.message || '')}</span>
          ${contextTrail ? `<small class="rr-competitor-modal-v2__event-trail">${escapeHtml(contextTrail)}</small>` : ''}
          ${detailTrail ? `<small class="rr-competitor-modal-v2__event-trail rr-competitor-modal-v2__event-trail--accent">${escapeHtml(detailTrail)}</small>` : ''}
        </div>
        <div class="rr-competitor-modal-v2__event-meta">
          <b>${escapeHtml(event.created_human || 'agora')}</b>
          <span>${escapeHtml(event.cta_label || 'Ver ficha completa')}</span>
        </div>
      </a>
    `;
  }).join('');
}

function renderCompareHeroCard(competitor, side) {
  if (!competitor) return '';

  const aproveitamento = Number(competitor.stats?.aproveitamento || 0).toLocaleString('pt-BR', { maximumFractionDigits: 1 });
  return `
    <article class="rr-competitor-modal-v2__compare-card rr-competitor-modal-v2__compare-card--${escapeHtml(side)}">
      <div class="rr-competitor-modal-v2__compare-head">
        <img src="${escapeHtml(competitor.photo_url || '/assets/images/logo_icon/favicon.png')}" alt="${escapeHtml(competitor.name)}" onerror="this.onerror=null;this.src='/assets/images/logo_icon/favicon.png';">
        <div>
          <strong>${escapeHtml(competitor.name || competitor.short_name || 'Competidor')}</strong>
          <span>${escapeHtml(competitor.level_label || 'Competidor')}</span>
        </div>
      </div>
      <div class="rr-competitor-modal-v2__compare-summary">
        <div><span>Aprov.</span><b>${escapeHtml(aproveitamento)}%</b></div>
        <div><span>Armadas</span><b>${escapeHtml(competitor.stats?.armadas_label || '0/0')}</b></div>
        <div><span>Destrezas</span><b>${Number(competitor.stats?.destrezas || 0).toLocaleString('pt-BR')}</b></div>
      </div>
    </article>
  `;
}

function renderCompareRow(row) {
  return `
    <article class="rr-competitor-modal-v2__compare-row">
      <b class="rr-competitor-modal-v2__compare-value${row.winner === 'a' ? ' is-winner' : ''}">${escapeHtml(row.display_a || '0')}</b>
      <span>${escapeHtml(row.label || '')}</span>
      <b class="rr-competitor-modal-v2__compare-value${row.winner === 'b' ? ' is-winner' : ''}">${escapeHtml(row.display_b || '0')}</b>
    </article>
  `;
}

function renderCompareSection(title, subtitle, tone, icon, rows) {
  if (!Array.isArray(rows) || !rows.length) return '';

  return `
    <section class="rr-competitor-modal-v2__compare-block rr-competitor-modal-v2__compare-block--${escapeHtml(tone)}">
      <header class="rr-competitor-modal-v2__compare-section-head">
        <span class="rr-competitor-modal-v2__compare-section-icon"><i class="fas ${escapeHtml(icon)}"></i></span>
        <div>
          <strong>${escapeHtml(title)}</strong>
          <span>${escapeHtml(subtitle)}</span>
        </div>
      </header>
      <div class="rr-competitor-modal-v2__compare-rows">${rows.map(renderCompareRow).join('')}</div>
    </section>
  `;
}

function renderCompetitorComparePanel() {
  const state = getCompetitorModalState();
  const modal = document.getElementById('competitorModal');
  const gate = modal?.querySelector('#epicCompareGate');
  const toolbar = modal?.querySelector('#epicCompareToolbar');
  const selector = modal?.querySelector('#epicCompareSelector');
  const results = modal?.querySelector('#epicCompareResults');
  const context = modal?.querySelector('#epicCompareContext');
  const content = modal?.querySelector('#epicCompareContent');
  const current = modal?.querySelector('#epicCompareCurrent');
  if (!gate || !toolbar || !selector || !results || !context || !content || !current) return;

  const activeLabel = state.activeContext?.label || getGlobalContextLabel();
  context.textContent = activeLabel;
  current.innerHTML = renderCompareHeroCard({
    photo_url: state.competitorPhoto,
    name: state.competitorName,
    level_label: getCompetitorLevelTheme(state.competitorLevel).label,
    stats: state.activeContext?.stats || state.globalStats,
  }, 'a');

  if (!state.isPremium) {
    gate.hidden = false;
    toolbar.hidden = true;
    selector.hidden = true;
    content.innerHTML = '';
    return;
  }

  gate.hidden = true;
  toolbar.hidden = false;
  selector.hidden = !state.compareSelectorOpen;
  if (!state.compareSelectorOpen) results.innerHTML = '';

  if (!state.compareData) {
    content.innerHTML = '<div class="rr-competitor-modal-v2__empty">Escolha outro competidor para abrir o comparativo frente a frente.</div>';
    return;
  }

  const data = state.compareData;
  content.innerHTML = `
    <div class="rr-competitor-modal-v2__compare-stage">
      ${renderCompareHeroCard(data.competitor_a, 'a')}
      <div class="rr-competitor-modal-v2__compare-versus">VS</div>
      ${renderCompareHeroCard(data.competitor_b, 'b')}
    </div>
    <section class="rr-competitor-modal-v2__compare-block">
      <header><strong>Comparativo principal</strong><span>${escapeHtml(data.context?.label || activeLabel)}</span></header>
      <div class="rr-competitor-modal-v2__compare-rows">${(data.summary_rows || []).map(renderCompareRow).join('')}</div>
    </section>
    ${renderCompareSection('Destrezas', 'Manobras especiais e leitura técnica', 'positive', 'fa-star', data.detail_positive_rows || [])}
    ${renderCompareSection('Erros', 'Penalizações e ações negativas', 'negative', 'fa-exclamation-triangle', data.detail_negative_rows || [])}
  `;
}

async function loadCompareCandidates(searchTerm) {
  const state = getCompetitorModalState();
  const modal = document.getElementById('competitorModal');
  const results = modal?.querySelector('#epicCompareResults');
  if (!results) return;

  const params = new URLSearchParams({
    exclude: String(state.competitorId || ''),
    q: String(searchTerm || '').trim(),
    limit: '18',
  });

  if (state.selected.eventId !== 'global') params.set('rodeio_id', String(state.selected.eventId || ''));
  if (state.selected.modalidadeId) params.set('modalidade_id', String(state.selected.modalidadeId));
  if (state.selected.divisao) params.set('divisao', String(state.selected.divisao));

  state.compareLoading = true;
  results.innerHTML = '<div class="rr-competitor-modal-v2__empty">Buscando competidores...</div>';

  try {
    const response = await fetch(`/api/stats/competitors/search?${params.toString()}`, getCompetitorFetchOptions());
    const payload = await response.json();
    if (!response.ok || !payload?.success) {
      throw new Error(payload?.message || 'Não foi possível buscar competidores agora.');
    }

    state.compareCandidates = Array.isArray(payload.data) ? payload.data : [];
    if (!state.compareCandidates.length) {
      results.innerHTML = '<div class="rr-competitor-modal-v2__empty">Nenhum competidor encontrado nesse recorte.</div>';
      return;
    }

    results.innerHTML = state.compareCandidates.map((competitor) => `
      <button type="button" class="rr-competitor-modal-v2__candidate" data-competitor-id="${escapeHtml(competitor.id)}">
        <img src="${escapeHtml(competitor.photo_url || '/assets/images/logo_icon/favicon.png')}" alt="${escapeHtml(competitor.name)}" onerror="this.onerror=null;this.src='/assets/images/logo_icon/favicon.png';">
        <div>
          <strong>${escapeHtml(competitor.name)}</strong>
          <span>${escapeHtml(competitor.level_label || 'Competidor')}</span>
        </div>
      </button>
    `).join('');

    results.querySelectorAll('[data-competitor-id]').forEach((button) => {
      button.addEventListener('click', () => {
        loadCompetitorComparison(button.dataset.competitorId);
      });
    });
  } catch (error) {
    results.innerHTML = `<div class="rr-competitor-modal-v2__empty">${escapeHtml(error.message || 'Erro ao buscar competidores.')}</div>`;
  } finally {
    state.compareLoading = false;
  }
}

async function loadCompetitorComparison(targetCompetitorId) {
  const state = getCompetitorModalState();
  const modal = document.getElementById('competitorModal');
  const content = modal?.querySelector('#epicCompareContent');
  if (!content) return;

  const params = new URLSearchParams({
    competitor_a: String(state.competitorId || ''),
    competitor_b: String(targetCompetitorId || ''),
  });

  if (state.selected.eventId !== 'global') params.set('rodeio_id', String(state.selected.eventId || ''));
  if (state.selected.modalidadeId) params.set('modalidade_id', String(state.selected.modalidadeId));
  if (state.selected.divisao) params.set('divisao', String(state.selected.divisao));

  content.innerHTML = '<div class="rr-competitor-modal-v2__empty">Montando comparativo...</div>';

  try {
    const response = await fetch(`/api/stats/competitors/compare?${params.toString()}`, getCompetitorFetchOptions());
    const payload = await response.json();
    if (!response.ok || !payload?.success) {
      throw new Error(payload?.message || 'Não foi possível comparar esses competidores agora.');
    }

    state.compareData = payload.data || null;
    state.compareSelectorOpen = false;
    const compareInput = modal.querySelector('#epicCompareSearch');
    if (compareInput) compareInput.value = '';
    renderCompetitorComparePanel();
  } catch (error) {
    content.innerHTML = `<div class="rr-competitor-modal-v2__empty">${escapeHtml(error.message || 'Erro ao montar comparativo.')}</div>`;
  }
}

function applySelectedCompetitorContext() {
  const state = getCompetitorModalState();
  const modal = document.getElementById('competitorModal');
  if (!modal) return;

  const options = buildCompetitorContextOptions();
  let contextStats = state.globalStats;
  let contextLabel = getGlobalContextLabel();
  let contextMeta = {
    mode: 'global',
    rodeioId: null,
    modalidadeId: null,
    divisao: '',
  };

  if (options.matchingContexts.length) {
    contextStats = aggregateStatsRecords(options.matchingContexts);
    contextLabel = buildCompetitorContextLabel();
    contextMeta = {
      mode: 'context',
      rodeioId: options.selectedEventId !== 'global' ? Number(options.selectedEventId || 0) : null,
      modalidadeId: options.selectedModalidadeId ? Number(options.selectedModalidadeId || 0) : null,
      divisao: options.selectedDivisao || '',
    };
  }

  state.activeContext = {
    ...contextMeta,
    label: contextLabel,
    stats: contextStats,
    records: options.matchingContexts,
  };

  const labelEl = modal.querySelector('#epicFilterContextLabel');
  const heroEl = modal.querySelector('#epicHeroContextHint');
  if (labelEl) labelEl.textContent = contextLabel;
  if (heroEl) heroEl.textContent = contextLabel;

  renderCompetitorSummary(contextStats);
  renderCompetitorDetailGrid(contextStats);
  renderCompetitorEventsPanel();
  renderCompetitorHistoryPanel();
  renderCompetitorComparePanel();
}

function setCompetitorModalLoading(isLoading) {
  const modal = document.getElementById('competitorModal');
  const loader = modal?.querySelector('#epicModalLoading');
  if (!loader) return;
  loader.hidden = !isLoading;
}

function updateCompetitorFollowUi() {
  const state = getCompetitorModalState();
  const modal = document.getElementById('competitorModal');
  if (!modal) return;

  const button = modal.querySelector('#epicFollowCompetitor');
  const count = modal.querySelector('#epicFollowersCount');
  const hint = modal.querySelector('#epicFollowersHint');

  if (count) {
    count.textContent = `${formatFollowersCount(state.followersCount)} seguidores`;
  }

  if (hint) {
    hint.textContent = state.canFollow
      ? 'Siga para receber e-mails quando ele entrar em arena, bolão ou modalidade.'
      : 'Faça login para seguir e receber alertas completos por e-mail.';
  }

  if (!button) return;

  button.disabled = state.followLoading;
  button.classList.toggle('is-active', !!state.isFollowing);

  if (!state.canFollow) {
    button.innerHTML = '<i class="fas fa-lock"></i><span>Entrar para seguir</span>';
    return;
  }

  if (state.followLoading) {
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Atualizando...</span>';
    return;
  }

  button.innerHTML = state.isFollowing
    ? '<i class="fas fa-check"></i><span>Seguindo</span>'
    : '<i class="fas fa-bell"></i><span>Seguir</span>';
}

async function toggleCompetitorFollow() {
  const state = getCompetitorModalState();
  if (!state.canFollow || !state.competitorId || state.followLoading) {
    if (!state.canFollow) {
      window.alert('Faça login para seguir este competidor.');
    }
    return;
  }

  state.followLoading = true;
  updateCompetitorFollowUi();

  try {
    const response = await fetch(`/web/competitors/${state.competitorId}/follow`, {
      method: state.isFollowing ? 'DELETE' : 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': getCompetitorCsrfToken(),
      },
    });

    const payload = await response.json();
    if (!response.ok || !payload?.success) {
      throw new Error(payload?.message || 'Não foi possível atualizar o acompanhamento agora.');
    }

    state.isFollowing = !!payload.following;
    state.followersCount = Number(payload.followers_count || 0);
  } catch (error) {
    window.alert(error.message || 'Erro ao atualizar o competidor.');
  } finally {
    state.followLoading = false;
    updateCompetitorFollowUi();
  }
}

function populateCompetitorModalShell() {
  const state = getCompetitorModalState();
  const modal = document.getElementById('competitorModal');
  if (!modal) return;

  const photo = modal.querySelector('#epicModalPhoto');
  const name = modal.querySelector('#epicModalName');
  const level = modal.querySelector('#epicModalLevelBadge');
  const claim = modal.querySelector('#epicClaimProfileWrap');
  const compareBtn = modal.querySelector('#epicCompareTrigger');

  if (photo) {
    photo.src = state.competitorPhoto || '/assets/images/logo_icon/favicon.png';
    photo.onerror = function() {
      this.onerror = null;
      this.src = '/assets/images/logo_icon/favicon.png';
    };
  }
  if (name) name.textContent = state.competitorName || 'Competidor';
  if (level) {
    const theme = getCompetitorLevelTheme(state.competitorLevel);
    level.textContent = theme.label;
    level.style.setProperty('--rr-level-color', theme.color);
  }
  if (compareBtn) {
    compareBtn.innerHTML = state.isPremium
      ? '<i class="fas fa-balance-scale"></i><span>Comparar</span>'
      : '<i class="fas fa-crown"></i><span>Comparar Premium</span>';
  }

  if (claim) {
    if (state.claimed) {
      claim.innerHTML = '<span class="rr-competitor-modal-v2__claim-ok"><i class="fas fa-check-circle"></i> Perfil já reivindicado</span>';
    } else {
      const message = encodeURIComponent(`Olá! Eu sou o competidor ${state.competitorName}. Quero reivindicar meu perfil no Rei do Rodeio e ativar o benefício premium grátis para competidores.`);
      claim.innerHTML = `
        <a id="epicClaimProfileBtn" href="https://wa.me/5547997953323?text=${message}" target="_blank" rel="noopener">
          <i class="fab fa-whatsapp"></i>
          <span>Reivindicar perfil e ganhar Premium grátis</span>
        </a>
      `;
    }
  }

  updateCompetitorFollowUi();
  renderCompetitorFilterControls();
  applySelectedCompetitorContext();
}

async function loadCompetitorContexts(competitorId) {
  const state = getCompetitorModalState();
  setCompetitorModalLoading(true);

  try {
    const response = await fetch(`/api/stats/competitors/${competitorId}/contexts`, getCompetitorFetchOptions());
    const payload = await response.json();
    if (!response.ok || !payload?.success) {
      throw new Error(payload?.message || 'Não foi possível carregar a ficha completa agora.');
    }

    const data = payload.data || {};
    state.contexts = Array.isArray(data.contexts) ? data.contexts : [];
    if (data.global) {
      state.globalStats = {
        ...data.global,
        armadasLabel: data.global.armadas_label || data.global.armadasLabel || '0/0',
      };
    }
    if (data.competitor) {
      state.competitorName = data.competitor.name || state.competitorName;
      state.competitorPhoto = data.competitor.photo_url || state.competitorPhoto;
      state.competitorLevel = normalizeCompetitorLevel(data.competitor.level || state.competitorLevel);
      state.claimed = !!data.competitor.claimed;
      state.followersCount = Number(data.competitor.followers_count || 0);
      state.isFollowing = !!data.competitor.is_following;
      state.canFollow = !!data.competitor.can_follow;
    }
    state.recentEvents = Array.isArray(data.recent_events) ? data.recent_events : [];
  } catch (error) {
    const modal = document.getElementById('competitorModal');
    const eventsList = modal?.querySelector('#epicEventsList');
    const historyList = modal?.querySelector('#epicHistoryList');
    if (eventsList) {
      eventsList.innerHTML = `<div class="rr-competitor-modal-v2__empty">${escapeHtml(error.message || 'Erro ao carregar a ficha.')}</div>`;
    }
    if (historyList) {
      historyList.innerHTML = `<div class="rr-competitor-modal-v2__empty">${escapeHtml(error.message || 'Erro ao carregar o histórico.')}</div>`;
    }
  } finally {
    setCompetitorModalLoading(false);
    populateCompetitorModalShell();
  }
}

function setCompetitorModalTab(tabName) {
  const state = getCompetitorModalState();
  const modal = document.getElementById('competitorModal');
  if (!modal) return;

  state.currentTab = tabName;
  modal.querySelectorAll('[data-modal-tab]').forEach((button) => {
    button.classList.toggle('is-active', button.dataset.modalTab === tabName);
  });
  modal.querySelectorAll('[data-modal-panel]').forEach((panel) => {
    panel.hidden = panel.dataset.modalPanel !== tabName;
  });

  if (tabName === 'compare') {
    renderCompetitorComparePanel();
  }
}

function ensureCompetitorModalV2() {
  let el = document.getElementById('competitorModal');
  if (el && el.dataset.version === 'v2') return el;
  if (el) el.remove();

  el = document.createElement('div');
  el.id = 'competitorModal';
  el.dataset.version = 'v2';
  el.className = 'rr-competitor-modal-v2';
  el.setAttribute('aria-hidden', 'true');
  el.innerHTML = `
    <div class="rr-competitor-modal-v2__backdrop" data-close-modal="1"></div>
    <div class="rr-competitor-modal-v2__dialog" role="dialog" aria-modal="true" aria-label="Ficha completa do competidor">
      <button type="button" class="rr-competitor-modal-v2__close" id="epicCloseModal" aria-label="Fechar ficha completa">
        <i class="fas fa-times"></i>
      </button>
      <div class="rr-competitor-modal-v2__loading" id="epicModalLoading" hidden>
        <span class="rr-competitor-modal-v2__loading-dot"></span>
        <span>Carregando ficha completa...</span>
      </div>
      <div class="rr-competitor-modal-v2__body">
        <section class="rr-competitor-modal-v2__hero">
          <div class="rr-competitor-modal-v2__hero-grid">
            <div class="rr-competitor-modal-v2__hero-main">
              <div class="rr-competitor-modal-v2__hero-photo">
                <img id="epicModalPhoto" src="/assets/images/logo_icon/favicon.png" alt="Competidor">
              </div>
              <div class="rr-competitor-modal-v2__hero-copy">
                <span class="rr-competitor-modal-v2__eyebrow">Ficha completa</span>
                <h3 id="epicModalName">Competidor</h3>
                <div class="rr-competitor-modal-v2__hero-tags">
                  <span class="rr-competitor-modal-v2__level" id="epicModalLevelBadge">Competidor</span>
                  <span class="rr-competitor-modal-v2__context" id="epicHeroContextHint">${escapeHtml(getGlobalContextLabel())}</span>
                </div>
                <div class="rr-competitor-modal-v2__claim" id="epicClaimProfileWrap"></div>
              </div>
            </div>
            <div class="rr-competitor-modal-v2__hero-actions">
              <button type="button" class="rr-competitor-modal-v2__follow-trigger" id="epicFollowCompetitor">
                <i class="fas fa-bell"></i>
                <span>Seguir</span>
              </button>
              <div class="rr-competitor-modal-v2__followers-box">
                <strong id="epicFollowersCount">0 seguidores</strong>
                <span id="epicFollowersHint">Siga para receber alertas por e-mail.</span>
              </div>
              <button type="button" class="rr-competitor-modal-v2__compare-trigger" id="epicCompareTrigger">
                <i class="fas fa-balance-scale"></i>
                <span>Comparar</span>
              </button>
              <p>Abra o comparativo premium e coloque o radar frente a frente.</p>
            </div>
          </div>
          <div class="rr-competitor-modal-v2__summary">
            <article><span>Aprov.</span><strong id="epicSummaryAproveitamento">0%</strong></article>
            <article><span>Armadas</span><strong id="epicSummaryArmadas">0/0</strong></article>
            <article><span>Boas</span><strong id="epicSummaryBoas">0</strong></article>
            <article><span>Erros</span><strong id="epicSummaryErros">0</strong></article>
            <article><span>Destrezas</span><strong id="epicSummaryDestrezas">0</strong></article>
          </div>
        </section>

        <nav class="rr-competitor-modal-v2__tabs">
          <button type="button" class="is-active" data-modal-tab="stats"><i class="fas fa-chart-bar"></i><span>Estatísticas</span></button>
          <button type="button" data-modal-tab="events"><i class="fas fa-flag-checkered"></i><span>Eventos</span></button>
          <button type="button" data-modal-tab="history"><i class="fas fa-stream"></i><span>Histórico</span></button>
          <button type="button" data-modal-tab="compare"><i class="fas fa-balance-scale"></i><span>Comparar</span></button>
        </nav>

        <section class="rr-competitor-modal-v2__panel" data-modal-panel="stats">
          <div class="rr-competitor-modal-v2__filters">
            <div class="rr-competitor-modal-v2__filter"><span>Evento</span><select id="epicFilterEvent"></select></div>
            <div class="rr-competitor-modal-v2__filter"><span>Modalidade</span><select id="epicFilterModalidade"></select></div>
            <div class="rr-competitor-modal-v2__filter"><span>Divisão</span><select id="epicFilterDivisao"></select></div>
          </div>
          <div class="rr-competitor-modal-v2__context-note">
            <strong id="epicFilterContextLabel">${escapeHtml(getGlobalContextLabel())}</strong>
            <span id="epicFilterEmptyNote" hidden>Esse recorte ainda não tem estatísticas registradas.</span>
          </div>
          <div class="rr-competitor-modal-v2__details-wrap" id="epicDetailsWrap">
            <div class="rr-competitor-modal-v2__details-grid" id="epicDetailsGrid"></div>
            <div class="rr-competitor-modal-v2__premium-lock" id="epicModalLocked" hidden>
              <i class="fas fa-crown"></i>
              <strong>Detalhamento Premium</strong>
              <p>Assine para liberar todas as estatísticas técnicas e o comparativo completo.</p>
              <button type="button" onclick="window.goToPremiumTab && window.goToPremiumTab()">Quero destravar</button>
            </div>
          </div>
          <div id="epicPremiumPreview" hidden></div>
        </section>

        <section class="rr-competitor-modal-v2__panel" data-modal-panel="events" hidden>
          <div class="rr-competitor-modal-v2__events-list" id="epicEventsList"></div>
        </section>

        <section class="rr-competitor-modal-v2__panel" data-modal-panel="history" hidden>
          <div class="rr-competitor-modal-v2__events-list" id="epicHistoryList"></div>
        </section>

        <section class="rr-competitor-modal-v2__panel" data-modal-panel="compare" hidden>
          <div class="rr-competitor-modal-v2__compare-gate" id="epicCompareGate" hidden>
            <i class="fas fa-crown"></i>
            <strong>Comparativo liberado só no Premium</strong>
            <p>Abra o frente a frente entre competidores e leia o radar no mesmo recorte.</p>
            <button type="button" onclick="window.goToPremiumTab && window.goToPremiumTab()">Destravar Premium</button>
          </div>
          <div class="rr-competitor-modal-v2__compare-toolbar" id="epicCompareToolbar" hidden>
            <div><strong>Recorte atual</strong><span id="epicCompareContext">${escapeHtml(getGlobalContextLabel())}</span></div>
            <button type="button" id="epicOpenCompareSelector">Escolher competidor</button>
          </div>
          <div class="rr-competitor-modal-v2__compare-current" id="epicCompareCurrent"></div>
          <div class="rr-competitor-modal-v2__compare-selector" id="epicCompareSelector" hidden>
            <div class="rr-competitor-modal-v2__compare-selector-head">
              <strong>Buscar competidor</strong>
              <button type="button" id="epicCloseCompareSelector">Fechar</button>
            </div>
            <div class="rr-competitor-modal-v2__compare-search"><input type="search" id="epicCompareSearch" placeholder="Buscar por nome"></div>
            <div class="rr-competitor-modal-v2__compare-results" id="epicCompareResults"></div>
          </div>
          <div class="rr-competitor-modal-v2__compare-content" id="epicCompareContent"></div>
        </section>
      </div>
    </div>
  `;

  document.body.appendChild(el);

  el.addEventListener('click', (event) => {
    if (event.target === el || event.target?.matches?.('[data-close-modal="1"]')) {
      window.closeCompetitorModal();
    }
  });

  el.querySelector('#epicCloseModal')?.addEventListener('click', () => window.closeCompetitorModal());
  el.querySelector('#epicFollowCompetitor')?.addEventListener('click', () => toggleCompetitorFollow());
  el.querySelectorAll('[data-modal-tab]').forEach((button) => {
    button.addEventListener('click', () => setCompetitorModalTab(button.dataset.modalTab || 'stats'));
  });

  el.querySelector('#epicCompareTrigger')?.addEventListener('click', () => {
    const state = getCompetitorModalState();
    state.compareSelectorOpen = !!state.isPremium;
    setCompetitorModalTab('compare');
    renderCompetitorComparePanel();
    if (state.isPremium) {
      const compareInput = el.querySelector('#epicCompareSearch');
      if (compareInput) compareInput.value = '';
      loadCompareCandidates('');
    }
  });

  el.querySelector('#epicOpenCompareSelector')?.addEventListener('click', () => {
    const state = getCompetitorModalState();
    state.compareSelectorOpen = true;
    renderCompetitorComparePanel();
    loadCompareCandidates('');
    el.querySelector('#epicCompareSearch')?.focus();
  });

  el.querySelector('#epicCloseCompareSelector')?.addEventListener('click', () => {
    const state = getCompetitorModalState();
    state.compareSelectorOpen = false;
    renderCompetitorComparePanel();
  });

  el.querySelector('#epicCompareSearch')?.addEventListener('input', (event) => {
    const state = getCompetitorModalState();
    state.compareSearchTerm = String(event.target.value || '');
    loadCompareCandidates(state.compareSearchTerm);
  });

  el.querySelector('#epicFilterEvent')?.addEventListener('change', (event) => {
    const state = getCompetitorModalState();
    state.selected.eventId = String(event.target.value || 'global');
    state.selected.modalidadeId = '';
    state.selected.divisao = '';
    renderCompetitorFilterControls();
    applySelectedCompetitorContext();
  });

  el.querySelector('#epicFilterModalidade')?.addEventListener('change', (event) => {
    const state = getCompetitorModalState();
    state.selected.modalidadeId = String(event.target.value || '');
    state.selected.divisao = '';
    renderCompetitorFilterControls();
    applySelectedCompetitorContext();
  });

  el.querySelector('#epicFilterDivisao')?.addEventListener('change', (event) => {
    const state = getCompetitorModalState();
    state.selected.divisao = String(event.target.value || '');
    applySelectedCompetitorContext();
  });

  if (!window.__rrCompetitorModalEscapeBound) {
    document.addEventListener('keydown', (event) => {
      const modal = document.getElementById('competitorModal');
      if (event.key === 'Escape' && modal && modal.classList.contains('is-open')) {
        window.closeCompetitorModal();
      }
    });
    window.__rrCompetitorModalEscapeBound = true;
  }

  return el;
}

function openCompetitorModalV2(competitorId, nome, foto, isPremium, allData) {
  const state = getCompetitorModalState();
  state.competitorId = Number(competitorId || 0);
  state.competitorName = String(nome || 'Competidor');
  state.competitorPhoto = String(foto || '');
  state.competitorLevel = normalizeCompetitorLevel(allData?.nivel || '');
  state.claimed = Number(allData?.claimed || 0) === 1;
  state.isPremium = Boolean(isPremium || window.rrIsPremium);
  state.globalStats = buildStatsRecord(allData || {});
  state.contexts = [];
  state.selected = { eventId: 'global', modalidadeId: '', divisao: '' };
  state.activeContext = { mode: 'global', label: getGlobalContextLabel(), stats: state.globalStats };
  state.compareTarget = null;
  state.compareData = null;
  state.compareCandidates = [];
  state.compareSelectorOpen = false;
  state.compareLoading = false;
  state.compareSearchTerm = '';
  state.currentTab = 'stats';
  state.followersCount = 0;
  state.isFollowing = false;
  state.canFollow = false;
  state.followLoading = false;
  state.recentEvents = [];

  const modal = ensureCompetitorModalV2();
  modal.classList.add('is-open');
  modal.setAttribute('aria-hidden', 'false');
  populateCompetitorModalShell();
  setCompetitorModalTab('stats');
  lockCompetitorModalBackground();
  loadCompetitorContexts(state.competitorId);
}

window.switchEpicTab = function(tabName) {
  setCompetitorModalTab(tabName);
};

window.closeCompetitorModal = function() {
  const modal = document.getElementById('competitorModal');
  if (!modal) return;

  modal.classList.remove('is-open');
  modal.setAttribute('aria-hidden', 'true');
  unlockCompetitorModalBackground();

  const state = getCompetitorModalState();
  state.compareSelectorOpen = false;
  state.compareLoading = false;
};

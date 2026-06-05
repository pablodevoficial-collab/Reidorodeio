@extends('admin.layouts.app')
@section('panel')
<style>
  /* Card compacto: tamanho fixo e espaçamento reduzido para muitos competidores */
  .rr-card { position: relative; width: 100%; max-width: 200px; height: 240px; margin: 0 auto; border-radius: 12px; box-shadow: 0 6px 18px rgba(0,0,0,0.18); background: #151515; overflow: hidden; display:flex; flex-direction:column; }
  .rr-card .ds-top { position: absolute; inset: 0 auto auto 0; width: 100%; height: 64px; background: linear-gradient(90deg, #ff7a00, #ffb347); }
  .rr-card .rr-card-content { position: relative; padding: 10px; padding-top: 52px; display:flex; flex-direction:column; justify-content:space-between; align-items:center; }
  .rr-card .avatar-holder { position: relative; margin: -34px auto 6px; width: 76px; height: 76px; border-radius: 50%; box-shadow: 0 0 0 3px #151515, inset 0 0 0 3px #000; background: #fff; overflow: hidden; }
  .rr-card .avatar-holder img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .rr-card .name { text-align: center; color: #fff; padding: 0 6px; }
  .rr-card .name a { color: #fff; text-decoration: none; font-weight: 700; font-size: 14px; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .rr-card .name a:hover { color: #ff8a1a; }
  .rr-card .footer-actions { text-align: center; padding-bottom: 12px; width:100%; }
  .rr-card .footer-actions .btn { min-width: 88px; padding: 6px 10px; font-size: 13px; }
  .rr-card-container { height: 100%; }
  .competitor-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
    align-items: flex-start; /* ensure rows align to top so cards don't shift vertically */
    gap: 12px 14px; /* row gap, column gap */
    margin: 0 auto;
    padding: 0 8px 12px;
    width: 100%;
    max-width: 1400px;
    box-sizing: border-box;
  }

  .competitor-header { display: flex; align-items: center; }
  .competitor-search { margin-left: auto; }
  .competitor-card-col {
    flex: 0 0 200px; /* fixed width to keep uniform grid */
    max-width: 200px;
    width: 200px;
    display: flex;
    align-items: stretch; /* make inner container stretch to same height */
  }
  @media (max-width: 1200px) {
    .competitor-card-col { flex: 0 0 180px; max-width:180px; width:180px; }
  }
  @media (max-width: 768px) {
    .competitor-card-col { flex: 0 0 45%; max-width:45%; width:45%; }
  }
  @media (max-width: 576px) {
    .competitor-card-col { flex-basis: 100%; max-width: 100%; }
  }
</style>
<div class="row mb-3">
  <div class="col-12 competitor-header">
    <form class="competitor-search" method="get" onsubmit="return false;">
      <input id="competitorSearchInput" class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="@lang('Buscar competidor...')" style="min-width:260px; max-width:420px;">
    </form>
  </div>
  </div>

@if($competitors->count())
<div class="competitor-grid">
  @foreach($competitors as $c)
    @php
      $stats = $c->stats ?? (object)[
        'vitorias' => 0, 'empates' => 0, 'derrotas' => 0, 'pontuacao_total' => 0,
        'pontuacao_media' => 0, 'count_boa' => 0, 'count_negativas_total' => 0,
        'last_points' => 0
      ];
      $vitorias = (int)($stats->vitorias ?? 0);
      $empates = (int)($stats->empates ?? 0);
      $derrotas = (int)($stats->derrotas ?? 0);
      $pontuacaoTotal = (int)($stats->pontuacao_total ?? 0);
      $aproveitamento = (float)($c->aproveitamento ?? 0); // 0..100
      $boas = (int)($stats->count_boa ?? 0);
      $negTot = (int)($stats->count_negativas_total ?? 0);
      $totalArmadas = max(1, $boas + $negTot);
      $positivas = (int)(
        ($stats->count_boa ?? 0) + ($stats->count_dobrada ?? 0) + ($stats->count_cabresteou ?? 0) +
        ($stats->count_duas_voltas ?? 0) + ($stats->count_limpou_garupa ?? 0) + ($stats->count_cola ?? 0) +
        ($stats->count_cupim ?? 0) + ($stats->count_top ?? 0) + ($stats->count_pescou ?? 0) + ($stats->count_limpou_cupim_longe ?? 0)
      );
      $negativas = (int)(
        ($stats->count_errou_pescoco ?? 0) + ($stats->count_errou_pata ?? 0) + ($stats->count_errou_top ?? 0) +
        ($stats->count_garupa_neg ?? 0) + ($stats->count_cola_neg ?? 0) + ($stats->count_uma_aspa ?? 0) + ($stats->count_por_cima ?? 0)
      );
      $totalAcoes = max(1, $positivas + $negativas);
      $pctPos = round(($positivas / $totalAcoes) * 100);
      $pctNeg = round(($negativas / $totalAcoes) * 100);
      $pctMedia = (int)round(min(100, max(0, $stats->pontuacao_media ?? 0))); // barra simples
    @endphp
    <div class="competitor-card-col" data-name="{{ strtolower($c->nome ?? '') }}">
      <div class="rr-card-container">
  <div class="rr-card">
          <div class="ds-top"></div>
          <div class="rr-card-content">
            <div class="avatar-holder">
              <img src="{{ $c->foto_url ?? asset('assets/images/logo_icon/favicon.png') }}" alt="{{ $c->nome ?? 'Sem nome' }}" onerror="this.src='{{ asset('assets/images/logo_icon/favicon.png') }}'">
            </div>
            <div class="name">
              <a href="{{ route('admin.competitors.edit', $c->id) }}">{{ $c->nome ?? 'Sem nome' }}</a>
            </div>
            <div class="footer-actions">
              <a href="{{ route('admin.competitors.edit', $c->id) }}" class="btn btn-sm btn--dark">Editar</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endforeach
</div>

@endif
@endsection

@push('script')
<script>
  // (seguido) elemento removido a pedido do usuário

  // Modal de estatísticas completas
  function openStatsModal(id, nome) {
    const modalId = 'rrStatsModal';
    let modal = document.getElementById(modalId);
    if (!modal) {
      modal = document.createElement('div');
      modal.id = modalId;
      modal.className = 'modal fade';
      modal.tabIndex = -1;
      modal.innerHTML = `
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Estatísticas de <span class="rr-name"></span></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="text-center text-muted p-3"><i class="las la-spinner la-spin"></i> Carregando...</div>
            </div>
          </div>
        </div>`;
      document.body.appendChild(modal);
    }
    modal.querySelector('.rr-name').textContent = nome;
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();

    // Busca stats do backend (usaremos o próprio objeto stats da relação via rota de detalhes JSON)
    fetch(`{{ url('admin/competitor-stats') }}/${id}`.replace(/\/$/, '') + '?as=json')
      .then(r => r.json())
      .then(data => {
        if (!data || !data.success) throw new Error('Falha ao carregar');
        const s = data.stats || {};
        
        // Função para formatar nomes dos campos
        const formatFieldName = (field) => {
          const fieldNames = {
            'vitorias': 'Vitórias',
            'derrotas': 'Derrotas', 
            'empates': 'Empates',
            'aproveitamento': 'Aproveitamento de Armadas (%)',
            'pontuacao_total': 'Pontuação Total',
            'last_points': 'Última Pontuação',
            'count_boa': 'Armadas (Boas)',
            'count_negativas_total': 'Armadas (Negativas)',
            'count_errou_pescoco': 'Errou Pescoço',
            'count_errou_pata': 'Errou Pata',
            'count_errou_top': 'Errou Top',
            'count_dobrada': 'Dobrada',
            'count_cabresteou': 'Cabresteou',
            'count_duas_voltas': 'Duas Voltas',
            'count_limpou_garupa': 'Limpou Garupa',
            'count_garupa_neg': 'Garupa (-)',
            'count_cola': 'Cola (+)',
            'count_cola_neg': 'Cola (-)',
            'count_cupim': 'Cupim',
            'count_top': 'Top',
            'count_pescou': 'Pescou',
            'count_uma_aspa': 'Uma Aspa',
            'count_por_cima': 'Por Cima',
            'count_limpou_cupim_longe': 'Limpou Cupim (Longe)'
          };
          return fieldNames[field] || field.replace(/count_|_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        };
        
        // Função para formatar valores
        const formatValue = (field, value) => {
          if (field === 'aproveitamento') {
            // Calcular aproveitamento baseado em armadas
            const boas = s.count_boa || 0;
            const negativas = s.count_negativas_total || 0;
            const total = boas + negativas;
            if (total === 0) return '0%';
            const percentage = ((boas / total) * 100).toFixed(1);
            return `${percentage}%`;
          }
          if (field === 'last_points') {
            return value > 0 ? `+${value}` : value;
          }
          return value;
        };
        
        // Organizar campos por categoria
        const categories = {
          'Resultados Gerais': ['vitorias', 'empates', 'derrotas', 'aproveitamento'],
          'Pontuação': ['pontuacao_total', 'last_points'],
          'Armadas': ['count_boa', 'count_negativas_total'],
          'Erros': ['count_errou_pescoco', 'count_errou_pata', 'count_errou_top', 'count_garupa_neg', 'count_cola_neg', 'count_dobrada', 'count_cabresteou', 'count_duas_voltas', 'count_boi_tirou', 'count_boi_pulou', 'count_por_cima', 'count_queimou_raia', 'count_caiu_do_cavalo', 'count_saiu_enrolado'],
          'Armadas Positivas': ['count_boa'],
          'Destrezas': ['count_limpou_garupa', 'count_cola', 'count_cupim', 'count_limpou_cupim_longe', 'count_pescou', 'count_limpou_top', 'count_limpou_top_mao', 'count_pescou_uma_aspa']
        };
        
        let html = '';
        Object.entries(categories).forEach(([category, fields]) => {
          html += `
            <div class="mb-4">
              <h6 class="text-primary mb-3 border-bottom pb-1">${category}</h6>
              <div class="row g-2">
          `;
          fields.forEach(field => {
            if (s.hasOwnProperty(field)) {
              const value = formatValue(field, s[field]);
              const isNegative = field === 'last_points' && s[field] < 0;
              const isPositive = field === 'last_points' && s[field] > 0;
              const textClass = isNegative ? 'text-danger' : (isPositive ? 'text-success' : 'text-dark');
              
              html += `
                <div class="col-md-6">
                  <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                    <span class="text-muted small">${formatFieldName(field)}</span>
                    <span class="fw-bold ${textClass}">${value}</span>
                  </div>
                </div>
              `;
            }
          });
          html += `
              </div>
            </div>
          `;
        });
        
        modal.querySelector('.modal-body').innerHTML = html;
      })
      .catch(() => {
        modal.querySelector('.modal-body').innerHTML = `<div class="alert alert-danger m-0">Erro ao carregar estatísticas.</div>`;
      });
  }
  window.openStatsModal = openStatsModal;
</script>
<script>
  (function(){
    const input = document.getElementById('competitorSearchInput');
    const cards = Array.from(document.querySelectorAll('.competitor-card-col'));
    const noResults = document.getElementById('noCompetitorResults');

    function setVisibility(match){
      cards.forEach(el=>{
        const name = (el.getAttribute('data-name')||'');
        if(match === '' || name.indexOf(match) !== -1){
          el.style.display = '';
        } else {
          el.style.display = 'none';
        }
      });
      if(noResults){
        const anyVisible = cards.some(el=>el.style.display !== 'none');
        noResults.classList.toggle('d-none', anyVisible);
      }
    }

    function debounce(fn, wait){ let t; return function(...a){ clearTimeout(t); t=setTimeout(()=>fn.apply(this,a), wait); } }

    const handler = debounce(function(){ const q = (input.value||'').trim().toLowerCase(); setVisibility(q); }, 180);
    if(input){ input.addEventListener('input', handler); }
    // Initialize visibility on load (hide "no results" when there are cards)
    try {
      const initialQ = (input && input.value) ? (input.value||'').trim().toLowerCase() : '';
      setVisibility(initialQ);
    } catch (e) {
      // noop
    }
  })();
</script>
@endpush

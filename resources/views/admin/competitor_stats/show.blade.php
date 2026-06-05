@extends('admin.layouts.app')
@section('panel')
<div class="row">
  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-body text-center">
        <div class="rounded-circle overflow-hidden mb-3" style="width:140px;height:140px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;">
          @if($competitor->foto)
            <img src="{{ $competitor->foto_url ?? asset('assets/images/logo_icon/favicon.png') }}" alt="{{ $competitor->nome }}" style="width:100%;height:100%;object-fit:cover;" onerror="this.src='{{ asset('assets/images/logo_icon/favicon.png') }}'">
          @else
            <i class="las la-user" style="font-size:72px;color:#bbb;"></i>
          @endif
        </div>
        <h5 class="mb-1">{{ $competitor->nome ?? 'Sem nome' }}</h5>
        <div class="text-muted mb-3">@lang('Pontuação Total'): {{ $competitor->stats->pontuacao_total ?? 0 }}</div>
        
        <!-- Aproveitamento destacado -->
        @php
          $boas = ($competitor->stats && isset($competitor->stats->count_boa)) ? $competitor->stats->count_boa : 0;
          $negativas = ($competitor->stats && isset($competitor->stats->count_negativas_total)) ? $competitor->stats->count_negativas_total : 0;
          $total = $boas + $negativas;
          $percentage = $total > 0 ? round(($boas / $total) * 100, 1) : 0;
        @endphp
        <div class="alert alert-info text-center mb-3">
          <h4 class="mb-1">{{ $percentage }}%</h4>
          <small>Aproveitamento de Armadas ({{ $boas }}/{{ $total }} armadas)</small>
        </div>
        
        <div class="d-flex justify-content-center gap-3">
          <div><strong>{{ $competitor->stats->vitorias ?? 0 }}</strong><div class="text-muted small">@lang('Vitórias')</div></div>
          <div><strong>{{ $competitor->stats->empates ?? 0 }}</strong><div class="text-muted small">@lang('Empates')</div></div>
          <div><strong>{{ $competitor->stats->derrotas ?? 0 }}</strong><div class="text-muted small">@lang('Derrotas')</div></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-header"><h5 class="mb-0">@lang('Estatísticas detalhadas')</h5></div>
      <div class="card-body">
        <div class="row g-3">
          @php
            $stats = $competitor->stats;
            
            // Organizar contadores por categoria
            $categories = [
              'Resultados Gerais' => [
                'vitorias' => 'Vitórias',
                'empates' => 'Empates', 
                'derrotas' => 'Derrotas'
              ],
              'Pontuação' => [
                'pontuacao_total' => 'Pontuação Total',
                'pontuacao_media' => 'Pontuação Média',
                'last_points' => 'Última Pontuação'
              ],
              'Armadas' => [
                'count_boa' => 'Armadas (Boas)',
                'count_negativas_total' => 'Armadas (Negativas)'
              ],
              'Erros' => [
                'count_errou_pescoco' => 'Pescoço',
                'count_errou_pata' => 'Pata',
                'count_errou_top' => 'Top',
                'count_garupa_neg' => 'Garupa',
                'count_cola_neg' => 'Cola',
                'count_dobrada' => 'Dobrada',
                'count_cabresteou' => 'Cabresteou',
                'count_duas_voltas' => 'Duas Voltas',
                'count_boi_tirou' => 'Boi tirou',
                'count_boi_pulou' => 'Boi pulou',
                'count_por_cima' => 'Por cima',
                'count_queimou_raia' => 'Queimou raia',
                'count_caiu_do_cavalo' => 'Caiu do cavalo',
                'count_saiu_enrolado' => 'Saiu enrolado'
              ],
              'Armadas Positivas' => [
                'count_boa' => 'Boa'
              ],
              'Destrezas' => [
                'count_limpou_garupa' => 'Limpou garupa',
                'count_cola' => 'Limpou cola',
                'count_cupim' => 'Limpou cupim',
                'count_limpou_cupim_longe' => 'Limpou cupim (longe)',
                'count_pescou' => 'Pescou',
                'count_limpou_top' => 'Limpou top',
                'count_limpou_top_mao' => 'Limpou top (mão)',
                'count_pescou_uma_aspa' => 'Pescou uma aspa'
              ]
            ];
          @endphp

          @foreach($categories as $categoryName => $counters)
            <div class="col-12">
              <h6 class="text-primary mb-3 border-bottom pb-1">{{ $categoryName }}</h6>
              <div class="row g-2">
                @foreach($counters as $field => $label)
                  <div class="col-6 col-md-4 col-lg-3">
                    <div class="border rounded p-2 h-100">
                      <div class="text-muted small">{{ $label }}</div>
                      @php
                        $value = ($stats && isset($stats->$field)) ? $stats->$field : 0;
                        $textClass = '';
                        if ($field === 'last_points') {
                          $textClass = $value > 0 ? 'text-success' : ($value < 0 ? 'text-danger' : '');
                          $value = $value > 0 ? '+' . $value : $value;
                        }
                      @endphp
                      <div class="h4 mb-0 {{ $textClass }}">{{ $value }}</div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @endforeach
          
          <!-- Aproveitamento de Armadas especial -->
          <div class="col-12">
            <h6 class="text-primary mb-3 border-bottom pb-1">Aproveitamento de Armadas</h6>
            <div class="row g-2">
              <div class="col-6 col-md-4 col-lg-3">
                <div class="border rounded p-2 h-100">
                  <div class="text-muted small">Aproveitamento de Armadas</div>
                  @php
                    $boas = ($stats && isset($stats->count_boa)) ? $stats->count_boa : 0;
                    $negativas = ($stats && isset($stats->count_negativas_total)) ? $stats->count_negativas_total : 0;
                    $total = $boas + $negativas;
                    $percentage = $total > 0 ? round(($boas / $total) * 100, 1) : 0;
                  @endphp
                  <div class="h4 mb-0 text-info">{{ $percentage }}%</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        </div>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">@lang('Últimas ações')</h5>
        <a href="{{ route('admin.live_transmission.competitor_scoring_history', ['competitor_id' => $competitor->id, 'limit' => 100]) }}" class="btn btn-sm btn--secondary" target="_blank">@lang('Ver completo')</a>
      </div>
      <div class="card-body" style="max-height: 340px; overflow-y: auto;">
        @forelse($logs as $log)
          <div class="d-flex justify-content-between border-bottom py-2">
            <div>{{ $log->action_description }}</div>
            <div>
              <span class="badge {{ $log->points > 0 ? 'bg-success' : ($log->points < 0 ? 'bg-danger' : 'bg-secondary') }}">{{ $log->points > 0 ? '+' : '' }}{{ $log->points }}</span>
              <small class="text-muted">{{ $log->scored_at->format('d/m/Y H:i:s') }}</small>
            </div>
          </div>
        @empty
          <div class="text-muted">@lang('Sem ações recentes.')</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection

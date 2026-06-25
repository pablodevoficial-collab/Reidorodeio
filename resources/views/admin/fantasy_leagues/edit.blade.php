@extends('admin.layouts.app')

@section('panel')

    <div class="fantasy-edit-wrapper">
        <div class="fantasy-edit-card">
            <div class="fantasy-edit-header">
                <h5><i class="las la-edit"></i> @lang('Editar Fantasy League')</h5>
            </div>

            <form method="post" action="{{ route('admin.fantasy_leagues.update', $fantasyLeague) }}" enctype="multipart/form-data" id="fantasyLeagueEditForm">
                @csrf
                @method('PUT')
                
                @if($errors->any())
                    <div class="alert alert-danger m-4">
                        <h6 class="alert-heading"><i class="las la-exclamation-triangle"></i> @lang('Erros de Validação')</h6>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="fantasy-edit-body">
                    <!-- Seção: Informações Básicas -->
                    <div class="fantasy-section">
                        <div class="fantasy-section-title">
                            <i class="las la-info-circle"></i>
                            @lang('Informações Básicas')
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-flag"></i> @lang('Rodeio')</label>
                                    <select name="rodeio_id" id="rodeio_id" class="fantasy-form-control" required>
                                        <option value="">@lang('Selecione um rodeio')</option>
                                        @foreach(($rodeios ?? []) as $r)
                                            <option value="{{ $r->id }}" @selected(old('rodeio_id', $fantasyLeague->rodeio_id) == $r->id)>{{ $r->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-list"></i> @lang('Modalidade')</label>
                                    <select name="modalidade_id" id="modalidade_id" class="fantasy-form-control" required>
                                        <option value="">@lang('Selecione uma modalidade')</option>
                                        @foreach(($modalidades ?? []) as $m)
                                            <option value="{{ $m->id }}"
                                                data-rodeio="{{ $m->rodeio_id }}"
                                                data-tem-divisoes="{{ $m->tem_divisoes ? '1' : '0' }}"
                                                data-divisoes='@json($m->divisoes_nomes ?? [])'
                                                @selected(old('modalidade_id', $fantasyLeague->modalidade_id) == $m->id)
                                            >{{ $m->nome }}</option>
                                        @endforeach
                                    </select>
                                    <small class="fantasy-form-help"><i class="las la-link"></i> @lang('Este bolão sempre segue a modalidade definida aqui.')</small>
                                </div>
                            </div>

                            <div class="col-md-6" id="divisao_wrap" style="display:none;">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-layer-group"></i> @lang('Divisão')</label>
                                    <select name="divisao" id="divisao" class="fantasy-form-control">
                                        <option value="">@lang('Selecione uma divisão')</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="fantasy-context-card" id="fantasyContextCard">
                                    <div class="fantasy-context-card__title">@lang('Contexto atual do bolão')</div>
                                    <div class="fantasy-context-card__chips">
                                        <div class="fantasy-context-chip">
                                            <span class="fantasy-context-chip__label">@lang('Rodeio')</span>
                                            <span class="fantasy-context-chip__value" id="context_rodeio">@lang('Selecione um rodeio')</span>
                                        </div>
                                        <div class="fantasy-context-chip">
                                            <span class="fantasy-context-chip__label">@lang('Modalidade')</span>
                                            <span class="fantasy-context-chip__value" id="context_modalidade">@lang('Selecione uma modalidade')</span>
                                        </div>
                                        <div class="fantasy-context-chip">
                                            <span class="fantasy-context-chip__label">@lang('Divisão')</span>
                                            <span class="fantasy-context-chip__value" id="context_divisao">@lang('Sem divisão')</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-tag"></i> @lang('Nome da Liga')</label>
                                    <input type="text" name="name" value="{{ old('name', $fantasyLeague->name) }}" class="fantasy-form-control" placeholder="Ex: Copa Elite 2026" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-medal"></i> @lang('Categoria')</label>
                                    <input type="text" name="category" value="{{ old('category', $fantasyLeague->category) }}" class="fantasy-form-control" placeholder="Ex: ouro, prata, bronze" required>
                                    <small class="fantasy-form-help"><i class="las la-lightbulb"></i> @lang('Define o nível da competição')</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-star"></i> @lang('Patrocinador organizador')</label>
                                    <select name="organizer_sponsor_id" class="fantasy-form-control">
                                        <option value="">@lang('Usar logo padrão do rodeio')</option>
                                        @foreach(($sponsors ?? []) as $sponsor)
                                            <option value="{{ $sponsor->id }}" @selected(old('organizer_sponsor_id', $fantasyLeague->organizer_sponsor_id) == $sponsor->id)>{{ $sponsor->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="fantasy-form-help"><i class="las la-info-circle"></i> @lang('Quando definido, o card da arena usa a logo do patrocinador no lugar da padrão.')</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-image"></i> @lang('Imagem do bolão')</label>
                                    <div class="fantasy-context-card">
                                        <div class="fantasy-context-card__title">@lang('Origem da imagem')</div>
                                        <div class="fantasy-context-card__chips">
                                            <div class="fantasy-context-chip" style="min-width: 100%;">
                                                <span class="fantasy-context-chip__value">@lang('Este bolão usa sempre a logo atual do rodeio vinculado.')</span>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="fantasy-form-help"><i class="las la-info-circle"></i> @lang('Uploads manuais e remoção de imagem foram desativados.')</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seção: Configuração Financeira -->
                    <div class="fantasy-section">
                        <div class="fantasy-section-title">
                            <i class="las la-dollar-sign"></i>
                            @lang('Configuração Financeira')
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-ticket-alt"></i> @lang('Tipo de Entrada')</label>
                                    @php
                                        $oldMode = old('entry_mode');
                                        $fixedModes = ['premium', 'free', '0.01', '20', '50', '100', 'custom'];
                                        $customEntryPrice = old('custom_entry_price');
                                        if (!$oldMode) {
                                            $oldMode = $fantasyLeague->is_premium ? 'premium' : (((float) $fantasyLeague->price <= 0) ? 'free' : (string) floatval($fantasyLeague->price));
                                        }
                                        if (!in_array($oldMode, $fixedModes, true)) {
                                            $customEntryPrice = $customEntryPrice !== null && $customEntryPrice !== '' ? $customEntryPrice : $oldMode;
                                            $oldMode = 'custom';
                                        }
                                    @endphp
                                    <select name="entry_mode" id="entry_mode" class="fantasy-form-control" required>
                                        <option value="free" @selected($oldMode === 'free')>@lang('Gratuito aberto a todos')</option>
                                        <option value="premium" @selected($oldMode === 'premium')>🌟 @lang('Premium (Grátis para assinantes)')</option>
                                        <option value="0.01" @selected($oldMode === '0.01')>🧪 @lang('Teste - R$ 0,01')</option>
                                        <option value="20" @selected($oldMode === '20')>💰 @lang('Pago - R$ 20,00')</option>
                                        <option value="50" @selected($oldMode === '50')>💰 @lang('Pago - R$ 50,00')</option>
                                        <option value="100" @selected($oldMode === '100')>💰 @lang('Pago - R$ 100,00')</option>
                                        <option value="custom" @selected($oldMode === 'custom')>✍️ @lang('Personalizado')</option>
                                    </select>
                                    <small class="fantasy-form-help"><i class="las la-lock"></i> @lang('Escolha um valor fixo ou informe um valor personalizado')</small>
                                </div>
                            </div>

                            <div class="col-md-3" id="custom_entry_price_wrap" style="display:none;">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-coins"></i> @lang('Valor Personalizado')</label>
                                    <input type="number" name="custom_entry_price" id="custom_entry_price" value="{{ $customEntryPrice }}" step="0.01" min="0.01" max="10000" class="fantasy-form-control" placeholder="Ex: 35,00">
                                    <small class="fantasy-form-help"><i class="las la-info-circle"></i> @lang('Informe o valor de entrada do bolão')</small>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-percentage"></i> @lang('Lucro da Casa (%)')</label>
                                    <input type="number" name="house_cut_percent" id="house_cut_percent" value="{{ old('house_cut_percent', $fantasyLeague->house_cut_percent ?? 30) }}" step="0.01" min="0" max="50" class="fantasy-form-control">
                                    <small class="fantasy-form-help" id="house_cut_help"><i class="las la-info-circle"></i> @lang('Para bolão pago: entre 20% e 50%')</small>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-users"></i> @lang('Máx. Usuários')</label>
                                    <input type="number" name="max_users" id="max_users_input" value="{{ old('max_users', $fantasyLeague->max_users) }}" min="1" class="fantasy-form-control" placeholder="Ilimitado">
                                    <small class="fantasy-form-help"><i class="las la-info-circle"></i> @lang('Obrigatório para cálculo do prêmio')</small>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-medal"></i> @lang('Pagar até o Top')</label>
                                    <input type="number" name="paid_positions_override" id="paid_positions_override_input" value="{{ old('paid_positions_override', $fantasyLeague->paid_positions_override ?? (!empty($fantasyLeague->prize_distribution) ? count((array) $fantasyLeague->prize_distribution) : null)) }}" min="1" class="fantasy-form-control" placeholder="Automático">
                                    <small class="fantasy-form-help"><i class="las la-info-circle"></i> @lang('Opcional. Só vale ao bater a meta; abaixo da meta recalcula automático pelas entradas reais')</small>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-clock"></i> @lang('Fecha em')</label>
                                    @php
                                        $closeVal = old('closes_at');
                                        if ($closeVal) {
                                            $closeVal = \Carbon\Carbon::parse($closeVal)->format('Y-m-d\\TH:i');
                                        } elseif ($fantasyLeague->closes_at) {
                                            $closeVal = \Carbon\Carbon::parse($fantasyLeague->closes_at)->format('Y-m-d\\TH:i');
                                        } else {
                                            $closeVal = '';
                                        }
                                    @endphp
                                    <input type="datetime-local" name="closes_at" value="{{ $closeVal }}" class="fantasy-form-control">
                                    <small class="fantasy-form-help"><i class="las la-info-circle"></i> @lang('Deixe vazio para manter aberto')</small>
                                </div>
                            </div>
                            
                            <!-- Deadline de Inscrições -->
                            <div class="col-lg-6">
                                <div class="fantasy-form-group">
                                    <label class="fantasy-form-label">
                                        <i class="las la-hourglass-half"></i> @lang('Deadline de Inscrições')
                                    </label>
                                    @php
                                        $deadlineVal = old('registration_deadline');
                                        if ($deadlineVal) {
                                            $deadlineVal = \Carbon\Carbon::parse($deadlineVal)->format('Y-m-d\\TH:i');
                                        } elseif ($fantasyLeague->registration_deadline) {
                                            $deadlineVal = \Carbon\Carbon::parse($fantasyLeague->registration_deadline)->format('Y-m-d\\TH:i');
                                        } else {
                                            $deadlineVal = '';
                                        }
                                    @endphp
                                    <input type="datetime-local" name="registration_deadline" value="{{ $deadlineVal }}" class="fantasy-form-control">
                                    <small class="fantasy-form-help"><i class="las la-info-circle"></i> @lang('Após essa data, não será possível criar novas equipes')</small>
                                </div>
                            </div>
                            
                            <!-- Permitir Inscrições Tardias -->
                            <div class="col-lg-6">
                                <div class="fantasy-form-group">
                                    <label class="fantasy-form-label">
                                        <i class="las la-unlock"></i> @lang('Permitir Inscrições Tardias')
                                    </label>
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="allow_late_registration" value="0">
                                        <input type="checkbox" name="allow_late_registration" value="1" id="allow_late_registration"
                                            class="form-check-input" @checked(old('allow_late_registration', $fantasyLeague->allow_late_registration))>
                                        <label class="form-check-label" for="allow_late_registration">
                                            @lang('Ignorar deadline e permitir inscrições a qualquer momento')
                                        </label>
                                    </div>
                                    <small class="fantasy-form-help text-warning"><i class="las la-exclamation-triangle"></i> @lang('Cuidado: isso pode comprometer a fairness da liga')</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card de Prêmio Total Calculado -->
                        <div class="prize-calculation-card" id="prizeCalculationCard" style="display:none;">
                            <div class="prize-calculation-header">
                                <i class="las la-trophy"></i>
                                <span>@lang('Cálculo do Prêmio Total')</span>
                            </div>
                            <div class="prize-calculation-body">
                                <div class="prize-calculation-row">
                                    <span class="prize-label">Arrecadação Total:</span>
                                    <span class="prize-value" id="totalCollection">R$ 0,00</span>
                                    <span class="prize-formula">(<span id="entryPrice">R$ 0</span> × <span id="maxUsersDisplay">0</span> usuários)</span>
                                </div>
                                <div class="prize-calculation-row">
                                    <span class="prize-label">Lucro da Casa:</span>
                                    <span class="prize-value prize-negative" id="houseCutValue">- R$ 0,00</span>
                                    <span class="prize-formula">(<span id="houseCutDisplay">0</span>%)</span>
                                </div>
                                <div class="prize-calculation-divider"></div>
                                <div class="prize-calculation-row prize-total-row">
                                    <span class="prize-label"><i class="las la-award"></i> PRÊMIO TOTAL:</span>
                                    <span class="prize-value prize-highlight" id="totalPrize">R$ 0,00</span>
                                </div>
                            </div>
                            <input type="hidden" name="total_prize" id="total_prize_input" value="{{ $fantasyLeague->total_prize ?? 0 }}">
                        </div>

                        <div class="prize-distribution-simulator" id="prizeDistributionSimulator" style="display:none;">
                            <div class="prize-distribution-header">
                                <i class="las la-chart-bar"></i>
                                <span>@lang('Simulador de Premiação Dinâmica')</span>
                            </div>
                            <div class="prize-distribution-body">
                                <div class="prize-tier-info">
                                    <div class="prize-tier-label">INSCRITOS / PAGOS</div>
                                    <div class="prize-tier-value">
                                        <span id="simulatorEntrants">0</span> inscritos →
                                        <span id="simulatorPaidPositions">0</span> posições pagas
                                    </div>
                                    <div class="prize-tier-desc">
                                        <span id="simulatorPercentage">0%</span> dos participantes serão premiados
                                    </div>
                                </div>

                                <div id="simulatorTableWrap">
                                    <table class="prize-table">
                                        <thead>
                                            <tr>
                                                <th>Posição</th>
                                                <th>% do Prêmio</th>
                                                <th>Valor</th>
                                            </tr>
                                        </thead>
                                        <tbody id="simulatorTableBody"></tbody>
                                    </table>
                                </div>

                                <div class="prize-empty-state" id="simulatorEmptyState" style="display:none;">
                                    <i class="las la-calculator"></i>
                                    <p>Configure o valor de entrada e máximo de usuários para ver a simulação</p>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="prize_distribution" id="prize_distribution_input" value="{{ old('prize_distribution', !empty($fantasyLeague->prize_distribution) ? json_encode($fantasyLeague->prize_distribution) : '') }}">

                        <div id="prizeManualEditor" style="display:none; margin-top:1.5rem; background:linear-gradient(135deg, rgba(249,115,22,0.12) 0%, rgba(59,130,246,0.08) 100%); border:2px solid rgba(249,115,22,0.22); border-radius:12px; overflow:hidden;">
                            <div style="padding:1rem 1.25rem; background:rgba(15,23,42,0.35); border-bottom:1px solid rgba(249,115,22,0.15); display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
                                <div>
                                    <div style="font-weight:800; color:#f8fafc; display:flex; align-items:center; gap:.5rem;">
                                        <i class="las la-sliders-h"></i>
                                        Distribuição manual por posição
                                    </div>
                                    <div style="font-size:.9rem; color:#cbd5e1; margin-top:.2rem;">Defina quanto cada posição recebe. Se não editar, o sistema continua automático.</div>
                                </div>
                                <button type="button" id="resetPrizeDistributionBtn" class="btn btn-sm btn-outline-warning">Usar automático</button>
                            </div>
                            <div style="padding:1rem 1.25rem;">
                                <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1rem;">
                                    <div style="padding:.65rem .9rem; border-radius:10px; background:rgba(15,23,42,0.38); color:#e2e8f0;">
                                        <strong id="manualPrizePositionsLabel">Top 0</strong>
                                    </div>
                                    <div style="padding:.65rem .9rem; border-radius:10px; background:rgba(15,23,42,0.38); color:#e2e8f0;">
                                        Alocado: <strong id="manualPrizeAllocated">R$ 0,00</strong>
                                    </div>
                                    <div id="manualPrizeDiffBox" style="padding:.65rem .9rem; border-radius:10px; background:rgba(15,23,42,0.38); color:#e2e8f0;">
                                        Diferença: <strong id="manualPrizeDifference">R$ 0,00</strong>
                                    </div>
                                </div>
                                <div id="manualPrizeRows" style="display:grid; gap:.75rem;"></div>
                                <small style="display:block; margin-top:.75rem; color:#cbd5e1;">A soma dos valores pode ficar abaixo do prêmio total, mas não pode passar dele.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Seção: Configurações de Recompensa Premium -->
                    <div class="fantasy-section" id="premium_reward_wrap" style="display:none;">
                        <div class="fantasy-section-title">
                            <i class="las la-gift"></i>
                            @lang('Configuração de Recompensa Premium')
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-award"></i> @lang('Tipo de Recompensa')</label>
                                    @php
                                        $reward = old('reward_mode', $fantasyLeague->reward_mode ?? 'points');
                                    @endphp
                                    <select name="reward_mode" id="reward_mode" class="fantasy-form-control">
                                        <option value="points" @selected($reward === 'points')>⭐ @lang('Apenas pontos (sem prêmio)')</option>
                                        <option value="manual_prize" @selected($reward === 'manual_prize')>🏆 @lang('Premiação manual')</option>
                                    </select>
                                    <small class="fantasy-form-help"><i class="las la-info-circle"></i> @lang('Exclusivo para bolão Premium')</small>
                                </div>
                            </div>

                            <div class="col-md-6" id="prize_type_wrap" style="display:none;">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-gift"></i> @lang('Formato do Premio')</label>
                                    @php
                                        $prizeType = old('prize_type', $fantasyLeague->prize_type ?? 'money');
                                    @endphp
                                    <select name="prize_type" id="prize_type" class="fantasy-form-control">
                                        <option value="money" @selected($prizeType === 'money')>@lang('Dinheiro')</option>
                                        <option value="physical" @selected($prizeType === 'physical')>@lang('Premio fisico')</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6" id="premium_prize_wrap" style="display:none;">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-money-bill-wave"></i> @lang('Valor do Prêmio (R$)')</label>
                                    <input type="number" name="manual_prize_pool" id="manual_prize_pool" value="{{ old('manual_prize_pool', $fantasyLeague->manual_prize_pool) }}" step="0.01" min="0" class="fantasy-form-control" placeholder="0,00">
                                    <small class="fantasy-form-help"><i class="las la-user-cog"></i> @lang('Definido manualmente pelo admin')</small>
                                </div>
                            </div>
                            <div class="col-md-12" id="prize_description_wrap" style="display:none;">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-align-left"></i> @lang('Resumo do Premio')</label>
                                    <textarea name="prize_description" id="prize_description" rows="2" maxlength="500" class="fantasy-form-control" placeholder="Ex: Top 5 com premios fisicos exclusivos">{{ old('prize_description', $fantasyLeague->prize_description) }}</textarea>
                                    <small class="fantasy-form-help"><i class="las la-info-circle"></i> @lang('Opcional. Se ficar vazio, o sistema usa a lista de premios fisicos')</small>
                                </div>
                            </div>

                            <div class="col-md-12" id="physical_prizes_wrap" style="display:none;">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-list-ol"></i> @lang('Premios fisicos por posicao')</label>
                                    <div id="physical_prizes_rows" style="display:grid; gap:.75rem;"></div>
                                    <small class="fantasy-form-help"><i class="las la-medal"></i> @lang('A quantidade segue o campo Pagar ate o Top. Ex: Top 5 cria 5 premios para preencher.')</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seção: Configurações Avançadas -->
                    <div class="fantasy-section">
                        <div class="fantasy-section-title">
                            <i class="las la-cog"></i>
                            @lang('Configurações Avançadas')
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-calendar"></i> @lang('Season ID')</label>
                                    <input type="number" name="season_id" value="{{ old('season_id', $fantasyLeague->season_id) }}" min="1" class="fantasy-form-control" placeholder="Opcional">
                                    <small class="fantasy-form-help"><i class="las la-info-circle"></i> @lang('Identificador da temporada')</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="fantasy-form-group">
                                    <label><i class="las la-toggle-on"></i> @lang('Status')</label>
                                    <div class="fantasy-checkbox-wrapper">
                                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $fantasyLeague->is_active)) id="is_active">
                                        <span>@lang('Liga ativa')</span>
                                    </div>
                                    <small class="fantasy-form-help"><i class="las la-info-circle"></i> @lang('Desmarque para manter inativa')</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fantasy-submit-footer">
                    <button type="submit" class="fantasy-submit-btn">
                        <i class="las la-save"></i> @lang('Salvar Alterações')
                    </button>
                </div>
            </form>

            <div class="fantasy-delete-footer">
                <form method="POST" action="{{ route('admin.fantasy_leagues.destroy', $fantasyLeague) }}" onsubmit="return confirm(@js(__('Tem certeza que deseja excluir esta liga?')));">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="fantasy-delete-btn">
                        <i class="las la-trash"></i> @lang('Excluir Liga Permanentemente')
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
(function () {
    "use strict";

    const rodeio = document.getElementById('rodeio_id');
    const modalidade = document.getElementById('modalidade_id');
    const divisaoWrap = document.getElementById('divisao_wrap');
    const divisao = document.getElementById('divisao');
    const contextRodeio = document.getElementById('context_rodeio');
    const contextModalidade = document.getElementById('context_modalidade');
    const contextDivisao = document.getElementById('context_divisao');
    const entryMode = document.getElementById('entry_mode');
    const customEntryPriceWrap = document.getElementById('custom_entry_price_wrap');
    const customEntryPriceInput = document.getElementById('custom_entry_price');
    const houseCut = document.getElementById('house_cut_percent');
    const houseHelp = document.getElementById('house_cut_help');
    const premiumRewardWrap = document.getElementById('premium_reward_wrap');
    const premiumPrizeWrap = document.getElementById('premium_prize_wrap');
    const rewardMode = document.getElementById('reward_mode');
    const manualPrize = document.getElementById('manual_prize_pool');
    const prizeTypeWrap = document.getElementById('prize_type_wrap');
    const prizeType = document.getElementById('prize_type');
    const prizeDescriptionWrap = document.getElementById('prize_description_wrap');
    const prizeDescription = document.getElementById('prize_description');
    const physicalPrizesWrap = document.getElementById('physical_prizes_wrap');
    const physicalPrizesRows = document.getElementById('physical_prizes_rows');
    const prizeDistributionInput = document.getElementById('prize_distribution_input');
    const prizeManualEditor = document.getElementById('prizeManualEditor');
    const manualPrizeRows = document.getElementById('manualPrizeRows');
    const manualPrizePositionsLabel = document.getElementById('manualPrizePositionsLabel');
    const manualPrizeAllocated = document.getElementById('manualPrizeAllocated');
    const manualPrizeDifference = document.getElementById('manualPrizeDifference');
    const manualPrizeDiffBox = document.getElementById('manualPrizeDiffBox');
    const resetPrizeDistributionBtn = document.getElementById('resetPrizeDistributionBtn');
    const editForm = document.getElementById('fantasyLeagueEditForm');
    const editSubmitBtn = editForm ? editForm.querySelector('.fantasy-submit-btn') : null;
    const initialPrizeDistribution = @json(($oldPrizeDistribution = old('prize_distribution')) ? (json_decode($oldPrizeDistribution, true) ?: []) : ($fantasyLeague->prize_distribution ?? []));
    const initialPhysicalPrizes = @json(old('physical_prizes', $fantasyLeague->prize_items ?? []));
    let isSubmitting = false;
    let manualPrizeAmounts = {};
    let hasInitialManualDistribution = Object.keys(initialPrizeDistribution || {}).length > 0;
    let manualPrizeDirty = hasInitialManualDistribution;
    let manualPrizeTouched = false;

    // Evita prompt de "dados não salvos" durante submit real
    window.addEventListener('beforeunload', function(event) {
        if (window.__rrFantasySubmitting === true) {
            event.stopImmediatePropagation();
            delete event.returnValue;
            return;
        }
    }, true);

    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            if (!validateManualPrizeBeforeSubmit()) {
                e.preventDefault();
                return;
            }
            if (isSubmitting) {
                e.preventDefault();
                return;
            }
            isSubmitting = true;
            window.__rrFantasySubmitting = true;
            window.onbeforeunload = null;

            if (editSubmitBtn) {
                editSubmitBtn.disabled = true;
                editSubmitBtn.innerHTML = '<i class="las la-spinner la-spin"></i> Salvando...';
            }
        });
    }

    function filterModalidades() {
        const rodeioId = rodeio ? rodeio.value : '';
        if (!modalidade) return;
        modalidade.disabled = !rodeioId;
        const opts = Array.from(modalidade.options);
        opts.forEach(opt => {
            if (!opt.value) return;
            const r = opt.getAttribute('data-rodeio');
            opt.hidden = !!rodeioId && r !== rodeioId;
        });
        if (modalidade.selectedOptions[0] && modalidade.selectedOptions[0].hidden) {
            modalidade.value = '';
        }
        syncLeagueContext();
        syncDivisoes();
    }

    function syncDivisoes() {
        if (!divisaoWrap || !divisao || !modalidade) return;
        const opt = modalidade.selectedOptions[0];
        const tem = opt && opt.getAttribute('data-tem-divisoes') === '1';
        const list = opt ? (JSON.parse(opt.getAttribute('data-divisoes') || '[]') || []) : [];
        const current = String(@json(old('divisao', $fantasyLeague->divisao)));

        divisao.innerHTML = '<option value="">@lang('Selecione uma divisão')</option>';
        if (tem && Array.isArray(list) && list.length) {
            list.forEach(d => {
                const o = document.createElement('option');
                o.value = String(d);
                o.textContent = String(d);
                if (current === String(d)) o.selected = true;
                divisao.appendChild(o);
            });
            divisaoWrap.style.display = '';
        } else {
            divisaoWrap.style.display = 'none';
            divisao.value = '';
        }
        syncLeagueContext();
    }

    function syncLeagueContext() {
        const rodeioOpt = rodeio && rodeio.selectedOptions ? rodeio.selectedOptions[0] : null;
        const modalidadeOpt = modalidade && modalidade.selectedOptions ? modalidade.selectedOptions[0] : null;
        const divisaoValue = divisao ? String(divisao.value || '').trim() : '';

        if (contextRodeio) {
            contextRodeio.textContent = rodeioOpt && rodeioOpt.value ? rodeioOpt.textContent.trim() : '@lang('Selecione um rodeio')';
        }
        if (contextModalidade) {
            contextModalidade.textContent = modalidadeOpt && modalidadeOpt.value ? modalidadeOpt.textContent.trim() : '@lang('Selecione uma modalidade')';
        }
        if (contextDivisao) {
            contextDivisao.textContent = divisaoValue !== '' ? divisaoValue : '@lang('Sem divisão')';
        }
    }

    function getPhysicalPrizePositions() {
        const paidInput = document.getElementById('paid_positions_override_input');
        const maxInput = document.getElementById('max_users_input');
        const override = paidInput ? parseInt(paidInput.value, 10) || 0 : 0;
        const maxUsers = maxInput ? parseInt(maxInput.value, 10) || 0 : 0;

        if (override > 0) return override;
        if (maxUsers > 0) return getPaidPositions(maxUsers);
        return 1;
    }

    function escapeHtmlAttr(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function renderPhysicalPrizeRows() {
        if (!physicalPrizesWrap || !physicalPrizesRows) return;

        const isPhysicalPrize = prizeType && prizeType.value === 'physical';
        const mode = entryMode ? String(entryMode.value || '') : '';
        const isManualReward = mode === 'free' || (mode === 'premium' && rewardMode && rewardMode.value === 'manual_prize');

        if (!isManualReward || !isPhysicalPrize) {
            physicalPrizesWrap.style.display = 'none';
            physicalPrizesRows.innerHTML = '';
            return;
        }

        physicalPrizesWrap.style.display = '';

        const currentValues = {};
        physicalPrizesRows.querySelectorAll('[data-physical-prize-position]').forEach(function(input) {
            currentValues[input.dataset.physicalPrizePosition] = input.value;
        });

        const positions = Math.max(1, getPhysicalPrizePositions());
        let html = '';

        for (let position = 1; position <= positions; position++) {
            const value = currentValues[position]
                ?? initialPhysicalPrizes[position]
                ?? initialPhysicalPrizes[String(position)]
                ?? '';

            html += `
                <div style="display:grid; grid-template-columns: 110px 1fr; gap:.75rem; align-items:center; padding:.85rem 1rem; border-radius:10px; background:rgba(15,23,42,0.32); border:1px solid rgba(249,115,22,0.14);">
                    <div style="font-weight:800; color:#f8fafc;">${position}º Lugar</div>
                    <input type="text" name="physical_prizes[${position}]" maxlength="500" class="fantasy-form-control" data-physical-prize-position="${position}" value="${escapeHtmlAttr(value)}" placeholder="Ex: Fivela personalizada">
                </div>
            `;
        }

        physicalPrizesRows.innerHTML = html;
    }

    function syncPricingUI(){
        const mode = entryMode ? String(entryMode.value || '') : '';
        const isPremium = mode === 'premium';
        const isFree = mode === 'free';
        const isCustom = mode === 'custom';
        const isManualReward = isFree || (isPremium && rewardMode && rewardMode.value === 'manual_prize');
        const isPhysicalPrize = isManualReward && prizeType && prizeType.value === 'physical';

        if (customEntryPriceWrap) customEntryPriceWrap.style.display = isCustom ? '' : 'none';
        if (customEntryPriceInput) {
            if (isCustom) {
                customEntryPriceInput.removeAttribute('disabled');
            } else {
                customEntryPriceInput.setAttribute('disabled', 'disabled');
            }
        }

        if (premiumRewardWrap) premiumRewardWrap.style.display = (isPremium || isFree) ? '' : 'none';
        if (prizeTypeWrap) prizeTypeWrap.style.display = isManualReward ? '' : 'none';
        if (premiumPrizeWrap) premiumPrizeWrap.style.display = (isManualReward && !isPhysicalPrize) ? '' : 'none';
        if (prizeDescriptionWrap) prizeDescriptionWrap.style.display = isPhysicalPrize ? '' : 'none';
        renderPhysicalPrizeRows();

        // Disable/enable premium fields to prevent them from being submitted
        if (rewardMode) {
            if (isPremium || isFree) {
                rewardMode.removeAttribute('disabled');
                if (isFree) rewardMode.value = 'manual_prize';
            } else {
                rewardMode.setAttribute('disabled', 'disabled');
            }
        }

        if (prizeType) {
            if (isManualReward) {
                prizeType.removeAttribute('disabled');
            } else {
                prizeType.setAttribute('disabled', 'disabled');
            }
        }
        
        if (manualPrize) {
            if (isManualReward && !isPhysicalPrize) {
                manualPrize.removeAttribute('disabled');
            } else {
                manualPrize.setAttribute('disabled', 'disabled');
            }
        }

        if (prizeDescription) {
            if (isPhysicalPrize) {
                prizeDescription.removeAttribute('disabled');
            } else {
                prizeDescription.setAttribute('disabled', 'disabled');
            }
        }

        if (houseCut) {
            if (isPremium || isFree) {
                houseCut.value = '0';
                houseCut.setAttribute('readonly', 'readonly');
            } else {
                houseCut.removeAttribute('readonly');
                if (!houseCut.value) houseCut.value = '30';
            }
        }

        if (houseHelp) {
            const isTestMode = mode === '0.01';
            if (isPremium) {
                houseHelp.innerHTML = '<i class="las la-info-circle"></i> Não se aplica a ligas Premium (grátis).';
            } else if (isFree) {
                houseHelp.innerHTML = '<i class="las la-info-circle"></i> Nao se aplica a bolao gratuito.';
            } else if (isTestMode) {
                houseHelp.innerHTML = '<i class="las la-info-circle"></i> Modo teste: 0% a 50% (sem mínimo)';
            } else if (isCustom) {
                houseHelp.innerHTML = '<i class="las la-info-circle"></i> Para bolão personalizado: entre 0% e 50%';
            } else {
                houseHelp.innerHTML = '<i class="las la-info-circle"></i> Para ligas pagas: entre 20% e 50%';
            }
        }
    }

    function getSelectedEntryPrice(mode) {
        if (mode === 'premium' || mode === 'free') {
            return 0;
        }

        if (mode === 'custom') {
            return customEntryPriceInput ? parseFloat(customEntryPriceInput.value) || 0 : 0;
        }

        return mode ? parseFloat(mode) || 0 : 0;
    }

    rodeio && rodeio.addEventListener('change', filterModalidades);
    modalidade && modalidade.addEventListener('change', syncDivisoes);
    divisao && divisao.addEventListener('change', syncLeagueContext);
    entryMode && entryMode.addEventListener('change', syncPricingUI);
    rewardMode && rewardMode.addEventListener('change', syncPricingUI);
    
    // Initial sync on page load
    filterModalidades();
    syncDivisoes();
    syncLeagueContext();
    syncPricingUI();
    entryMode && entryMode.addEventListener('change', function() {
        syncPricingUI();
        syncPrizeCalculation();
    });
    rewardMode && rewardMode.addEventListener('change', syncPricingUI);
    rewardMode && rewardMode.addEventListener('change', syncPrizeCalculation);
    prizeType && prizeType.addEventListener('change', function() {
        syncPricingUI();
        syncPrizeCalculation();
    });
    
    // Prize calculation inputs
    const maxUsersInput = document.getElementById('max_users_input');
    const paidPositionsOverrideInput = document.getElementById('paid_positions_override_input');
    const prizeCard = document.getElementById('prizeCalculationCard');
    
    houseCut && houseCut.addEventListener('input', syncPrizeCalculation);
    maxUsersInput && maxUsersInput.addEventListener('input', function() {
        syncPrizeCalculation();
        renderPhysicalPrizeRows();
    });
    paidPositionsOverrideInput && paidPositionsOverrideInput.addEventListener('input', function() {
        syncPrizeCalculation();
        renderPhysicalPrizeRows();
    });
    customEntryPriceInput && customEntryPriceInput.addEventListener('input', syncPrizeCalculation);
    manualPrize && manualPrize.addEventListener('input', syncPrizeCalculation);

    function getPaidPositions(totalPlayers) {
        if (totalPlayers <= 0) return 0;
        return Math.max(1, Math.floor(totalPlayers * 10 / 100));
    }

    function resolvePaidPositions(totalPlayers, override) {
        if (totalPlayers <= 0) return 0;

        var parsedOverride = parseInt(override, 10) || 0;
        if (parsedOverride > 0) {
            return Math.min(parsedOverride, totalPlayers);
        }

        return getPaidPositions(totalPlayers);
    }

    function getTiers(paidPositions) {
        if (paidPositions <= 0) return [];
        if (paidPositions === 1) return [{ from: 1, to: 1, pct: 100 }];
        if (paidPositions === 2) return [{ from: 1, to: 1, pct: 65 }, { from: 2, to: 2, pct: 35 }];
        if (paidPositions === 3) return [{ from: 1, to: 1, pct: 50 }, { from: 2, to: 2, pct: 30 }, { from: 3, to: 3, pct: 20 }];

        var tiers = [{ from: 1, to: 1 }, { from: 2, to: 2 }, { from: 3, to: 3 }];
        var remaining = paidPositions - 3;
        var pos = 4;

        if (remaining <= 3) {
            tiers.push({ from: 4, to: paidPositions });
        } else {
            var chunks = remaining <= 8 ? 2 : (remaining <= 20 ? 3 : 4);
            var base = Math.floor(remaining / chunks);
            var extra = remaining - base * chunks;
            var sizes = [];
            for (var c = 0; c < chunks; c++) sizes.push(base + (c < extra ? 1 : 0));
            sizes.sort(function(a, b) { return a - b; });
            for (var c = 0; c < sizes.length; c++) {
                tiers.push({ from: pos, to: pos + sizes[c] - 1 });
                pos += sizes[c];
            }
        }

        var nTiers = tiers.length;
        var floorPctPerPerson = 100.0 / (paidPositions * 3.6);
        var totalFloor = floorPctPerPerson * paidPositions;
        var curvePool = 100.0 - totalFloor;

        var spread = Math.max(3, Math.pow(paidPositions, 1.2));
        var ratio = Math.pow(spread, 1.0 / Math.max(1, nTiers - 1));

        var perPerson = new Array(nTiers);
        perPerson[nTiers - 1] = 1;
        for (var i = nTiers - 2; i >= 0; i--) perPerson[i] = perPerson[i + 1] * ratio;

        var totalRaw = 0;
        for (var i = 0; i < nTiers; i++) {
            var count = tiers[i].to - tiers[i].from + 1;
            totalRaw += perPerson[i] * count;
        }

        for (var i = 0; i < nTiers; i++) {
            var count = tiers[i].to - tiers[i].from + 1;
            var curvePctPerPerson = curvePool * perPerson[i] / totalRaw;
            var totalPctPerPerson = floorPctPerPerson + curvePctPerPerson;
            tiers[i].pct = Math.round(totalPctPerPerson * count * 100) / 100;
        }

        var sum = tiers.reduce(function(s, t) { return s + t.pct; }, 0);
        if (Math.abs(sum - 100) > 0.01) tiers[0].pct = Math.round((tiers[0].pct + (100 - sum)) * 100) / 100;

        return tiers;
    }

    function formatBRL(value) {
        return 'R$ ' + value.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function roundMoney(value) {
        return Math.round((Number(value) + Number.EPSILON) * 100) / 100;
    }

    function roundDistribution(value) {
        return Math.round((Number(value) + Number.EPSILON) * 1000000) / 1000000;
    }

    function getAutoDistributionMap(paidPositions) {
        const tiers = getTiers(paidPositions);
        const distribution = {};

        tiers.forEach(function(tier) {
            const count = tier.to - tier.from + 1;
            const percentPerPosition = tier.pct / count;
            for (let position = tier.from; position <= tier.to; position++) {
                distribution[position] = percentPerPosition;
            }
        });

        const sum = Object.values(distribution).reduce((acc, value) => acc + value, 0);
        if (distribution[1] && Math.abs(sum - 100) > 0.0001) {
            distribution[1] += (100 - sum);
        }

        return distribution;
    }

    function distributionToAmounts(distribution, totalPrize, paidPositions) {
        const amounts = {};
        let allocated = 0;

        for (let position = 1; position <= paidPositions; position++) {
            const percent = Number(distribution[position] ?? distribution[String(position)] ?? 0);
            const amount = roundMoney((totalPrize * percent) / 100);
            amounts[position] = amount;
            allocated += amount;
        }

        allocated = roundMoney(allocated);
        if (paidPositions > 0 && Math.abs(allocated - totalPrize) > 0.009) {
            amounts[1] = roundMoney((amounts[1] || 0) + (totalPrize - allocated));
        }

        return amounts;
    }

    function buildAutoAmounts(totalPrize, paidPositions) {
        return distributionToAmounts(getAutoDistributionMap(paidPositions), totalPrize, paidPositions);
    }

    function amountsToDistribution(amounts, totalPrize, paidPositions) {
        const distribution = {};
        let sum = 0;

        for (let position = 1; position <= paidPositions; position++) {
            const amount = roundMoney(amounts[position] || 0);
            distribution[position] = totalPrize > 0 ? roundDistribution((amount / totalPrize) * 100) : 0;
            sum += distribution[position];
        }

        if (distribution[1] && Math.abs(sum - 100) > 0.0001) {
            distribution[1] = roundDistribution(distribution[1] + (100 - sum));
        }

        return distribution;
    }

    function updateManualPrizeSummary(totalPrize, paidPositions) {
        if (!prizeManualEditor || !manualPrizePositionsLabel) return;

        let allocated = 0;
        for (let position = 1; position <= paidPositions; position++) {
            const amount = roundMoney(manualPrizeAmounts[position] || 0);
            allocated += amount;
        }

        allocated = roundMoney(allocated);
        const difference = roundMoney(totalPrize - allocated);

        manualPrizePositionsLabel.textContent = 'Top ' + paidPositions;
        manualPrizeAllocated.textContent = formatBRL(allocated);
        manualPrizeDifference.textContent = formatBRL(Math.abs(difference));

        if (difference >= 0) {
            if (difference === 0) {
                manualPrizeDiffBox.style.background = 'rgba(34,197,94,0.18)';
                manualPrizeDiffBox.style.color = '#bbf7d0';
            } else {
                manualPrizeDiffBox.style.background = 'rgba(234,179,8,0.18)';
                manualPrizeDiffBox.style.color = '#fde68a';
            }

            prizeDistributionInput.value = manualPrizeDirty
                ? JSON.stringify(amountsToDistribution(manualPrizeAmounts, totalPrize, paidPositions))
                : '';
        } else {
            manualPrizeDiffBox.style.background = 'rgba(220,38,38,0.18)';
            manualPrizeDiffBox.style.color = '#fecaca';
            prizeDistributionInput.value = '';
        }

        renderPrizePreviewRows(totalPrize, paidPositions);
    }

    function renderPrizePreviewRows(totalPrize, paidPositions) {
        const tableBody = document.getElementById('simulatorTableBody');
        if (!tableBody) return;

        const autoAmounts = buildAutoAmounts(totalPrize, paidPositions);
        const usingManual = manualPrizeDirty;
        const accentColors = [
            'rgba(251, 191, 36, 0.16)',
            'rgba(203, 213, 225, 0.16)',
            'rgba(194, 120, 56, 0.16)',
            'rgba(59, 130, 246, 0.08)'
        ];

        let tableHTML = '';
        for (let position = 1; position <= paidPositions; position++) {
            const amount = roundMoney(usingManual ? (manualPrizeAmounts[position] || 0) : (autoAmounts[position] || 0));
            const percent = totalPrize > 0 ? ((amount / totalPrize) * 100) : 0;
            const icon = position === 1 ? '🥇' : (position === 2 ? '🥈' : (position === 3 ? '🥉' : '🏅'));
            const label = position + 'º Lugar';

            tableHTML += `
                <tr style="background:${accentColors[position - 1] || 'rgba(148,163,184,0.08)'};">
                    <td style="font-weight:700;">${icon} ${label}</td>
                    <td class="prize-percent-cell">${percent.toFixed(2)}%</td>
                    <td class="prize-amount-cell">${formatBRL(amount)}</td>
                </tr>
            `;
        }

        tableBody.innerHTML = tableHTML;
    }

    function renderManualPrizeEditor(maxUsers, totalPrize, paidOverride) {
        if (!prizeManualEditor || !manualPrizeRows) return;

        const paidPositions = resolvePaidPositions(maxUsers, paidOverride);
        if (maxUsers <= 0 || totalPrize <= 0 || paidPositions <= 0) {
            prizeManualEditor.style.display = 'none';
            prizeDistributionInput.value = '';
            return;
        }

        prizeManualEditor.style.display = '';

        const currentPositions = Object.keys(manualPrizeAmounts).length;
        const previousPrize = Number(prizeManualEditor.dataset.totalPrize || 0);
        const totalPrizeChanged = Math.abs(previousPrize - totalPrize) > 0.009;
        if (currentPositions !== paidPositions || ((!manualPrizeTouched || hasInitialManualDistribution) && totalPrizeChanged)) {
            if (hasInitialManualDistribution && !manualPrizeTouched && Object.keys(initialPrizeDistribution || {}).length === paidPositions) {
                manualPrizeAmounts = distributionToAmounts(initialPrizeDistribution, totalPrize, paidPositions);
            } else {
                manualPrizeAmounts = buildAutoAmounts(totalPrize, paidPositions);
                if (!manualPrizeDirty) {
                    prizeDistributionInput.value = '';
                }
            }
        }
        prizeManualEditor.dataset.totalPrize = totalPrize.toFixed(2);

        let rowsHtml = '';
        for (let position = 1; position <= paidPositions; position++) {
            rowsHtml += `
                <div style="display:grid; grid-template-columns: 110px 1fr 110px; gap:.75rem; align-items:center; padding:.85rem 1rem; border-radius:10px; background:rgba(15,23,42,0.32); border:1px solid rgba(249,115,22,0.14);">
                    <div style="font-weight:800; color:#f8fafc;">${position}º Lugar</div>
                    <input type="number" min="0" step="0.01" class="fantasy-form-control manual-prize-input" data-position="${position}" value="${Number(manualPrizeAmounts[position] || 0).toFixed(2)}">
                    <div style="font-size:.9rem; color:#cbd5e1; text-align:right;">${position === 1 ? 'campeão' : 'posição paga'}</div>
                </div>
            `;
        }

        manualPrizeRows.innerHTML = rowsHtml;
        manualPrizeRows.querySelectorAll('.manual-prize-input').forEach(function(input) {
            input.addEventListener('input', function() {
                manualPrizeTouched = true;
                manualPrizeDirty = true;
                manualPrizeAmounts[parseInt(this.dataset.position, 10)] = roundMoney(parseFloat(this.value) || 0);
                updateManualPrizeSummary(totalPrize, paidPositions);
            });
        });

        updateManualPrizeSummary(totalPrize, paidPositions);
    }

    function validateManualPrizeBeforeSubmit() {
        if (!prizeManualEditor || prizeManualEditor.style.display === 'none' || !manualPrizeDirty) {
            return true;
        }

        if (!prizeDistributionInput.value) {
            alert('A distribuição manual do prêmio não pode passar do prêmio total antes de salvar.');
            return false;
        }

        return true;
    }

    if (resetPrizeDistributionBtn) {
        resetPrizeDistributionBtn.addEventListener('click', function() {
            hasInitialManualDistribution = false;
            manualPrizeTouched = false;
            manualPrizeDirty = false;
            manualPrizeAmounts = {};
            syncPrizeCalculation();
        });
    }

    function updatePrizeDistributionSimulator(maxUsers, totalPrize, paidOverride) {
        const simulator = document.getElementById('prizeDistributionSimulator');
        const tableWrap = document.getElementById('simulatorTableWrap');
        const emptyState = document.getElementById('simulatorEmptyState');

        if (!simulator) return;
        
        if (maxUsers <= 0 || totalPrize <= 0) {
            simulator.style.display = '';
            tableWrap.style.display = 'none';
            emptyState.style.display = '';
            if (prizeManualEditor) {
                prizeManualEditor.style.display = 'none';
                prizeDistributionInput.value = '';
            }
            return;
        }

        simulator.style.display = '';
        tableWrap.style.display = '';
        emptyState.style.display = 'none';

        const paidPositions = resolvePaidPositions(maxUsers, paidOverride);
        const percentage = ((paidPositions / maxUsers) * 100).toFixed(1);

        document.getElementById('simulatorEntrants').textContent = maxUsers;
        document.getElementById('simulatorPaidPositions').textContent = paidPositions;
        document.getElementById('simulatorPercentage').textContent = manualPrizeDirty
            ? 'Valores manuais • Top ' + paidPositions
            : percentage + '%';

        renderManualPrizeEditor(maxUsers, totalPrize, paidOverride);
    }
    
    function syncPrizeCalculation() {
        const mode = entryMode ? String(entryMode.value || '') : '';
        const isPremium = mode === 'premium';
        const isFree = mode === 'free';
        const isManualReward = (isPremium || isFree) && rewardMode && rewardMode.value === 'manual_prize';
        const isPhysicalPrize = isManualReward && prizeType && prizeType.value === 'physical';
        const manualPrizeValue = manualPrize ? (parseFloat(manualPrize.value) || 0) : 0;
        const maxUsers = maxUsersInput ? parseInt(maxUsersInput.value) || 0 : 0;
        const paidPositionsOverride = paidPositionsOverrideInput ? parseInt(paidPositionsOverrideInput.value) || 0 : 0;
        const cutPercent = houseCut ? parseFloat(houseCut.value) || 0 : 0;
        const entryPrice = (!isPremium && !isFree) ? getSelectedEntryPrice(mode) : 0;
        
        // Show/hide card based on entry mode and max users
        if (prizeCard) {
            if (isPhysicalPrize) {
                prizeCard.style.display = 'none';
                const totalPrizeInput = document.getElementById('total_prize_input');
                if (totalPrizeInput) totalPrizeInput.value = '0.00';
                updatePrizeDistributionSimulator(0, 0, 0);
                return;
            }

            if (isManualReward) {
                const shouldShowManual = maxUsers > 0 && manualPrizeValue > 0;
                prizeCard.style.display = shouldShowManual ? '' : 'none';
                const totalPrizeInput = document.getElementById('total_prize_input');
                if (totalPrizeInput) totalPrizeInput.value = manualPrizeValue.toFixed(2);

                if (!shouldShowManual) {
                    updatePrizeDistributionSimulator(0, 0, 0);
                    return;
                }

                document.getElementById('entryPrice').textContent = 'R$ 0,00';
                document.getElementById('maxUsersDisplay').textContent = maxUsers;
                document.getElementById('totalCollection').textContent = 'R$ 0,00';
                document.getElementById('houseCutDisplay').textContent = '0,0';
                document.getElementById('houseCutValue').textContent = '- R$ 0,00';
                document.getElementById('totalPrize').textContent = 'R$ ' + manualPrizeValue.toFixed(2).replace('.', ',');
                updatePrizeDistributionSimulator(maxUsers, manualPrizeValue, paidPositionsOverride);
                return;
            }

            const shouldShow = maxUsers > 0 && entryPrice > 0;
            prizeCard.style.display = shouldShow ? '' : 'none';
            
            if (!shouldShow) {
                const totalPrizeInput = document.getElementById('total_prize_input');
                if (totalPrizeInput) totalPrizeInput.value = '0.00';
                updatePrizeDistributionSimulator(0, 0, 0);
                return;
            }

            const totalCollection = entryPrice * maxUsers;
            const houseCutAmt = totalCollection * (cutPercent / 100);
            const totalPrize = totalCollection - houseCutAmt;
            
            document.getElementById('entryPrice').textContent = 'R$ ' + entryPrice.toFixed(2).replace('.', ',');
            document.getElementById('maxUsersDisplay').textContent = maxUsers;
            document.getElementById('totalCollection').textContent = 'R$ ' + totalCollection.toFixed(2).replace('.', ',');
            document.getElementById('houseCutDisplay').textContent = cutPercent.toFixed(1).replace('.', ',');
            document.getElementById('houseCutValue').textContent = '- R$ ' + houseCutAmt.toFixed(2).replace('.', ',');
            document.getElementById('totalPrize').textContent = 'R$ ' + totalPrize.toFixed(2).replace('.', ',');
            document.getElementById('total_prize_input').value = totalPrize.toFixed(2);
            updatePrizeDistributionSimulator(maxUsers, totalPrize, paidPositionsOverride);
        }
    }
    
    filterModalidades();
    syncPricingUI();
    syncPrizeCalculation();
})();
</script>
@endpush

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.fantasy_leagues.index') }}" />
@endpush

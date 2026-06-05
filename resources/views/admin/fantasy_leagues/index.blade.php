@extends('admin.layouts.app')

@section('panel')

    <div class="fantasy-index-wrapper">
        <div class="fantasy-index-card">
            <div class="fantasy-index-header">
                <h5><i class="las la-trophy"></i> @lang('Fantasy Leagues')</h5>
                <div style="display: flex; gap: 0.75rem;">
                    <a href="{{ route('admin.fantasy_leagues.entries') }}" class="fantasy-add-btn" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
                        <i class="las la-list-ol"></i> @lang('Entradas')
                    </a>
                    <a href="{{ route('admin.fantasy_leagues.create') }}" class="fantasy-add-btn">
                        <i class="las la-plus-circle"></i> @lang('Nova Liga')
                    </a>
                </div>
            </div>

            <div class="fantasy-filters-section">
                <form method="get">
                    <div class="fantasy-filter-group">
                        <div class="fantasy-filter-item search">
                            <label class="fantasy-filter-label">
                                <i class="las la-search"></i> @lang('Buscar')
                            </label>
                            <input type="search" name="q" value="{{ request('q') }}" class="fantasy-filter-input" placeholder="@lang('Digite o nome da liga...')">
                        </div>

                        <div class="fantasy-filter-item">
                            <label class="fantasy-filter-label">
                                <i class="las la-tag"></i> @lang('Categoria')
                            </label>
                            <select name="category" class="fantasy-filter-select">
                                <option value="">@lang('Todas as categorias')</option>
                                @foreach(($categories ?? []) as $cat)
                                    <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="fantasy-filter-item">
                            <label class="fantasy-filter-label">
                                <i class="las la-ticket-alt"></i> @lang('Tipo')
                            </label>
                            <select name="is_premium" class="fantasy-filter-select">
                                <option value="">@lang('Todos os tipos')</option>
                                <option value="1" @selected(request('is_premium') === '1')>@lang('Premium')</option>
                                <option value="paid" @selected(in_array(request('is_premium'), ['paid', '0'], true))>@lang('Pago')</option>
                                <option value="free" @selected(request('is_premium') === 'free')>@lang('Gratuito')</option>
                            </select>
                        </div>

                        <div class="fantasy-filter-item">
                            <label class="fantasy-filter-label">
                                <i class="las la-power-off"></i> @lang('Status')
                            </label>
                            <select name="is_active" class="fantasy-filter-select">
                                <option value="">@lang('Todos os status')</option>
                                <option value="1" @selected(request('is_active') === '1')>@lang('Ativas')</option>
                                <option value="0" @selected(request('is_active') === '0')>@lang('Inativas')</option>
                            </select>
                        </div>

                        <div class="fantasy-filter-item">
                            <div class="fantasy-filter-actions">
                                <button type="submit" class="fantasy-filter-btn primary">
                                    <i class="las la-filter"></i> @lang('Filtrar')
                                </button>
                                <a href="{{ route('admin.fantasy_leagues.index') }}" class="fantasy-filter-btn secondary">
                                    <i class="las la-redo-alt"></i> @lang('Limpar')
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="fantasy-table-wrapper">
                <table class="fantasy-table">
                    <thead>
                        <tr>
                            <th>@lang('Nome')</th>
                            <th>@lang('Categoria')</th>
                            <th>@lang('Preço')</th>
                            <th>@lang('Tipo')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Máx. Users')</th>
                            <th>@lang('Prêmio')</th>
                            <th>@lang('Criada em')</th>
                            <th>@lang('Ações')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leagues as $league)
                            @php
                                $isFreeLeague = !$league->is_premium && (float) $league->price <= 0;
                                $prizeType = $league->prize_type ?? 'money';
                                $prizeDescription = trim((string) ($league->prize_description ?? ''));
                                $prizeItems = is_array($league->prize_items ?? null) ? $league->prize_items : [];
                                $moneyPrize = (float) ($league->total_prize ?? $league->manual_prize_pool ?? 0);
                            @endphp
                            <tr>
                                <td>
                                    <span class="fantasy-league-name">{{ $league->name }}</span>
                                </td>
                                <td>
                                    <span style="color: #94a3b8; text-transform: capitalize;">{{ $league->category }}</span>
                                </td>
                                <td>
                                    @if($league->is_premium)
                                        <span style="font-weight: 600; color: #93c5fd;">@lang('Premium')</span>
                                    @elseif($isFreeLeague)
                                        <span style="font-weight: 600; color: #86efac;">@lang('Gratis')</span>
                                    @else
                                        <span style="font-weight: 600; color: #10b981;">R$ {{ number_format((float) $league->price, 2, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($league->is_premium)
                                        <span class="fantasy-badge premium">🌟 Premium</span>
                                    @elseif($isFreeLeague)
                                        <span class="fantasy-badge free">Livre</span>
                                    @else
                                        <span class="fantasy-badge paid">💰 Pago</span>
                                    @endif
                                </td>
                                <td>
                                    @if($league->is_active)
                                        <span class="fantasy-badge active">✓ Ativa</span>
                                    @elseif(($league->status ?? 'active') === 'finalized')
                                        <span class="fantasy-badge finalized">🏆 Finalizada</span>
                                    @else
                                        <span class="fantasy-badge inactive">✕ Inativa</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="color: #94a3b8;">{{ $league->max_users ?? '∞' }}</span>
                                </td>
                                <td>
                                    @if($prizeType === 'physical' && $prizeDescription !== '')
                                        <span style="color: #fdba74; font-weight: 700;">{{ \Illuminate\Support\Str::limit($prizeDescription, 48) }}</span>
                                    @elseif($prizeType === 'physical' && count($prizeItems) > 0)
                                        <span style="color: #fdba74; font-weight: 700;">{{ count($prizeItems) }} @lang('premios fisicos')</span>
                                    @elseif($moneyPrize > 0)
                                        <span style="color: #10b981; font-weight: 700;">R$ {{ number_format($moneyPrize, 2, ',', '.') }}</span>
                                    @elseif($prizeType === 'physical')
                                        <span style="color: #fdba74; font-weight: 700;">@lang('Premio fisico')</span>
                                    @else
                                        <span style="color: #64748b;">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="color: #94a3b8; font-size: 0.85rem;">{{ showDateTime($league->created_at) }}</span>
                                </td>
                                <td>
                                    <div class="fantasy-actions">
                                        <a href="{{ route('admin.fantasy_leagues.edit', $league) }}" class="fantasy-action-btn edit">
                                            <i class="las la-edit"></i> Editar
                                        </a>

                                        <form method="post" action="{{ route('admin.fantasy_leagues.toggle_status', $league) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="fantasy-action-btn toggle">
                                                @if($league->is_active)
                                                    <i class="las la-ban"></i> Desativar
                                                @else
                                                    <i class="las la-check"></i> Ativar
                                                @endif
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.fantasy_leagues.destroy', $league) }}" onsubmit="return confirm(@js(__('Tem certeza que deseja excluir esta liga?')));" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="fantasy-action-btn delete">
                                                <i class="las la-trash"></i> Excluir
                                            </button>
                                        </form>
                                        
                                        @if(($league->status ?? 'active') !== 'finalized')
                                            <form method="POST" action="{{ route('admin.fantasy_leagues.finalize', $league) }}" 
                                                  onsubmit="return confirm(@js(__('Finalizar esta liga distribuirá os prêmios e processará as comissões de afiliados. Esta ação NÃO pode ser desfeita. Continuar?')));" 
                                                  style="display: inline;">
                                                @csrf
                                                <button type="submit" class="fantasy-action-btn finalize">
                                                    <i class="las la-flag-checkered"></i> Finalizar
                                                </button>
                                            </form>
                                        @else
                                            <span class="fantasy-action-btn finalize disabled" title="Liga já finalizada">
                                                <i class="las la-flag-checkered"></i> Finalizada
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="fantasy-empty-state">
                                        <i class="las la-inbox"></i>
                                        <p>@lang('Nenhuma Fantasy League encontrada')</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($leagues->hasPages())
                <div class="fantasy-pagination">
                    {{ paginateLinks($leagues) }}
                </div>
            @endif
        </div>
    </div>

@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.dashboard') }}" />
@endpush

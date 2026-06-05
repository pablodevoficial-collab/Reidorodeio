@forelse($cards as $card)
    @php
        $levelLabels = [
            'favorito' => 'Favorito',
            'elite' => 'Elite',
            'ascendente' => 'Ascendente',
            'competidor' => 'Competidor',
        ];
        $levelIcons = [
            'favorito' => 'fa-fire',
            'elite' => 'fa-crown',
            'ascendente' => 'fa-chart-line',
            'competidor' => 'fa-bolt',
        ];
        $levelKey = $card['nivel_key'] ?? 'competidor';
        $levelLabel = $levelLabels[$levelKey] ?? 'Competidor';
        $levelIcon = $levelIcons[$levelKey] ?? 'fa-bolt';
        $entryType = $card['entry_type'] ?? 'competitor';
        $isGroupCard = $entryType === 'group';
        $memberPhotos = collect($card['member_photos'] ?? [])->filter()->values();
        $captainPhoto = $memberPhotos->first() ?: ($card['entry_photo'] ?? asset('assets/images/logo_icon/logo.png'));
        $memberNames = collect($card['member_names'] ?? [])->values();
        $memberCount = max((int) ($card['member_count'] ?? $memberPhotos->count()), $memberPhotos->count());
        $groupRosterItems = $memberPhotos->map(function ($photo, $index) use ($memberNames) {
            return [
                'photo' => $photo,
                'name' => (string) ($memberNames->get($index) ?: ('Integrante ' . ($index + 1))),
                'is_captain' => $index === 0,
            ];
        })->values();
        $groupRosterClass = $memberCount >= 10 ? 'rr-neuro-group-roster--ten' : 'rr-neuro-group-roster--compact';
    @endphp
    <article class="rr-neuro-wrapper {{ !$isPremiumUser ? 'rr-neuro-wrapper--with-premium' : '' }} {{ $card['neon_class'] }}"
        data-entry-type="{{ $entryType }}"
        data-entry-id="{{ $card['entry_id'] }}"
        data-entry-name="{{ $card['entry_name_raw'] }}"
        data-search-text="{{ $card['search_text'] }}"
        data-nivel="{{ $card['nivel_key'] }}"
        data-modalidade-id="{{ $card['modalidade_id'] ?? '' }}"
        data-modalidade-name="{{ $card['modalidade_nome'] ?? '' }}"
        data-rodeio-id="{{ $card['rodeio_id'] ?? '' }}"
        data-rodeio-name="{{ $card['rodeio_nome'] ?? '' }}"
        data-divisao="{{ $card['divisao'] ?? '' }}"
        data-member-ids="{{ implode(',', $card['member_ids'] ?? []) }}"
        data-member-names="{{ implode('|', $card['member_names'] ?? []) }}"
        data-captain-name="{{ $card['captain_name'] ?? $card['competitor_display_name'] }}"
        data-multiplier="{{ number_format($card['free_multiplier'], 2, '.', '') }}"
        data-premium-multiplier="{{ number_format($card['premium_multiplier'], 2, '.', '') }}"
        @if($isGroupCard)
        data-group-id="{{ $card['entry_id'] }}"
        @else
        data-competitor-id="{{ $card['entry_id'] }}"
        @endif>
        <div class="rr-card-inner">
            <div class="rr-card-inside"></div>
            <div class="card__shine"></div>
            <div class="card__glare"></div>
            <div class="rr-card-content-layer">
                <div class="rr-neuro-header">
                    <div class="rr-neuro-topline">
                        <span class="rr-neuro-level-badge rr-neuro-level-badge--{{ $levelKey }}">
                            <i class="fas {{ $levelIcon }}"></i>
                            <span>{{ $levelLabel }}</span>
                        </span>
                    </div>
                    <div class="rr-neuro-hero">
                        <div class="rr-neuro-avatar-stack {{ $isGroupCard ? 'rr-neuro-avatar-stack--group' : '' }}">
                            <span class="rr-neuro-portrait-glow" aria-hidden="true"></span>
                            @if($isGroupCard)
                            <div class="rr-neuro-group-roster {{ $groupRosterClass }}" aria-label="Integrantes do grupo">
                                @foreach($groupRosterItems as $member)
                                <span class="rr-neuro-group-roster__item {{ $member['is_captain'] ? 'is-captain' : '' }}" title="{{ $member['name'] }}">
                                    <img src="{{ $member['photo'] }}"
                                         alt="{{ $member['name'] }}"
                                         loading="lazy"
                                         onerror="this.onerror=null;this.src='{{ asset('assets/images/logo_icon/logo.png') }}';">
                                </span>
                                @endforeach
                            </div>
                            @else
                            <div class="rr-neuro-img-container">
                                <img src="{{ $captainPhoto }}"
                                     alt="{{ $card['entry_name_raw'] }}"
                                     class="rr-neuro-img"
                                     loading="lazy"
                                     onerror="this.onerror=null;this.src='{{ asset('assets/images/logo_icon/logo.png') }}';">
                            </div>
                            @endif
                        </div>
                        <div class="rr-neuro-title-wrap">
                            @if($isGroupCard)
                            <span class="rr-neuro-captain-badge">
                                <i class="fas fa-user-tie"></i>
                                <span>Capitão do grupo</span>
                            </span>
                            @endif
                            <h1 class="rr-neuro-title">
                                {{ $card['competitor_display_name'] }}
                            </h1>
                            @if($isGroupCard)
                            <button type="button" class="rr-neuro-group-members-btn" data-action="open-group-members">
                                <i class="fas fa-users"></i>
                                <span>Ver grupo</span>
                            </button>
                            @elseif($card['entry_subtitle'] !== '')
                            <p class="rr-neuro-subtitle">{{ $card['entry_subtitle'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="rr-neuro-content">
                    <button type="button" class="rr-neuro-stats-inline" data-action="open-slip">
                        <span class="rr-neuro-odd-label">Paga agora</span>
                        <span class="rr-neuro-odd-row">
                            <span class="rr-neuro-multiplier">{{ number_format($isPremiumUser ? $card['premium_multiplier'] : $card['free_multiplier'], 2, ',', '.') }}x</span>
                        </span>
                    </button>
                    @unless($isGroupCard)
                    <button type="button" class="rr-neuro-view-stats" data-action="open-stats">
                        <i class="fas fa-chart-bar"></i>
                        <span class="rr-neuro-view-stats__label">Ver estat&iacute;sticas</span>
                    </button>
                    @endunless
                </div>
            </div>
        </div>
        <button type="button" class="rr-neuro-play-now rr-neuro-play-now--floating {{ !$isPremiumUser ? 'rr-neuro-play-now--floating-premium' : '' }}" data-action="open-slip">
            <span class="rr-neuro-play-now__copy">
                <span class="rr-neuro-play-now__label">Jogue agora</span>
            </span>
            <span class="rr-neuro-play-now__icon"><i class="fas fa-arrow-right"></i></span>
        </button>
        @if(!$isPremiumUser)
        <button type="button" class="rr-neuro-premium-banner rr-neuro-premium-banner--floating" data-action="go-premium">
            <span class="rr-neuro-premium-icon">
                <i class="fas fa-crown"></i>
            </span>
            <span class="rr-neuro-premium-text">
                <span class="rr-neuro-premium-value">Premium {{ number_format($card['premium_multiplier'], 2, ',', '.') }}x</span>
            </span>
            <i class="fas fa-chevron-right rr-neuro-premium-arrow"></i>
        </button>
        @endif
    </article>
@empty
    <div class="alert alert-info mb-0">Nenhum {{ $entryLabelSingular }} disponível para exibir.</div>
@endforelse

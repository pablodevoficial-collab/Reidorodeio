@extends('admin.layouts.app')

@section('panel')
<div class="rr-admin-dark">
@include('admin.partials.rr-admin-dark')

<div class="rr-req-shell">
    <div class="rr-req-header">
        <div>
            <h3>Solicitações de Competidor</h3>
            <p>Revise os dados enviados e aprove o vínculo do usuário com o perfil público de competidor.</p>
        </div>
        <div class="rr-req-stats">
            <span class="rr-req-pill rr-req-pill--pending">Pendentes {{ $counts['pending'] }}</span>
            <span class="rr-req-pill rr-req-pill--approved">Aprovadas {{ $counts['approved'] }}</span>
            <span class="rr-req-pill rr-req-pill--rejected">Rejeitadas {{ $counts['rejected'] }}</span>
        </div>
    </div>

    <div class="rr-req-filters">
        <a href="{{ route('admin.competitors.requests.index', ['status' => 'pending']) }}" class="rr-req-filter {{ $status === 'pending' ? 'is-active' : '' }}">Pendentes</a>
        <a href="{{ route('admin.competitors.requests.index', ['status' => 'approved']) }}" class="rr-req-filter {{ $status === 'approved' ? 'is-active' : '' }}">Aprovadas</a>
        <a href="{{ route('admin.competitors.requests.index', ['status' => 'rejected']) }}" class="rr-req-filter {{ $status === 'rejected' ? 'is-active' : '' }}">Rejeitadas</a>
        <a href="{{ route('admin.competitors.requests.index', ['status' => '']) }}" class="rr-req-filter {{ $status === '' ? 'is-active' : '' }}">Todas</a>
    </div>

    <div class="rr-req-list">
        @forelse ($requests as $requestItem)
            @php
                $user = $requestItem->user;
                $fullName = trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
                $displayName = $fullName !== '' ? $fullName : ($user->username ?? 'Usuário');
                $avatar = $user && $user->image ? asset('assets/images/user/profile/' . $user->image) : asset('assets/images/logo_icon/favicon.png');
            @endphp
            <article class="rr-req-card">
                <div class="rr-req-card__top">
                    <div class="rr-req-card__identity">
                        <img src="{{ $avatar }}" alt="{{ $displayName }}">
                        <div>
                            <strong>{{ $displayName }}</strong>
                            <span>@{{ $user->username ?? 'sem-username' }}</span>
                            <small>Solicitado em {{ $requestItem->created_at?->format('d/m/Y H:i') }}</small>
                        </div>
                    </div>
                    <span class="rr-req-status rr-req-status--{{ $requestItem->status }}">{{ ucfirst($requestItem->status) }}</span>
                </div>

                <div class="rr-req-grid">
                    <div class="rr-req-field"><span>Email</span><strong>{{ $user->email ?? '-' }}</strong></div>
                    <div class="rr-req-field"><span>WhatsApp</span><strong>{{ $user->mobile ?? '-' }}</strong></div>
                    <div class="rr-req-field"><span>CPF</span><strong>{{ $user->cpf ?? '-' }}</strong></div>
                    <div class="rr-req-field"><span>Nascimento</span><strong>{{ optional($user->birthdate)->format('d/m/Y') ?? '-' }}</strong></div>
                </div>

                <div class="rr-req-bio">
                    <span>Biografia enviada</span>
                    <p>{{ $requestItem->biografia ?: 'Nenhuma biografia enviada.' }}</p>
                </div>

                @if ($requestItem->status === 'approved' && $requestItem->competitor)
                    <div class="rr-req-linked">
                        <span>Competidor vinculado</span>
                        <a href="{{ route('admin.competitors.edit', $requestItem->competitor) }}">#{{ $requestItem->competitor->id }} · {{ $requestItem->competitor->nome }}</a>
                    </div>
                @endif

                @if ($requestItem->admin_notes)
                    <div class="rr-req-note">
                        <span>Observação do admin</span>
                        <p>{{ $requestItem->admin_notes }}</p>
                    </div>
                @endif

                @if ($requestItem->status === 'pending')
                    <div class="rr-req-actions">
                        <form method="POST" action="{{ route('admin.competitors.requests.approve', $requestItem) }}">
                            @csrf
                            <button type="submit" class="rr-req-btn rr-req-btn--approve">Aprovar usuário/competidor</button>
                        </form>

                        <form method="POST" action="{{ route('admin.competitors.requests.reject', $requestItem) }}">
                            @csrf
                            <input type="hidden" name="admin_notes" value="Solicitação rejeitada no painel administrativo.">
                            <button type="submit" class="rr-req-btn rr-req-btn--reject">Rejeitar</button>
                        </form>
                    </div>
                @endif
            </article>
        @empty
            <div class="rr-req-empty">
                <strong>Nenhuma solicitação encontrada.</strong>
                <span>Quando alguém pedir cadastro como competidor, ela aparece aqui.</span>
            </div>
        @endforelse
    </div>

    @if ($requests->hasPages())
        <div class="rr-req-pagination">
            {{ paginateLinks($requests) }}
        </div>
    @endif
</div>
</div>
@endsection

@push('style')
<style>
.rr-req-shell{display:grid;gap:18px}
.rr-req-header,.rr-req-card,.rr-req-empty,.rr-req-pagination{background:rgba(15,23,42,.76);border:1px solid rgba(148,163,184,.14);border-radius:20px;box-shadow:0 18px 34px rgba(15,23,42,.22)}
.rr-req-header{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;padding:22px 24px}
.rr-req-header h3{margin:0 0 6px;color:#f8fafc;font-size:1.2rem;font-weight:800}
.rr-req-header p{margin:0;color:#94a3b8;max-width:720px}
.rr-req-stats{display:flex;flex-wrap:wrap;gap:10px}
.rr-req-pill,.rr-req-filter,.rr-req-status{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;font-weight:800;font-size:.78rem}
.rr-req-pill{min-height:36px;padding:0 14px;border:1px solid transparent}
.rr-req-pill--pending{background:rgba(245,158,11,.12);border-color:rgba(245,158,11,.26);color:#fbbf24}
.rr-req-pill--approved{background:rgba(16,185,129,.12);border-color:rgba(16,185,129,.26);color:#34d399}
.rr-req-pill--rejected{background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.22);color:#f87171}
.rr-req-filters{display:flex;flex-wrap:wrap;gap:10px}
.rr-req-filter{min-height:38px;padding:0 14px;text-decoration:none;background:rgba(30,41,59,.72);border:1px solid rgba(148,163,184,.14);color:#cbd5e1}
.rr-req-filter.is-active{background:linear-gradient(135deg,#f97316,#ea580c);border-color:transparent;color:#fff}
.rr-req-list{display:grid;gap:16px}
.rr-req-card{padding:20px;display:grid;gap:16px}
.rr-req-card__top{display:flex;justify-content:space-between;gap:14px;align-items:flex-start}
.rr-req-card__identity{display:flex;gap:14px;align-items:center}
.rr-req-card__identity img{width:68px;height:68px;border-radius:20px;object-fit:cover;border:2px solid rgba(249,115,22,.34)}
.rr-req-card__identity strong{display:block;color:#f8fafc;font-size:1rem}
.rr-req-card__identity span,.rr-req-card__identity small{display:block;color:#94a3b8}
.rr-req-status{min-height:32px;padding:0 12px}
.rr-req-status--pending{background:rgba(245,158,11,.12);color:#fbbf24}
.rr-req-status--approved{background:rgba(16,185,129,.12);color:#34d399}
.rr-req-status--rejected{background:rgba(239,68,68,.12);color:#f87171}
.rr-req-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
.rr-req-field,.rr-req-bio,.rr-req-note,.rr-req-linked{padding:14px 15px;border-radius:16px;background:rgba(30,41,59,.62);border:1px solid rgba(148,163,184,.12)}
.rr-req-field span,.rr-req-bio span,.rr-req-note span,.rr-req-linked span{display:block;margin-bottom:6px;color:#94a3b8;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em}
.rr-req-field strong,.rr-req-linked a{color:#f8fafc}
.rr-req-linked a{text-decoration:none}
.rr-req-bio p,.rr-req-note p{margin:0;color:#e2e8f0;line-height:1.6}
.rr-req-actions{display:flex;flex-wrap:wrap;gap:12px}
.rr-req-btn{border:0;border-radius:14px;min-height:44px;padding:0 16px;font-weight:800;color:#fff}
.rr-req-btn--approve{background:linear-gradient(135deg,#10b981,#059669)}
.rr-req-btn--reject{background:linear-gradient(135deg,#ef4444,#dc2626)}
.rr-req-empty{padding:30px 22px;text-align:center;color:#94a3b8;display:grid;gap:6px}
.rr-req-empty strong{color:#f8fafc}
.rr-req-pagination{padding:18px}
@media (max-width: 920px){.rr-req-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (max-width: 640px){.rr-req-header,.rr-req-card__top,.rr-req-card__identity,.rr-req-actions{flex-direction:column}.rr-req-grid{grid-template-columns:1fr}}
</style>
@endpush

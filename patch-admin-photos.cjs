const fs = require('fs');

const bladeTemplate = `
@extends('admin.layouts.app')

@push('style')
<style>
.rr-photo-shell{display:grid;gap:18px}
.rr-photo-header,.rr-photo-empty,.rr-photo-pagination{background:rgba(15,23,42,.76);border:1px solid rgba(148,163,184,.14);border-radius:20px;box-shadow:0 18px 34px rgba(15,23,42,.22)}
.rr-photo-header{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;padding:22px 24px}
.rr-photo-header h3{margin:0 0 6px;color:#f8fafc;font-size:1.2rem;font-weight:800}
.rr-photo-header p{margin:0;color:#94a3b8;max-width:720px}
.rr-photo-stats{display:flex;flex-wrap:wrap;gap:10px}
.rr-photo-pill,.rr-photo-filter,.rr-photo-status{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;font-weight:800;font-size:.78rem}
.rr-photo-pill{min-height:36px;padding:0 14px;border:1px solid transparent}
.rr-photo-pill--pending{background:rgba(245,158,11,.12);border-color:rgba(245,158,11,.26);color:#fbbf24}
.rr-photo-pill--approved{background:rgba(16,185,129,.12);border-color:rgba(16,185,129,.26);color:#34d399}
.rr-photo-pill--rejected{background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.22);color:#f87171}
.rr-photo-filters{display:flex;flex-wrap:wrap;gap:10px}
.rr-photo-filter{min-height:38px;padding:0 14px;text-decoration:none;background:rgba(30,41,59,.72);border:1px solid rgba(148,163,184,.14);color:#cbd5e1}
.rr-photo-filter.is-active{background:linear-gradient(135deg,#f97316,#ea580c);border-color:transparent;color:#fff}
.rr-photo-status{min-height:28px;padding:0 12px}
.rr-photo-status--pending{background:rgba(245,158,11,.12);color:#fbbf24}
.rr-photo-status--approved{background:rgba(16,185,129,.12);color:#34d399}
.rr-photo-status--rejected{background:rgba(239,68,68,.12);color:#f87171}
.rr-photo-empty{padding:30px 22px;text-align:center;color:#94a3b8;display:grid;gap:6px}
.rr-photo-empty strong{color:#f8fafc}
.rr-photo-pagination{padding:18px}

.rr-table-wrap {
    background: rgba(15,23,42,.76);
    border: 1px solid rgba(148,163,184,.14);
    border-radius: 20px;
    box-shadow: 0 18px 34px rgba(15,23,42,.22);
    overflow-x: auto;
}
.rr-table {
    width: 100%;
    border-collapse: collapse;
    color: #e2e8f0;
}
.rr-table th {
    text-align: left;
    padding: 16px 24px;
    font-size: 0.85rem;
    font-weight: 800;
    text-transform: uppercase;
    color: #94a3b8;
    background: rgba(30,41,59,.62);
    border-bottom: 1px solid rgba(148,163,184,.14);
}
.rr-table td {
    padding: 16px 24px;
    border-bottom: 1px solid rgba(148,163,184,.14);
    vertical-align: middle;
}
.rr-table tr:last-child td { border-bottom: 0; }
.rr-table tbody tr:hover { background: rgba(30,41,59,.42); }
.rr-table-identity {
    display: flex;
    align-items: center;
    gap: 14px;
}
.rr-table-identity img {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(249,115,22,.22);
    background: #020617;
}
.rr-table-identity div strong {
    display: block;
    color: #f8fafc;
    font-weight: 800;
}
.rr-table-identity div span {
    display: block;
    font-size: 0.8rem;
    color: #94a3b8;
}

.rr-modal { position:fixed; inset:0; z-index:100; display:none; padding:18px; background:rgba(2,6,23,.86); backdrop-filter:blur(8px); }
.rr-modal.is-open { display:grid; place-items:center; }
.rr-modal__dialog { width:min(1080px,100%); max-height:min(90vh,920px); overflow:auto; display:grid; gap:16px; padding:24px; background:linear-gradient(180deg,rgba(11,17,33,.98),rgba(5,10,23,.98)); border:1px solid rgba(255,255,255,.08); border-radius:24px; box-shadow:0 28px 60px rgba(2,6,23,.34); }
.rr-modal__head { display:flex; align-items:center; justify-content:space-between; gap:12px; }
.rr-modal__title { margin:0; font-size:1.4rem; color:#fff; font-weight:800; letter-spacing:-.04em; }
.rr-modal__close { width:42px; height:42px; border:0; border-radius:50%; background:rgba(255,255,255,.08); color:#fff; cursor:pointer; }
.rr-photo-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
.rr-photo-preview { padding:14px 15px; border-radius:16px; background:rgba(30,41,59,.62); border:1px solid rgba(148,163,184,.12); text-align:center; }
.rr-photo-preview span { display:block; margin-bottom:8px; color:#94a3b8; font-size:.8rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; }
.rr-photo-preview img { max-width:100%; max-height:400px; object-fit:contain; border-radius:12px; }
.rr-button-group { display:flex; gap:8px; flex-wrap:wrap; }
.rr-btn { min-height:40px; padding:0 16px; border-radius:12px; font-weight:800; color:#fff; border:0; cursor:pointer; display:inline-flex; align-items:center; gap:6px; font-size:0.85rem; text-decoration:none; }
.rr-btn i { font-size: 1.2rem; }
.rr-btn--open { background:linear-gradient(135deg,#3b82f6,#2563eb); }
.rr-btn--approve { background:linear-gradient(135deg,#10b981,#059669); }
.rr-btn--reject { background:linear-gradient(135deg,#ef4444,#dc2626); }
.rr-btn--outline { background:transparent; border:1px solid rgba(148,163,184,.26); color:#cbd5e1; }

@media (max-width: 640px){.rr-photo-header{flex-direction:column} .rr-photo-grid{grid-template-columns:1fr;}}
</style>
@endpush

@section('panel')
<div class="rr-admin-dark">
@include('admin.partials.rr-admin-dark')

<div class="rr-photo-shell">
    <div class="rr-photo-header">
        <div>
            <h3>Aprovar Fotos</h3>
            <p>Revise as fotos de perfil enviadas pelos usuários antes de publicá-las no site.</p>
        </div>
        <div class="rr-photo-stats">
            <span class="rr-photo-pill rr-photo-pill--pending">Pendentes {{ $counts['pending'] }}</span>
            <span class="rr-photo-pill rr-photo-pill--approved">Aprovadas {{ $counts['approved'] }}</span>
            <span class="rr-photo-pill rr-photo-pill--rejected">Rejeitadas {{ $counts['rejected'] }}</span>
        </div>
    </div>

    <div class="rr-photo-filters">
        <a href="{{ route('admin.users.profile_photos.index', ['status' => 'pending']) }}" class="rr-photo-filter {{ $status === 'pending' ? 'is-active' : '' }}">Pendentes</a>
        <a href="{{ route('admin.users.profile_photos.index', ['status' => 'approved']) }}" class="rr-photo-filter {{ $status === 'approved' ? 'is-active' : '' }}">Aprovadas</a>
        <a href="{{ route('admin.users.profile_photos.index', ['status' => 'rejected']) }}" class="rr-photo-filter {{ $status === 'rejected' ? 'is-active' : '' }}">Rejeitadas</a>
        <a href="{{ route('admin.users.profile_photos.index', ['status' => '']) }}" class="rr-photo-filter {{ $status === '' ? 'is-active' : '' }}">Todas</a>
    </div>

    @if($requests->count() > 0)
        <div class="rr-table-wrap">
            <table class="rr-table">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Nova Foto</th>
                        <th>Data Env.</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $requestItem)
                        @php
                            $user = $requestItem->user;
                            $fullName = trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
                            $displayName = $fullName !== '' ? $fullName : ($user->username ?? 'Usuário');
                            $currentAvatar = $user && $user->image ? asset('assets/images/user/profile/' . $user->image) : asset('assets/images/logo_icon/favicon.png');
                            $pendingAvatar = $requestItem->image_url ?: asset('assets/images/logo_icon/favicon.png');
                        @endphp
                        <tr>
                            <td>
                                <div class="rr-table-identity">
                                    <img src="{{ $currentAvatar }}" alt="Foto atual de {{ $displayName }}">
                                    <div>
                                        <strong>{{ $displayName }}</strong>
                                        <span>@{{ $user->username ?? 'sem-username' }}</span>
                                        <span>{{ $user->email ?? '-' }} | {{ $user->mobile ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <img src="{{ $pendingAvatar }}" style="width:54px;height:54px;object-fit:cover;border-radius:12px;border:1px solid rgba(255,255,255,.1);">
                            </td>
                            <td>
                                <div>{{ $requestItem->created_at?->format('d/m/Y') }}</div>
                                <small style="color:#94a3b8;">{{ $requestItem->created_at?->format('H:i') }}</small>
                            </td>
                            <td>
                                <span class="rr-photo-status rr-photo-status--{{ $requestItem->status }}">{{ ucfirst($requestItem->status) }}</span>
                                @if($requestItem->admin_notes && $requestItem->status === 'rejected')
                                    <small style="display:block;margin-top:4px;color:#fca5a5;font-size:0.75rem;max-width:180px;line-height:1.3;">{{ Str::limit($requestItem->admin_notes, 40) }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="rr-button-group">
                                    @if ($requestItem->status === 'pending')
                                        <form method="POST" action="{{ route('admin.users.profile_photos.approve', $requestItem) }}" class="d-inline" style="margin:0;">
                                            @csrf
                                            <input type="hidden" name="admin_notes" value="Foto aprovada pelo time Rei do Rodeio.">
                                            <button type="submit" class="rr-btn rr-btn--approve"><i class="las la-check-circle"></i> Aprovar</button>
                                        </form>
                                        <button type="button" class="rr-btn rr-btn--reject" onclick="openRejectModal({{ $requestItem->id }}, '{{ route('admin.users.profile_photos.reject', $requestItem) }}')"><i class="las la-times-circle"></i> Rejeitar</button>
                                    @endif
                                    
                                    <button type="button" class="rr-btn rr-btn--open" onclick="openPhotoModal('{{ $currentAvatar }}', '{{ $pendingAvatar }}', '{{ $displayName }}')"><i class="las la-image"></i> Abrir Foto</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($requests->hasPages())
            <div class="rr-photo-pagination">
                {{ paginateLinks($requests) }}
            </div>
        @endif
    @else
        <div class="rr-photo-empty">
            <strong>Nenhuma foto encontrada.</strong>
            <span>Quando um usuário enviar uma nova foto, ela aparece aqui para análise.</span>
        </div>
    @endif
</div>
</div>

<!-- Modal Visualizar Foto -->
<div class="rr-modal" id="rrPhotoModal">
    <div class="rr-modal__dialog">
        <div class="rr-modal__head">
            <h3 class="rr-modal__title">Comparar fotos de <span id="rrModalUserName"></span></h3>
            <button type="button" class="rr-modal__close" onclick="closeModal('rrPhotoModal')"><i class="las la-times" style="font-size:1.5rem"></i></button>
        </div>
        <div class="rr-photo-grid">
            <div class="rr-photo-preview">
                <span>Foto Atual no Perfil</span>
                <img id="rrModalCurrentPhoto" src="" alt="Foto atual">
            </div>
            <div class="rr-photo-preview">
                <span>Nova Foto Enviada</span>
                <a id="rrModalNewPhotoLink" href="#" target="_blank" title="Clique para original">
                    <img id="rrModalNewPhoto" src="" alt="Nova foto enviada">
                </a>
            </div>
        </div>
        <div class="rr-button-group" style="justify-content:flex-end;margin-top:10px;">
            <button type="button" class="rr-btn rr-btn--outline" onclick="closeModal('rrPhotoModal')">Voltar</button>
        </div>
    </div>
</div>

<!-- Modal Rejeitar -->
<div class="rr-modal" id="rrRejectModal">
    <div class="rr-modal__dialog" style="max-width:540px;">
        <div class="rr-modal__head">
            <h3 class="rr-modal__title">Rejeitar Foto</h3>
            <button type="button" class="rr-modal__close" onclick="closeModal('rrRejectModal')"><i class="las la-times" style="font-size:1.5rem"></i></button>
        </div>
        <div class="rr-photo-preview" style="text-align:left;">
            <form method="POST" action="" id="rrRejectForm">
                @csrf
                <label style="color:#f8fafc;font-weight:800;display:block;margin-bottom:10px;">Motivo da rejeição (opcional)</label>
                <input type="text" name="admin_notes" style="width:100%;height:46px;background:rgba(15,23,42,.8);border:1px solid rgba(148,163,184,.2);padding:10px 14px;border-radius:12px;color:#fff;outline:none;" placeholder="Ex: Foto de baixa qualidade">
                <div class="rr-button-group" style="justify-content:flex-end;margin-top:16px;">
                    <button type="button" class="rr-btn rr-btn--outline" onclick="closeModal('rrRejectModal')">Cancelar</button>
                    <button type="submit" class="rr-btn rr-btn--reject"><i class="las la-times"></i> Rejeitar Foto</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('script')
<script>
    function openPhotoModal(currentImg, newImg, userName) {
        document.getElementById('rrModalCurrentPhoto').src = currentImg;
        document.getElementById('rrModalNewPhoto').src = newImg;
        document.getElementById('rrModalNewPhotoLink').href = newImg;
        document.getElementById('rrModalUserName').textContent = userName;
        document.getElementById('rrPhotoModal').classList.add('is-open');
    }

    function openRejectModal(reqId, rejectUrl) {
        document.getElementById('rrRejectForm').action = rejectUrl;
        document.getElementById('rrRejectModal').classList.add('is-open');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('is-open');
    }
</script>
@endpush
`;

fs.writeFileSync('resources/views/admin/users/profile_photos/index.blade.php', bladeTemplate);
console.log('Script applied.');

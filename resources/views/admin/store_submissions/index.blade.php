@extends('admin.layouts.app')

@section('panel')
    @php
        $statusLabels = [
            'pending' => 'Pendente',
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            'archived' => 'Arquivado',
        ];
    @endphp

    <div class="rr-store-admin">
        <div class="rr-store-admin__hero">
            <div class="rr-store-admin__hero-copy">
                <span class="rr-store-admin__kicker">Loja da arena</span>
                <h3 class="rr-store-admin__title">Produtos enviados por clientes</h3>
                <p class="rr-store-admin__lead">Revisão rápida dos anúncios recebidos pela loja web.</p>
            </div>

            <div class="rr-store-admin__stats">
                <article class="rr-store-admin__stat">
                    <span>Total</span>
                    <strong>{{ number_format((int) ($stats['total'] ?? 0), 0, ',', '.') }}</strong>
                </article>
                <article class="rr-store-admin__stat rr-store-admin__stat--pending">
                    <span>Pendentes</span>
                    <strong>{{ number_format((int) ($stats['pending'] ?? 0), 0, ',', '.') }}</strong>
                </article>
                <article class="rr-store-admin__stat rr-store-admin__stat--approved">
                    <span>Aprovados</span>
                    <strong>{{ number_format((int) ($stats['approved'] ?? 0), 0, ',', '.') }}</strong>
                </article>
                <article class="rr-store-admin__stat rr-store-admin__stat--rejected">
                    <span>Rejeitados</span>
                    <strong>{{ number_format((int) ($stats['rejected'] ?? 0), 0, ',', '.') }}</strong>
                </article>
            </div>
        </div>

        <div class="card custom--card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label class="form-label">Buscar</label>
                        <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Produto, usuário ou e-mail">
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">Todos</option>
                            @foreach($statusLabels as $value => $label)
                                <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-5">
                        <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                            <button type="submit" class="btn btn--primary h-45">
                                <i class="las la-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('admin.store_submissions.index') }}" class="btn btn--dark h-45">
                                <i class="las la-sync"></i> Limpar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="rr-store-admin__grid">
            @forelse ($submissions as $submission)
                @php
                    $statusKey = (string) $submission->status;
                    $photoUrls = $submission->photo_urls;
                    $platformShare = ((float) $submission->price * (float) $submission->commission_percent) / 100;
                    $sellerNet = (float) $submission->price - $platformShare;
                    $fullName = trim((string) (($submission->user?->firstname ?? '') . ' ' . ($submission->user?->lastname ?? '')));
                @endphp
                <article class="card custom--card rr-store-admin__card">
                    <div class="rr-store-admin__gallery">
                        @if(!empty($photoUrls))
                            <img src="{{ $photoUrls[0] }}" alt="{{ $submission->title }}">
                        @else
                            <div class="rr-store-admin__gallery-empty">
                                <i class="las la-image"></i>
                            </div>
                        @endif
                        <span class="rr-store-admin__badge rr-store-admin__badge--{{ $statusKey }}">
                            {{ $statusLabels[$statusKey] ?? ucfirst($statusKey) }}
                        </span>
                    </div>

                    <div class="card-body rr-store-admin__body">
                        <div class="rr-store-admin__header">
                            <div>
                                <h5 class="rr-store-admin__product-title">{{ $submission->title }}</h5>
                                <div class="rr-store-admin__user">
                                    <strong>{{ $submission->user?->username ?? 'Usuário removido' }}</strong>
                                    @if($fullName !== '')
                                        <span>{{ $fullName }}</span>
                                    @endif
                                    @if($submission->user?->email)
                                        <span>{{ $submission->user->email }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="rr-store-admin__price-box">
                                <strong>{{ $submission->formatted_price }}</strong>
                                <span>{{ number_format((float) $submission->commission_percent, 2, ',', '.') }}% plataforma</span>
                            </div>
                        </div>

                        @if($submission->description)
                            <p class="rr-store-admin__description">{{ $submission->description }}</p>
                        @endif

                        <div class="rr-store-admin__meta-grid">
                            <div>
                                <span>Repasse estimado</span>
                                <strong>R$ {{ number_format($sellerNet, 2, ',', '.') }}</strong>
                            </div>
                            <div>
                                <span>Receita da plataforma</span>
                                <strong>R$ {{ number_format($platformShare, 2, ',', '.') }}</strong>
                            </div>
                            <div>
                                <span>Enviado em</span>
                                <strong>{{ optional($submission->created_at)->format('d/m/Y H:i') }}</strong>
                            </div>
                            <div>
                                <span>Revisado em</span>
                                <strong>{{ optional($submission->reviewed_at)->format('d/m/Y H:i') ?: 'Ainda não' }}</strong>
                            </div>
                        </div>

                        @if(count($photoUrls) > 1)
                            <div class="rr-store-admin__thumbs">
                                @foreach(array_slice($photoUrls, 0, 5) as $photoUrl)
                                    <img src="{{ $photoUrl }}" alt="{{ $submission->title }}">
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.store_submissions.review', $submission) }}" class="rr-store-admin__review-form">
                            @csrf
                            <div class="row g-3">
                                <div class="col-lg-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-control">
                                        @foreach($statusLabels as $value => $label)
                                            <option value="{{ $value }}" {{ $statusKey === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-7">
                                    <label class="form-label">Notas internas</label>
                                    <textarea name="admin_notes" class="form-control" rows="2" placeholder="Observações para operação da loja">{{ $submission->admin_notes }}</textarea>
                                </div>
                                <div class="col-lg-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn--primary w-100 h-45">
                                        <i class="las la-save"></i> Salvar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </article>
            @empty
                <div class="card custom--card">
                    <div class="card-body text-center text-muted py-5">
                        {{ __($emptyMessage) }}
                    </div>
                </div>
            @endforelse
        </div>

        @if($submissions->hasPages())
            <div class="mt-4">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>
@endsection

@push('style')
<style>
.rr-store-admin {
    display: grid;
    gap: 1.25rem;
}

.rr-store-admin__hero {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(420px, .9fr);
    gap: 1rem;
    padding: 1.25rem;
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background:
        radial-gradient(circle at top right, rgba(249, 115, 22, 0.08), transparent 28%),
        linear-gradient(145deg, rgba(20, 24, 38, 0.96), rgba(11, 15, 27, 0.98));
    box-shadow: 0 24px 44px rgba(0, 0, 0, 0.22);
}

.rr-store-admin__kicker {
    display: inline-flex;
    align-items: center;
    padding: .38rem .72rem;
    border-radius: 999px;
    background: rgba(249, 115, 22, 0.14);
    border: 1px solid rgba(249, 115, 22, 0.24);
    color: #fdba74;
    font-size: .74rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.rr-store-admin__title {
    margin: .7rem 0 .35rem;
    color: #f8fafc;
    font-weight: 900;
    font-size: clamp(1.5rem, 2.4vw, 2rem);
}

.rr-store-admin__lead {
    margin: 0;
    color: #94a3b8;
}

.rr-store-admin__stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .85rem;
}

.rr-store-admin__stat {
    border-radius: 18px;
    padding: 1rem;
    background: rgba(12, 18, 32, 0.72);
    border: 1px solid rgba(255, 255, 255, 0.07);
}

.rr-store-admin__stat span {
    display: block;
    color: #94a3b8;
    font-size: .76rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.rr-store-admin__stat strong {
    display: block;
    margin-top: .45rem;
    color: #f8fafc;
    font-size: 1.45rem;
    font-weight: 900;
}

.rr-store-admin__stat--pending { box-shadow: inset 0 0 0 1px rgba(250, 204, 21, 0.14); }
.rr-store-admin__stat--approved { box-shadow: inset 0 0 0 1px rgba(34, 197, 94, 0.14); }
.rr-store-admin__stat--rejected { box-shadow: inset 0 0 0 1px rgba(248, 113, 113, 0.14); }

.rr-store-admin__grid {
    display: grid;
    gap: 1rem;
}

.rr-store-admin__card {
    overflow: hidden;
}

.rr-store-admin__gallery {
    position: relative;
    aspect-ratio: 16 / 7;
    background: rgba(15, 23, 42, 0.92);
    overflow: hidden;
}

.rr-store-admin__gallery img,
.rr-store-admin__thumbs img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.rr-store-admin__gallery-empty {
    width: 100%;
    height: 100%;
    display: grid;
    place-items: center;
    color: #64748b;
    font-size: 2.4rem;
}

.rr-store-admin__badge {
    position: absolute;
    top: .85rem;
    right: .85rem;
    display: inline-flex;
    align-items: center;
    padding: .42rem .78rem;
    border-radius: 999px;
    font-size: .75rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.rr-store-admin__badge--pending {
    color: #fcd34d;
    background: rgba(234, 179, 8, 0.14);
    border: 1px solid rgba(234, 179, 8, 0.24);
}

.rr-store-admin__badge--approved {
    color: #86efac;
    background: rgba(34, 197, 94, 0.14);
    border: 1px solid rgba(34, 197, 94, 0.24);
}

.rr-store-admin__badge--rejected,
.rr-store-admin__badge--archived {
    color: #fca5a5;
    background: rgba(239, 68, 68, 0.14);
    border: 1px solid rgba(239, 68, 68, 0.22);
}

.rr-store-admin__body {
    display: grid;
    gap: 1rem;
}

.rr-store-admin__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.rr-store-admin__product-title {
    margin: 0 0 .4rem;
    color: #f8fafc;
    font-size: 1.2rem;
    font-weight: 800;
}

.rr-store-admin__user {
    display: grid;
    gap: .18rem;
    color: #94a3b8;
    font-size: .9rem;
}

.rr-store-admin__user strong {
    color: #e2e8f0;
}

.rr-store-admin__price-box {
    text-align: right;
}

.rr-store-admin__price-box strong {
    display: block;
    color: #f8fafc;
    font-size: 1.35rem;
    font-weight: 900;
}

.rr-store-admin__price-box span {
    display: block;
    color: #94a3b8;
    font-size: .84rem;
}

.rr-store-admin__description {
    margin: 0;
    color: #cbd5e1;
    line-height: 1.65;
}

.rr-store-admin__meta-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .75rem;
}

.rr-store-admin__meta-grid div {
    border-radius: 16px;
    padding: .9rem;
    background: rgba(15, 23, 42, 0.58);
    border: 1px solid rgba(255, 255, 255, 0.06);
}

.rr-store-admin__meta-grid span {
    display: block;
    color: #94a3b8;
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.rr-store-admin__meta-grid strong {
    display: block;
    margin-top: .45rem;
    color: #f8fafc;
    font-size: 1rem;
}

.rr-store-admin__thumbs {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: .55rem;
}

.rr-store-admin__thumbs img {
    aspect-ratio: 1 / 1;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.rr-store-admin__review-form {
    padding-top: .2rem;
}

@media (max-width: 1199px) {
    .rr-store-admin__hero,
    .rr-store-admin__stats,
    .rr-store-admin__meta-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 767px) {
    .rr-store-admin__hero,
    .rr-store-admin__stats,
    .rr-store-admin__meta-grid,
    .rr-store-admin__thumbs {
        grid-template-columns: 1fr;
    }

    .rr-store-admin__header {
        flex-direction: column;
    }

    .rr-store-admin__price-box {
        text-align: left;
    }
}
</style>
@endpush

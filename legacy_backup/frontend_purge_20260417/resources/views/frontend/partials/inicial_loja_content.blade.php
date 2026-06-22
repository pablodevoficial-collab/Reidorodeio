@php
    $wallet = $storeOverview['wallet'] ?? [];
    $topups = $storeOverview['topups'] ?? [];
    $vouchers = $storeOverview['vouchers'] ?? [];
    $activeVouchers = $storeOverview['active_vouchers'] ?? [];
    $availableBalance = (float) ($wallet['available_balance'] ?? 0);
    $receivableBalance = (float) ($wallet['receivable_balance'] ?? 0);
    $activeVoucherCount = (int) ($wallet['active_vouchers'] ?? count($activeVouchers));
    $pendingPurchases = collect($storePurchases ?? [])->where('status', 'pending')->count();
    $featuredVoucher = collect($vouchers)->first(function ($item) {
        $bundleItems = collect(data_get($item, 'metadata.bundle_vouchers', []));
        return (float) ($item['price'] ?? 0) === 170.0
            || $bundleItems->count() >= 3
            || !empty($item['is_featured']);
    });
    $purchaseStatusLabels = [
        'approved' => 'Aprovado',
        'pending' => 'Pendente',
        'cancelled' => 'Cancelado',
        'expired' => 'Expirado',
        'rejected' => 'Recusado',
    ];
    $storeUserAuthenticated = auth()->check();
@endphp

<style>
.rr-store{--bg:rgba(8,13,27,.92);--card:rgba(15,23,42,.92);--soft:rgba(22,33,61,.82);--border:rgba(96,165,250,.14);--text:#f8fafc;--muted:#8ea2c1;--sub:#cbd5e1;--orange:#f97316;--blue:#2563eb;--green:#22c55e;--gold:#fbbf24;--danger:#f87171;color:var(--text)}
body.light .rr-store{--bg:rgba(255,249,241,.96);--card:rgba(255,255,255,.96);--soft:rgba(255,247,237,.96);--border:rgba(194,65,12,.12);--text:#1f2937;--muted:#7c6a61;--sub:#475569;--orange:#ea580c;--blue:#2563eb;--green:#16a34a;--gold:#d97706;--danger:#dc2626}
.rr-store__shell{display:grid;gap:.85rem}.rr-store__hero,.rr-store__section,.rr-store__modal-dialog,.rr-store__submenu{border:1px solid var(--border);border-radius:26px;background:radial-gradient(circle at top right,rgba(249,115,22,.09),transparent 26%),linear-gradient(135deg,rgba(249,115,22,.08),rgba(37,99,235,.06)),var(--bg);box-shadow:0 20px 48px rgba(2,6,23,.2)}
.rr-store__hero{padding:1rem 1.1rem;display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,400px);gap:.9rem}.rr-store__section{padding:1rem}
.rr-store__submenu{display:flex;flex-wrap:wrap;gap:.7rem;padding:.85rem 1rem;position:sticky;top:calc(var(--hub-navbar-height,63px) + 10px);z-index:8}
.rr-store__submenu-btn{appearance:none;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);color:var(--text);border-radius:999px;min-height:42px;padding:.72rem 1rem;display:inline-flex;align-items:center;justify-content:center;gap:.55rem;font-size:.82rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;cursor:pointer;transition:transform .18s ease,background .18s ease,border-color .18s ease,box-shadow .18s ease}
.rr-store__submenu-btn:hover{transform:translateY(-1px)}
.rr-store__submenu-btn.is-active{background:linear-gradient(135deg,var(--orange),var(--blue));border-color:transparent;color:#fff;box-shadow:0 18px 30px rgba(37,99,235,.2)}
body.light .rr-store__submenu-btn{background:rgba(255,255,255,.88);border-color:rgba(194,65,12,.1)}
body.light .rr-store__submenu-btn.is-active{background:linear-gradient(135deg,#ea580c,#2563eb);color:#fff;border-color:transparent;box-shadow:0 16px 28px rgba(37,99,235,.18)}
.rr-store__eyebrow,.rr-store__section-kicker,.rr-store__pill,.rr-store__status{display:inline-flex;align-items:center;gap:.45rem;width:fit-content;border-radius:999px;padding:.52rem .86rem;font-size:.75rem;font-weight:800;letter-spacing:.11em;text-transform:uppercase}
.rr-store__eyebrow,.rr-store__section-kicker{background:rgba(249,115,22,.12);color:#ffd6bf;border:1px solid rgba(249,115,22,.18)}body.light .rr-store__eyebrow,body.light .rr-store__section-kicker{color:#b45309}
.rr-store__title{margin:.65rem 0 .35rem;font-size:clamp(1.7rem,2.2vw,2.35rem);line-height:1;letter-spacing:-.05em;max-width:12ch}
.rr-store__hero-metrics,.rr-store__grid,.rr-store__side-grid,.rr-store__submissions-grid,.rr-store__preview-grid,.rr-store__field-grid{display:grid;gap:.85rem}
.rr-store__hero-metrics{grid-template-columns:repeat(auto-fit,minmax(150px,220px));justify-content:start;margin-top:.85rem}
.rr-store__metric,.rr-store__product,.rr-store__compact,.rr-store__publish-card,.rr-store__submission-card,.rr-store__pix-card,.rr-store__pix-code,.rr-store__publish-wrap{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:.9rem;min-width:0}
.rr-store__metric span,.rr-store__label,.rr-store__subline{display:block;color:var(--muted);font-size:.76rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
.rr-store__metric strong{display:block;margin-top:.4rem;font-size:1.25rem;line-height:1;letter-spacing:-.04em}.rr-store__metric small,.rr-store__helper,.rr-store__empty,.rr-store__preview-empty,.rr-store__modal-note,.rr-store__publish-copy p,.rr-store__publish-list li{color:var(--sub)}
.rr-store__hero-side{display:grid;gap:.75rem;background:var(--card);border:1px solid var(--border);border-radius:22px;padding:.9rem}
.rr-store__hero-actions,.rr-store__actions,.rr-store__modal-actions{display:flex;flex-wrap:wrap;gap:.75rem}
.rr-store__btn{appearance:none;border:0;border-radius:16px;min-height:42px;padding:.78rem 1rem;display:inline-flex;align-items:center;justify-content:center;gap:.55rem;font-size:.86rem;font-weight:800;text-decoration:none;cursor:pointer;transition:transform .18s ease,opacity .18s ease,box-shadow .18s ease}.rr-store__btn:hover{transform:translateY(-1px)}.rr-store__btn:disabled{cursor:not-allowed;opacity:.5;transform:none}
.rr-store__btn--primary{color:#fff;background:linear-gradient(135deg,var(--orange),var(--blue));box-shadow:0 18px 36px rgba(37,99,235,.24)}.rr-store__btn--wallet{color:#e5f9ee;background:linear-gradient(135deg,#15803d,#22c55e)}.rr-store__btn--ghost{color:var(--text);background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08)}body.light .rr-store__btn--ghost{background:rgba(255,255,255,.92);border-color:rgba(194,65,12,.1)}
.rr-store__purchase-row{display:flex;align-items:center;gap:.55rem;flex-wrap:nowrap}
.rr-store__purchase-row .rr-store__btn{flex:1 1 auto}
.rr-store__qty{position:relative;flex:0 0 auto}
.rr-store__qty-trigger{width:52px;min-width:52px;min-height:42px;padding:.55rem .45rem;border-radius:14px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);color:var(--text);display:inline-flex;align-items:center;justify-content:center;gap:.3rem;font-size:.76rem;font-weight:900;letter-spacing:.05em;cursor:pointer;box-shadow:inset 0 1px 0 rgba(255,255,255,.04)}
.rr-store__qty-trigger i{font-size:.7rem;color:var(--gold)}
.rr-store__qty-menu{position:absolute;top:calc(100% + 8px);left:0;display:none;grid-template-columns:repeat(5,minmax(0,1fr));gap:.35rem;width:196px;padding:.45rem;border-radius:16px;border:1px solid var(--border);background:var(--card);box-shadow:0 18px 34px rgba(2,6,23,.3);z-index:12}
.rr-store__qty.is-open .rr-store__qty-menu{display:grid}
.rr-store__qty-option{min-height:34px;border-radius:12px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.05);color:var(--text);font-size:.78rem;font-weight:900;cursor:pointer}
.rr-store__qty-option.is-active{background:linear-gradient(135deg,var(--orange),var(--blue));border-color:transparent;color:#fff}
.rr-store__qty-note{display:block;color:var(--muted);font-size:.72rem;font-weight:800;letter-spacing:.08em}
.rr-store__qty-total{display:block;color:var(--text);font-size:.78rem;font-weight:900}
.rr-store__section-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:.85rem}.rr-store__section-title{margin:.4rem 0 0;font-size:1.18rem;letter-spacing:-.04em}
.rr-store__grid--catalog{grid-template-columns:repeat(auto-fit,minmax(220px,280px));justify-content:start}.rr-store__grid--split{grid-template-columns:repeat(2,minmax(0,1fr))}
.rr-store__topup-card{display:grid;gap:1rem;padding:1rem;border-radius:22px;background:var(--card);border:1px solid var(--border)}
.rr-store__topup-head h4{margin:0;font-size:1.22rem;letter-spacing:-.04em}
.rr-store__topup-head p{margin:.35rem 0 0;color:var(--sub);line-height:1.5}
.rr-store__topup-grid{display:grid;grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr);gap:1rem}
.rr-store__topup-presets{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.75rem}
.rr-store__topup-preset{appearance:none;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);color:var(--text);border-radius:18px;min-height:78px;padding:.9rem .8rem;display:grid;gap:.18rem;justify-items:start;cursor:pointer;transition:transform .18s ease,border-color .18s ease,box-shadow .18s ease,background .18s ease}
.rr-store__topup-preset:hover{transform:translateY(-1px)}
.rr-store__topup-preset.is-active{border-color:rgba(34,197,94,.36);background:linear-gradient(135deg,rgba(21,128,61,.18),rgba(37,99,235,.12));box-shadow:0 16px 26px rgba(37,99,235,.16)}
.rr-store__topup-preset strong{font-size:1.08rem;line-height:1;letter-spacing:-.04em}
.rr-store__topup-preset span{color:var(--muted);font-size:.72rem;font-weight:800;letter-spacing:.11em;text-transform:uppercase}
.rr-store__topup-custom{display:grid;gap:.65rem;padding:.95rem;border-radius:20px;background:var(--soft);border:1px solid var(--border)}
.rr-store__topup-custom label{color:var(--sub);font-size:.76rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
.rr-store__topup-input{width:100%;border-radius:18px;border:1px solid var(--border);background:rgba(2,6,23,.35);color:var(--text);padding:1rem 1rem;font-size:1rem;font-weight:800}
body.light .rr-store__topup-input{background:rgba(255,255,255,.92)}
.rr-store__topup-note{color:var(--muted);font-size:.82rem;line-height:1.45}
.rr-store__product{display:grid;gap:.7rem;align-content:start}.rr-store__cover{position:relative;min-height:94px;border-radius:18px;padding:.8rem;display:flex;align-items:center;justify-content:space-between;overflow:hidden;background:radial-gradient(circle at top right,rgba(255,255,255,.22),transparent 30%),linear-gradient(145deg,rgba(15,23,42,.92),rgba(9,14,28,1))}body.light .rr-store__cover{background:radial-gradient(circle at top right,rgba(255,255,255,.7),transparent 32%),linear-gradient(145deg,rgba(255,245,235,.98),rgba(255,232,214,.98))}
.rr-store__cover::after{content:'';position:absolute;inset:auto -16% -36% auto;width:120px;height:120px;border-radius:999px;background:rgba(249,115,22,.14);filter:blur(12px)}
.rr-store__cover-icon{width:46px;height:46px;border-radius:16px;display:inline-flex;align-items:center;justify-content:center;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);font-size:1.05rem;color:#fff7ed}body.light .rr-store__cover-icon{background:rgba(255,255,255,.84);color:#c2410c;border-color:rgba(194,65,12,.12)}
.rr-store__cover-price{text-align:right;position:relative;z-index:1}.rr-store__cover-price span{display:block;color:rgba(226,232,240,.72);font-size:.68rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}body.light .rr-store__cover-price span{color:#9a3412}.rr-store__cover-price strong{display:block;margin-top:.25rem;font-size:1.45rem;line-height:1;letter-spacing:-.05em}
.rr-store__top{display:flex;align-items:flex-start;justify-content:space-between;gap:.65rem}.rr-store__card-title{margin:0;font-size:.98rem;line-height:1.2;letter-spacing:-.03em}.rr-store__subline{margin-top:.28rem;font-size:.68rem}
.rr-store__pill{color:var(--text);background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08)}body.light .rr-store__pill{background:rgba(255,255,255,.88);border-color:rgba(194,65,12,.1)}
.rr-store__pill--green{color:#d1fae5;background:rgba(34,197,94,.14);border-color:rgba(34,197,94,.24)}.rr-store__pill--gold{color:#fde68a;background:rgba(251,191,36,.14);border-color:rgba(251,191,36,.24)}.rr-store__pill--danger{color:#fecaca;background:rgba(248,113,113,.12);border-color:rgba(248,113,113,.2)}body.light .rr-store__pill--green{color:#166534}body.light .rr-store__pill--gold{color:#a16207}body.light .rr-store__pill--danger{color:#b91c1c}
.rr-store__benefits{display:grid;gap:.32rem;margin:0;padding:0}.rr-store__benefits li{list-style:none;color:var(--sub);font-size:.82rem;line-height:1.35}
.rr-store__compact-list{display:grid;gap:.8rem}.rr-store__status{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);color:var(--text)}body.light .rr-store__status{background:rgba(255,255,255,.9);border-color:rgba(194,65,12,.1)}.rr-store__status--approved{color:#86efac}.rr-store__status--pending{color:#fde68a}.rr-store__status--cancelled,.rr-store__status--expired,.rr-store__status--rejected{color:#fca5a5}body.light .rr-store__status--approved{color:#166534}body.light .rr-store__status--pending{color:#a16207}body.light .rr-store__status--cancelled,body.light .rr-store__status--expired,body.light .rr-store__status--rejected{color:#b91c1c}
.rr-store__publish-card{display:grid;grid-template-columns:minmax(0,1fr) minmax(260px,320px);gap:1rem}.rr-store__publish-copy h4{margin:.55rem 0 .3rem;font-size:1.18rem;letter-spacing:-.04em}.rr-store__publish-list{display:grid;gap:.4rem;margin:.8rem 0 0;padding:0}.rr-store__publish-list li{list-style:none;font-size:.85rem}.rr-store__side-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
.rr-store__premium-panel{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(240px,.95fr);gap:1rem;align-items:stretch}
.rr-store__premium-panel-copy h4{margin:.5rem 0 .35rem;font-size:1.18rem;letter-spacing:-.04em}
.rr-store__premium-panel-copy p{margin:0;color:var(--sub);line-height:1.55}
.rr-store__premium-list{display:grid;gap:.45rem;margin:.85rem 0 0;padding:0}
.rr-store__premium-list li{list-style:none;color:var(--sub);font-size:.84rem}
.rr-store__premium-box{display:grid;gap:.75rem;align-content:start;padding:.95rem;border-radius:20px;background:var(--card);border:1px solid var(--border)}
.rr-store__premium-box strong{font-size:1.1rem;letter-spacing:-.03em}
.rr-store__premium-box p{margin:0;color:var(--sub);font-size:.88rem;line-height:1.5}
.rr-store__side-grid div{border-radius:18px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);padding:.9rem}body.light .rr-store__side-grid div{background:rgba(255,255,255,.88);border-color:rgba(194,65,12,.08)}.rr-store__side-grid strong{display:block;margin-top:.5rem;font-size:1.25rem}
.rr-store__submissions-grid{grid-template-columns:repeat(3,minmax(0,1fr));margin-top:1rem}.rr-store__submission-media{aspect-ratio:16/10;border-radius:18px;overflow:hidden;background:rgba(15,23,42,.92)}.rr-store__submission-media img{width:100%;height:100%;object-fit:cover}.rr-store__submission-empty{width:100%;height:100%;display:grid;place-items:center;color:#64748b;font-size:2rem}
.rr-store__modal{position:fixed;inset:0;z-index:1500;display:none}.rr-store__modal.is-open{display:block}.rr-store__modal-backdrop{position:absolute;inset:0;background:rgba(2,6,23,.72);backdrop-filter:blur(8px)}
.rr-store__modal-dialog{position:absolute;inset:50% auto auto 50%;transform:translate(-50%,-50%);width:min(960px,calc(100vw - 24px));max-height:calc(100dvh - 32px);overflow:hidden;display:flex;flex-direction:column}.rr-store__modal-head,.rr-store__modal-foot{padding:1rem 1.15rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;border-bottom:1px solid var(--border)}.rr-store__modal-foot{border-bottom:0;border-top:1px solid var(--border)}.rr-store__modal-body{padding:1rem 1.15rem 1.15rem;overflow:auto}.rr-store__modal-body::-webkit-scrollbar{width:0;height:0}.rr-store__modal-close{width:42px;height:42px;border-radius:16px;border:1px solid var(--border);background:rgba(255,255,255,.05);color:var(--text);cursor:pointer}
.rr-store__pix-grid{display:grid;grid-template-columns:minmax(260px,.9fr) minmax(0,1.1fr);gap:1rem}.rr-store__pix-card,.rr-store__pix-code,.rr-store__publish-wrap{background:var(--soft)}.rr-store__pix-image{aspect-ratio:1/1;max-width:280px;margin:0 auto;padding:1rem;border-radius:18px;background:#fff}.rr-store__pix-image img{width:100%;height:100%;object-fit:contain}.rr-store__pix-text{width:100%;min-height:120px;border-radius:16px;background:rgba(2,6,23,.42);border:1px solid var(--border);color:var(--text);padding:.9rem 1rem;font-size:.92rem;line-height:1.6;word-break:break-word}
.rr-store__publish-form{display:grid;gap:1rem}.rr-store__field-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.rr-store__field{display:grid;gap:.45rem}.rr-store__field--full{grid-column:1/-1}.rr-store__field label{color:var(--sub);font-size:.78rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
.rr-store__field input,.rr-store__field textarea{width:100%;border-radius:18px;border:1px solid var(--border);background:rgba(2,6,23,.35);color:var(--text);padding:.95rem 1rem;font-size:.95rem}body.light .rr-store__field input,body.light .rr-store__field textarea{background:rgba(255,255,255,.92)}.rr-store__field textarea{min-height:140px;resize:vertical}
.rr-store__upload-input{width:100%;border-radius:18px;border:1px dashed rgba(249,115,22,.28);background:rgba(249,115,22,.08);color:var(--text);padding:1rem}.rr-store__preview-grid{grid-template-columns:repeat(6,minmax(0,1fr))}.rr-store__preview-item{aspect-ratio:1/1;border-radius:16px;overflow:hidden;border:1px solid var(--border);background:rgba(255,255,255,.05)}.rr-store__preview-item img{width:100%;height:100%;object-fit:cover}
@media (max-width:1180px){.rr-store__hero,.rr-store__grid--catalog,.rr-store__grid--split,.rr-store__publish-card,.rr-store__submissions-grid,.rr-store__pix-grid,.rr-store__premium-panel,.rr-store__topup-grid{grid-template-columns:1fr}}
@media (max-width:767px){.rr-store__shell{gap:.7rem}.rr-store__hero,.rr-store__section,.rr-store__submenu{padding:1rem;border-radius:22px}.rr-store__hero-metrics,.rr-store__side-grid,.rr-store__field-grid,.rr-store__preview-grid,.rr-store__topup-presets{grid-template-columns:1fr}.rr-store__modal-dialog{width:calc(100vw - 12px);max-height:calc(100dvh - 12px);inset:auto 6px 6px 6px;transform:none;border-radius:22px}.rr-store__hero-actions,.rr-store__actions,.rr-store__modal-actions{flex-direction:column}.rr-store__btn{width:100%}.rr-store__purchase-row{width:100%}.rr-store__purchase-row .rr-store__btn{width:auto}.rr-store__qty-menu{left:auto;right:0}.rr-store__submenu{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.55rem;position:relative;top:auto;z-index:1;padding:.8rem .8rem .75rem;border-radius:0 0 22px 22px;border-top:0;box-shadow:0 14px 24px rgba(2,6,23,.16)}.rr-store__submenu-btn{width:100%;min-height:48px;padding:.72rem .55rem;font-size:.7rem;letter-spacing:.06em}}
body.light .rr-store__section-title,
body.light .rr-store__topup-head h4,
body.light .rr-store__card-title,
body.light .rr-store__cover-price strong,
body.light .rr-store__topup-preset strong{color:#1f2937}
body.light .rr-store__topup-head p,
body.light .rr-store__helper,
body.light .rr-store__topup-note,
body.light .rr-store__benefits li,
body.light .rr-store__subline{color:#64748b}
body.light .rr-store__topup-preset{background:rgba(255,255,255,.92);border-color:rgba(194,65,12,.1)}
body.light .rr-store__topup-preset.is-active{background:linear-gradient(135deg,rgba(21,128,61,.14),rgba(37,99,235,.1));border-color:rgba(34,197,94,.28);box-shadow:0 14px 22px rgba(37,99,235,.12)}
body.light .rr-store__topup-input{color:#1f2937;border-color:rgba(194,65,12,.12)}
body.light .rr-store__topup-custom label,
body.light .rr-store__metric span,
body.light .rr-store__label{color:#7c6a61}
</style>

<div
    id="rrStoreHub"
    class="rr-store"
    data-purchase-template="{{ route('web.store.purchase', ['product' => '__PRODUCT__']) }}"
    data-topup-url="{{ route('web.store.topup') }}"
    data-status-template="{{ route('web.store.status', ['purchase' => '__PURCHASE__']) }}"
    data-cancel-template="{{ route('web.store.cancel', ['purchase' => '__PURCHASE__']) }}"
    data-store-home="{{ route('hub.loja') }}"
    data-csrf="{{ csrf_token() }}"
    data-user-authenticated="{{ $storeUserAuthenticated ? '1' : '0' }}"
    data-available-balance="{{ number_format($availableBalance, 2, '.', '') }}"
>
    <div class="rr-store__shell">
        <div class="rr-store__submenu" id="rrStoreSubmenu">
            <button type="button" class="rr-store__submenu-btn is-active" data-store-panel-target="saldo">
                <i class="fas fa-wallet"></i>
                <span>Saldo</span>
            </button>
            <button type="button" class="rr-store__submenu-btn" data-store-panel-target="bolao">
                <i class="fas fa-ticket-alt"></i>
                <span>Bolão</span>
            </button>
        </div>

        <section class="rr-store__section" data-store-panel="saldo">
            <div class="rr-store__section-head">
                <div>
                    <h3 class="rr-store__section-title">Recarga rápida</h3>
                </div>
            </div>
            <div class="rr-store__topup-card">
                <div class="rr-store__topup-head">
                    <h4>Coloque saldo na carteira</h4>
                    <p>Escolha um valor rápido ou digite uma recarga personalizada. Ao clicar em gerar PIX, o checkout abre na hora como já funciona hoje.</p>
                </div>

                <div class="rr-store__topup-grid">
                    <div class="rr-store__topup-presets">
                        <button type="button" class="rr-store__topup-preset" data-store-topup-amount="20">
                            <span>Recarga</span>
                            <strong>R$ 20</strong>
                        </button>
                        <button type="button" class="rr-store__topup-preset" data-store-topup-amount="50">
                            <span>Recarga</span>
                            <strong>R$ 50</strong>
                        </button>
                        <button type="button" class="rr-store__topup-preset" data-store-topup-amount="100">
                            <span>Recarga</span>
                            <strong>R$ 100</strong>
                        </button>
                    </div>

                    <div class="rr-store__topup-custom">
                        <label for="rrStoreTopupCustomAmount">Valor personalizado</label>
                        <input
                            id="rrStoreTopupCustomAmount"
                            class="rr-store__topup-input"
                            type="number"
                            min="20"
                            max="5000"
                            step="0.01"
                            placeholder="Ex.: 275,00"
                        >
                        <div class="rr-store__topup-note">Recarga mínima de R$ 20,00 e máxima de R$ 5.000,00 por operação.</div>
                    </div>
                </div>

                <div class="rr-store__actions">
                    <button type="button" class="rr-store__btn rr-store__btn--primary" id="rrStoreGenerateTopupPix">
                        <i class="fas fa-qrcode"></i>
                        <span>Gerar PIX</span>
                    </button>
                </div>
            </div>
        </section>

        <section class="rr-store__section" data-store-panel="bolao" hidden>
            <div class="rr-store__section-head">
                <div>
                    <span class="rr-store__section-kicker"><i class="fas fa-ticket-alt"></i> Bilhetes</span>
                    <h3 class="rr-store__section-title">Bilhetes para bolão</h3>
                </div>
            </div>

            <div class="rr-store__grid rr-store__grid--catalog">
                @foreach ($vouchers as $item)
                    @php
                        $canUseWallet = in_array('wallet', $item['payment_methods'] ?? [], true);
                        $hasBalance = $availableBalance >= (float) $item['price'];
                        $bundleItems = data_get($item, 'metadata.bundle_vouchers', []);
                    @endphp
                    <article class="rr-store__product">
                        <div class="rr-store__cover">
                            <span class="rr-store__cover-icon"><i class="fas fa-ticket-alt"></i></span>
                            <div class="rr-store__cover-price">
                                <span>Bilhete</span>
                                <strong>{{ $item['formatted_price'] }}</strong>
                            </div>
                        </div>
                        <div class="rr-store__top">
                            <div>
                                <h4 class="rr-store__card-title">{{ $item['title'] }}</h4>
                                @if(!empty($item['subtitle']))
                                    <div class="rr-store__subline">{{ $item['subtitle'] }}</div>
                                @endif
                            </div>
                            <div class="rr-store__compact-list">
                                @if(!empty($item['badge']))
                                    <span class="rr-store__pill {{ $item['is_featured'] ? 'rr-store__pill--gold' : '' }}">{{ $item['badge'] }}</span>
                                @endif
                                @if(($item['owned_active_count'] ?? 0) > 0)
                                    <span class="rr-store__pill rr-store__pill--green">{{ $item['owned_active_count'] }} ativo(s)</span>
                                @endif
                            </div>
                        </div>
                        <ul class="rr-store__benefits">
                            <li>{{ data_get($item, 'metadata.bonus_copy', 'Entrada pronta para o bolão compatível.') }}</li>
                            @foreach($bundleItems as $bundleItem)
                                <li>{{ $bundleItem['title'] ?? 'Bilhete do combo' }}</li>
                            @endforeach
                        </ul>
                        <div class="rr-store__actions">
                            <div class="rr-store__purchase-row">
                                <div class="rr-store__qty" data-store-qty-wrap="{{ $item['id'] }}">
                                    <button type="button" class="rr-store__qty-trigger" data-store-qty-trigger="{{ $item['id'] }}" aria-label="Selecionar quantidade de bilhetes">
                                        <i class="fas fa-layer-group"></i>
                                        <span data-store-qty-display="{{ $item['id'] }}">1x</span>
                                    </button>
                                    <div class="rr-store__qty-menu" data-store-qty-menu="{{ $item['id'] }}">
                                        @for ($quantity = 1; $quantity <= (int) ($item['max_quantity'] ?? 10); $quantity++)
                                            <button type="button" class="rr-store__qty-option{{ $quantity === 1 ? ' is-active' : '' }}" data-store-qty-option="{{ $item['id'] }}" data-quantity="{{ $quantity }}">{{ $quantity }}</button>
                                        @endfor
                                    </div>
                                </div>
                                <button type="button" class="rr-store__btn rr-store__btn--primary" data-store-product="{{ $item['id'] }}" data-payment-method="pix" data-store-unit-price="{{ number_format((float) $item['price'], 2, '.', '') }}">
                                    <i class="fas fa-qrcode"></i>
                                    <span>Gerar PIX</span>
                                </button>
                            </div>
                            @if($canUseWallet)
                                <button type="button" class="rr-store__btn rr-store__btn--wallet" data-store-product="{{ $item['id'] }}" data-payment-method="wallet" data-store-unit-price="{{ number_format((float) $item['price'], 2, '.', '') }}" {{ $hasBalance ? '' : 'disabled' }}>
                                    <i class="fas fa-wallet"></i>
                                    <span>{{ $hasBalance ? 'Usar carteira' : 'Saldo insuficiente' }}</span>
                                </button>
                            @endif
                        </div>
                        <div>
                            <span class="rr-store__qty-note">Quantidade selecionada</span>
                            <span class="rr-store__qty-total" data-store-qty-total="{{ $item['id'] }}">{{ $item['formatted_price'] }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

    </div>

    <div class="rr-store__modal" id="rrStoreModal" aria-hidden="true">
        <div class="rr-store__modal-backdrop" data-store-close-modal></div>
        <div class="rr-store__modal-dialog" role="dialog" aria-modal="true" aria-label="Pagamento da loja">
            <div class="rr-store__modal-head">
                <span class="rr-store__section-kicker"><i class="fas fa-qrcode"></i> Checkout da loja</span>
                <button type="button" class="rr-store__modal-close" data-store-close-modal aria-label="Fechar">×</button>
            </div>
            <div class="rr-store__modal-body" id="rrStoreModalBody"></div>
        </div>
    </div>

</div>

<script>
(function() {
    if (window.RRStore && typeof window.RRStore.destroy === 'function') {
        window.RRStore.destroy();
    }

    function createStoreController() {
        let root = null;
        let modal = null;
        let modalBody = null;
        let panelButtons = [];
        let panels = [];
        let topupInput = null;
        let topupSubmitBtn = null;
        let topupPresetButtons = [];
        let pollTimer = null;
        let keydownHandler = null;
        const selectedQuantities = new Map();

        function replaceTemplate(template, value) {
            return String(template || '').replace(/__PRODUCT__|__PURCHASE__/g, value);
        }

        function csrf() {
            return root?.dataset?.csrf || '';
        }

        function jsonHeaders() {
            return {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest'
            };
        }

        function showSuccess(message) {
            if (typeof window.showSuccessToast === 'function') {
                window.showSuccessToast(message);
                return;
            }

            alert(message);
        }

        function showError(message) {
            if (typeof window.showErrorToast === 'function') {
                window.showErrorToast(message);
                return;
            }

            alert(message);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function normalizeQrSrc(input) {
            if (!input) {
                return '';
            }

            if (String(input).startsWith('data:image')) {
                return input;
            }

            return 'data:image/png;base64,' + input;
        }

        function extractErrorMessage(data) {
            if (data?.message) {
                return data.message;
            }

            if (data?.error) {
                return data.error;
            }

            if (data?.errors && typeof data.errors === 'object') {
                const firstGroup = Object.values(data.errors)[0];
                if (Array.isArray(firstGroup) && firstGroup[0]) {
                    return firstGroup[0];
                }
            }

            return 'Não foi possível concluir a operação.';
        }

        function activatePanel(panelName) {
            panelButtons.forEach(function(button) {
                button.classList.toggle('is-active', button.dataset.storePanelTarget === panelName);
            });

            panels.forEach(function(panel) {
                panel.hidden = panel.dataset.storePanel !== panelName;
            });
        }

        function setTopupAmount(amount) {
            if (!topupInput) {
                return;
            }

            const normalizedAmount = amount ? String(amount) : '';
            topupInput.value = normalizedAmount;

            topupPresetButtons.forEach(function(button) {
                button.classList.toggle('is-active', String(button.dataset.storeTopupAmount || '') === normalizedAmount);
            });
        }

        function stopPolling() {
            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }
        }

        function openModal(html) {
            if (!modal || !modalBody) {
                return;
            }

            modalBody.innerHTML = html;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('rr-store-modal-open');
        }

        function closeModal() {
            stopPolling();

            if (!modal || !modalBody) {
                return;
            }

            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            modalBody.innerHTML = '';
            document.body.classList.remove('rr-store-modal-open');
        }

        function renderLoading(message) {
            openModal(
                '<div class="rr-store__pix-card">' +
                    '<div class="rr-store__section-title" style="margin:0 0 .7rem;">Gerando pagamento</div>' +
                    '<p class="rr-store__helper">' + escapeHtml(message || 'Aguarde...') + '</p>' +
                '</div>'
            );
        }

        function refreshStoreTab() {
            stopPolling();

            if (typeof window.switchHubTab === 'function') {
                window.switchHubTab('loja');
                return;
            }

            window.location.href = root?.dataset?.storeHome || '{{ route('hub.loja') }}';
        }

        function isAuthenticated() {
            return root?.dataset?.userAuthenticated === '1';
        }

        function promptAuth(requiredAction) {
            const authMessage = requiredAction
                ? 'Faça login ou crie sua conta para ' + requiredAction + '.'
                : 'Faça login ou crie sua conta para continuar.';

            if (typeof window.openAuthModal === 'function') {
                window.openAuthModal();
            } else if (window.RRAuthModal?.open) {
                window.RRAuthModal.open();
            } else {
                showError(authMessage);
                return false;
            }

            showError(authMessage);
            return false;
        }

        function getAvailableBalance() {
            return Number(root?.dataset?.availableBalance || 0);
        }

        function getSelectedQuantity(productId) {
            const normalizedProductId = String(productId || '');
            return selectedQuantities.get(normalizedProductId) || 1;
        }

        function closeAllQuantityMenus() {
            root?.querySelectorAll('[data-store-qty-wrap]').forEach(function(wrap) {
                wrap.classList.remove('is-open');
            });
        }

        function syncProductQuantityUi(productId) {
            const normalizedProductId = String(productId || '');
            const quantity = getSelectedQuantity(normalizedProductId);

            root?.querySelectorAll('[data-store-qty-display="' + normalizedProductId + '"]').forEach(function(display) {
                display.textContent = quantity + 'x';
            });

            root?.querySelectorAll('[data-store-qty-option="' + normalizedProductId + '"]').forEach(function(option) {
                option.classList.toggle('is-active', Number(option.dataset.quantity || 0) === quantity);
            });

            root?.querySelectorAll('[data-store-qty-total="' + normalizedProductId + '"]').forEach(function(totalLabel) {
                const actionButton = root.querySelector('[data-store-product="' + normalizedProductId + '"][data-store-unit-price]');
                const unitPrice = Number(actionButton?.dataset?.storeUnitPrice || 0);
                const total = unitPrice * quantity;

                totalLabel.textContent = 'R$ ' + total.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            });

            root?.querySelectorAll('[data-store-product="' + normalizedProductId + '"][data-payment-method="wallet"]').forEach(function(button) {
                const unitPrice = Number(button.dataset.storeUnitPrice || 0);
                const total = unitPrice * quantity;
                const hasBalance = getAvailableBalance() >= total;
                const label = button.querySelector('span');

                button.disabled = !hasBalance;
                if (label) {
                    label.textContent = hasBalance ? 'Usar carteira' : 'Saldo insuficiente';
                }
            });
        }

        function setProductQuantity(productId, quantity) {
            const normalizedProductId = String(productId || '');
            const normalizedQuantity = Math.min(10, Math.max(1, Number(quantity || 1)));

            selectedQuantities.set(normalizedProductId, normalizedQuantity);
            syncProductQuantityUi(normalizedProductId);
        }

        function renderPixModal(purchase, helperMessage) {
            const qrSrc = normalizeQrSrc(purchase.qr_code_base64);
            const qrMarkup = qrSrc
                ? '<div class="rr-store__pix-image"><img src="' + escapeHtml(qrSrc) + '" alt="QR Code PIX"></div>'
                : '<div class="rr-store__pix-card"><p class="rr-store__helper">QR Code indisponível. Use o copia e cola abaixo.</p></div>';

            openModal(
                '<div class="rr-store__pix-grid">' +
                    '<div class="rr-store__pix-card">' +
                        qrMarkup +
                        '<div class="rr-store__hero-actions" style="margin-top:1rem;">' +
                            '<span class="rr-store__pill"><i class="fas fa-receipt"></i> ' + escapeHtml(purchase.product?.title || purchase.description || 'Compra da loja') + '</span>' +
                            '<span class="rr-store__pill"><i class="fas fa-layer-group"></i> ' + escapeHtml(String(purchase.quantity || 1)) + ' bilhete(s)</span>' +
                            '<span class="rr-store__pill rr-store__pill--green"><i class="fas fa-wallet"></i> ' + escapeHtml(purchase.formatted_amount || '') + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="rr-store__pix-code">' +
                        '<div class="rr-store__section-title" style="margin:0 0 .65rem;">Pague este PIX</div>' +
                        '<p class="rr-store__helper">' + escapeHtml(helperMessage || 'Assim que o pagamento for aprovado, a loja atualiza sozinha.') + '</p>' +
                        '<textarea class="rr-store__pix-text" readonly>' + escapeHtml(purchase.qr_code || '') + '</textarea>' +
                        '<div class="rr-store__modal-actions" style="margin-top:1rem;">' +
                            '<button type="button" class="rr-store__btn rr-store__btn--primary" data-store-copy-pix="' + purchase.id + '">' +
                                '<i class="fas fa-copy"></i><span>Copiar PIX</span>' +
                            '</button>' +
                            '<button type="button" class="rr-store__btn rr-store__btn--ghost" data-store-check-pix="' + purchase.id + '">' +
                                '<i class="fas fa-rotate"></i><span>Verificar agora</span>' +
                            '</button>' +
                            '<button type="button" class="rr-store__btn rr-store__btn--ghost" data-store-cancel-purchase="' + purchase.id + '">' +
                                '<i class="fas fa-times"></i><span>Cancelar</span>' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            );

            stopPolling();
            pollTimer = setInterval(function() {
                checkPurchaseStatus(purchase.id, false);
            }, 4000);
        }

        async function parseJsonResponse(response) {
            let data = null;

            try {
                data = await response.json();
            } catch (error) {
                data = null;
            }

            if (!response.ok) {
                throw new Error(extractErrorMessage(data));
            }

            return data;
        }

        async function purchaseProduct(productId, paymentMethod, button) {
            if (!productId || !root) {
                return;
            }

            if (!isAuthenticated()) {
                promptAuth(paymentMethod === 'wallet' ? 'usar a carteira' : 'comprar na loja');
                return;
            }

            if (button) {
                button.disabled = true;
            }

            try {
                const quantity = getSelectedQuantity(productId);
                const response = await fetch(replaceTemplate(root.dataset.purchaseTemplate, productId), {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: JSON.stringify({
                        payment_method: paymentMethod,
                        quantity: quantity,
                    })
                });
                const data = await parseJsonResponse(response);
                const purchase = data?.data;

                if (!purchase) {
                    throw new Error('Compra inválida retornada pela loja.');
                }

                if (purchase.status === 'approved') {
                    closeModal();
                    showSuccess(data.message || 'Compra aprovada com sucesso.');
                    refreshStoreTab();
                    return;
                }

                renderPixModal(purchase, data.message || 'PIX gerado com sucesso.');
            } catch (error) {
                showError(error.message || 'Não foi possível gerar a compra.');
            } finally {
                if (button) {
                    button.disabled = false;
                }
            }
        }

        async function createTopupPurchase(amount, button) {
            if (!root) {
                return;
            }

            if (!isAuthenticated()) {
                promptAuth('adicionar saldo');
                return;
            }

            const numericAmount = Number(String(amount || '').replace(',', '.'));
            if (!Number.isFinite(numericAmount) || numericAmount < 20 || numericAmount > 5000) {
                showError('Informe um valor entre R$ 20,00 e R$ 5.000,00.');
                return;
            }

            if (button) {
                button.disabled = true;
            }

            try {
                renderLoading('Gerando PIX da recarga...');

                const response = await fetch(root.dataset.topupUrl, {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: JSON.stringify({ amount: numericAmount })
                });

                const data = await parseJsonResponse(response);
                const purchase = data?.data;

                if (!purchase) {
                    throw new Error('Não foi possível gerar a recarga.');
                }

                renderPixModal(purchase, data.message || 'Assim que o pagamento for aprovado, o saldo entra na carteira automaticamente.');
            } catch (error) {
                closeModal();
                showError(error.message || 'Não foi possível gerar o PIX da recarga.');
            } finally {
                if (button) {
                    button.disabled = false;
                }
            }
        }

        async function checkPurchaseStatus(purchaseId, manualCheck) {
            if (!purchaseId || !root) {
                return;
            }

            try {
                const response = await fetch(replaceTemplate(root.dataset.statusTemplate, purchaseId), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await parseJsonResponse(response);
                const purchase = data?.data;

                if (!purchase) {
                    return;
                }

                if (purchase.status === 'approved') {
                    closeModal();
                    showSuccess('Compra aprovada com sucesso.');
                    refreshStoreTab();
                    return;
                }

                if (['cancelled', 'expired', 'rejected'].includes(String(purchase.status))) {
                    closeModal();
                    showError('Este pagamento não está mais disponível.');
                    refreshStoreTab();
                    return;
                }

                if (manualCheck) {
                    showSuccess('Pagamento ainda pendente.');
                }
            } catch (error) {
                if (manualCheck) {
                    showError(error.message || 'Não foi possível verificar o pagamento.');
                }
            }
        }

        async function cancelPurchase(purchaseId) {
            if (!purchaseId || !root) {
                return;
            }

            try {
                const response = await fetch(replaceTemplate(root.dataset.cancelTemplate, purchaseId), {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: JSON.stringify({})
                });
                const data = await parseJsonResponse(response);
                closeModal();
                showSuccess(data.message || 'Pagamento cancelado.');
                refreshStoreTab();
            } catch (error) {
                showError(error.message || 'Não foi possível cancelar o pagamento.');
            }
        }

        async function copyPixCode() {
            const textarea = modalBody?.querySelector('.rr-store__pix-text');
            if (!textarea) {
                return;
            }

            try {
                await navigator.clipboard.writeText(textarea.value || '');
                showSuccess('Código PIX copiado.');
            } catch (error) {
                textarea.select();
                showError('Copie manualmente o código PIX.');
            }
        }

        function bindEvents() {
            if (!root) {
                return;
            }

            root.querySelectorAll('[data-store-product]').forEach(function(button) {
                button.addEventListener('click', function() {
                    purchaseProduct(button.dataset.storeProduct, button.dataset.paymentMethod, button);
                });
            });

            root.querySelectorAll('[data-store-view-purchase]').forEach(function(button) {
                button.addEventListener('click', function() {
                    renderLoading('Buscando os dados mais recentes deste PIX...');
                    fetch(replaceTemplate(root.dataset.statusTemplate, button.dataset.storeViewPurchase), {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(parseJsonResponse)
                        .then(function(data) {
                            if (!data?.data) {
                                throw new Error('Pagamento não encontrado.');
                            }

                            if (data.data.status === 'approved') {
                                closeModal();
                                showSuccess('Compra já aprovada.');
                                refreshStoreTab();
                                return;
                            }

                            renderPixModal(data.data, 'PIX ainda em aberto. Você pode concluir ou verificar novamente.');
                        })
                        .catch(function(error) {
                            closeModal();
                            showError(error.message || 'Não foi possível abrir este pagamento.');
                        });
                });
            });

            root.querySelectorAll('[data-store-switch]').forEach(function(button) {
                button.addEventListener('click', function() {
                    if (typeof window.switchHubTab === 'function') {
                        window.switchHubTab(button.dataset.storeSwitch);
                    }
                });
            });

            root.querySelectorAll('[data-store-qty-trigger]').forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    const wrap = root.querySelector('[data-store-qty-wrap="' + button.dataset.storeQtyTrigger + '"]');
                    const nextState = !wrap?.classList.contains('is-open');
                    closeAllQuantityMenus();
                    if (nextState && wrap) {
                        wrap.classList.add('is-open');
                    }
                });
            });

            root.querySelectorAll('[data-store-qty-option]').forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    setProductQuantity(button.dataset.storeQtyOption, button.dataset.quantity);
                    closeAllQuantityMenus();
                });
            });

            topupPresetButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    setTopupAmount(button.dataset.storeTopupAmount || '');
                });
            });

            if (topupSubmitBtn) {
                topupSubmitBtn.addEventListener('click', function() {
                    createTopupPurchase(topupInput?.value || '', topupSubmitBtn);
                });
            }
        }

        function bindModalEvents() {
            if (!modal || modal.dataset.bound === '1') {
                return;
            }

            modal.addEventListener('click', function(event) {
                const target = event.target;

                if (target.closest('[data-store-close-modal]')) {
                    closeModal();
                    return;
                }

                if (target.closest('[data-store-copy-pix]')) {
                    copyPixCode();
                    return;
                }

                const checkButton = target.closest('[data-store-check-pix]');
                if (checkButton) {
                    checkPurchaseStatus(checkButton.dataset.storeCheckPix, true);
                    return;
                }

                const cancelButton = target.closest('[data-store-cancel-purchase]');
                if (cancelButton) {
                    cancelPurchase(cancelButton.dataset.storeCancelPurchase);
                }
            });

            document.addEventListener('click', function(event) {
                if (!root?.contains(event.target)) {
                    closeAllQuantityMenus();
                    return;
                }

                if (!event.target.closest('[data-store-qty-wrap]')) {
                    closeAllQuantityMenus();
                }
            });

            keydownHandler = function(event) {
                if (event.key !== 'Escape') {
                    return;
                }

                if (modal.classList.contains('is-open')) {
                    closeModal();
                }
            };

            document.addEventListener('keydown', keydownHandler);
            modal.dataset.bound = '1';
        }

        return {
            init(nextRoot) {
                root = nextRoot?.querySelector('#rrStoreHub') || document.getElementById('rrStoreHub');
                if (!root) {
                    return;
                }

            modal = root.querySelector('#rrStoreModal');
            modalBody = root.querySelector('#rrStoreModalBody');
            topupInput = root.querySelector('#rrStoreTopupCustomAmount');
            topupSubmitBtn = root.querySelector('#rrStoreGenerateTopupPix');
            topupPresetButtons = Array.from(root.querySelectorAll('[data-store-topup-amount]'));
            panelButtons = Array.from(root.querySelectorAll('[data-store-panel-target]'));
            panels = Array.from(root.querySelectorAll('[data-store-panel]'));
            root.querySelectorAll('[data-store-qty-display]').forEach(function(display) {
                setProductQuantity(display.dataset.storeQtyDisplay, 1);
            });

            stopPolling();
            bindEvents();
            bindModalEvents();
            panelButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    activatePanel(button.dataset.storePanelTarget);
                });
            });

            var initialPanel = 'saldo';
            try {
                var storedPanel = sessionStorage.getItem('rr_store_initial_panel');
                if (storedPanel === 'bolao' || storedPanel === 'saldo') {
                    initialPanel = storedPanel;
                }
                sessionStorage.removeItem('rr_store_initial_panel');
            } catch (e) {}

            activatePanel(initialPanel);
            setTopupAmount('20');
        },
            destroy() {
                stopPolling();

                if (keydownHandler) {
                    document.removeEventListener('keydown', keydownHandler);
                    keydownHandler = null;
                }

                document.body.classList.remove('rr-store-modal-open');
            }
        };
    }

    window.RRStore = createStoreController();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            window.RRStore.init(document);
        }, { once: true });
    } else {
        window.RRStore.init(document);
    }
})();
</script>

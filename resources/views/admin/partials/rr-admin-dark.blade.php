<style>
    .rr-admin-dark {
        --rr-accent: #f97316;
        --rr-accent-dark: #ea580c;
        --rr-black: #0b111d;
        --rr-black-soft: #111827;
        --rr-text: #e2e8f0;
    }
    .rr-admin-dark h1,
    .rr-admin-dark h2,
    .rr-admin-dark h3,
    .rr-admin-dark h4,
    .rr-admin-dark h5,
    .rr-admin-dark h6,
    .rr-admin-dark .page-title,
    .rr-admin-dark .page-header,
    .rr-admin-dark .card-title {
        color: #fff;
    }
    .rr-admin-dark .text-muted {
        color: #fff !important;
    }
    .rr-admin-dark .card {
        background: linear-gradient(135deg, var(--rr-black-soft) 0%, var(--rr-black) 100%);
        border: 1px solid rgba(249, 115, 22, 0.2);
        box-shadow: 0 24px 40px rgba(15, 23, 42, 0.25);
        border-radius: 16px;
    }
    .rr-admin-dark .card-header {
        background: linear-gradient(135deg, var(--rr-accent) 0%, var(--rr-accent-dark) 100%);
        color: #fff;
        border-bottom: 1px solid rgba(249, 115, 22, 0.35);
    }
    .rr-admin-dark .card-body,
    .rr-admin-dark .card-footer {
        color: var(--rr-text);
    }
    .rr-admin-dark .table thead {
        background: rgba(15, 23, 42, 0.75);
    }
    .rr-admin-dark .table thead th {
        color: #fff;
        border-bottom: 2px solid rgba(249, 115, 22, 0.3);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.8rem;
    }
    .rr-admin-dark .table tbody tr {
        background: rgba(15, 23, 42, 0.7);
        border: 1px solid rgba(249, 115, 22, 0.12);
    }
    .rr-admin-dark .table tbody tr:hover {
        box-shadow: 0 18px 32px rgba(249, 115, 22, 0.18);
    }
    .rr-admin-dark .table tbody td {
        color: var(--rr-text);
        border-color: rgba(148, 163, 184, 0.15);
    }
    .rr-admin-dark .table-striped > tbody > tr:nth-of-type(odd) {
        background: rgba(15, 23, 42, 0.85);
    }
    .rr-admin-dark .badge--primary,
    .rr-admin-dark .badge.bg-primary {
        background: linear-gradient(135deg, var(--rr-accent) 0%, var(--rr-accent-dark) 100%);
        color: #fff;
    }
    .rr-admin-dark .btn--primary,
    .rr-admin-dark .btn-primary {
        background: linear-gradient(135deg, var(--rr-accent) 0%, var(--rr-accent-dark) 100%);
        border: none;
        color: #fff;
        box-shadow: 0 10px 18px rgba(249, 115, 22, 0.25);
    }
    .rr-admin-dark .btn--primary:hover,
    .rr-admin-dark .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(249, 115, 22, 0.35);
        color: #fff;
    }
    .rr-admin-dark .btn--dark {
        background: rgba(15, 23, 42, 0.85);
        border: 1px solid rgba(249, 115, 22, 0.4);
        color: #fff;
    }
    .rr-admin-dark .btn--dark:hover {
        background: rgba(15, 23, 42, 0.95);
        border-color: rgba(249, 115, 22, 0.6);
    }
    .rr-admin-dark .btn-outline--primary {
        border-color: var(--rr-accent);
        color: var(--rr-accent);
        background: transparent;
    }
    .rr-admin-dark .btn-outline--primary:hover {
        background: var(--rr-accent);
        color: #fff;
    }
    .rr-admin-dark .btn-outline--warning {
        border-color: #fbbf24;
        color: #fbbf24;
        background: transparent;
    }
    .rr-admin-dark .btn-outline--warning:hover {
        background: #fbbf24;
        color: #111827;
    }
    .rr-admin-dark .btn-outline--success {
        border-color: #10b981;
        color: #10b981;
        background: transparent;
    }
    .rr-admin-dark .btn-outline--success:hover {
        background: #10b981;
        color: #fff;
    }
    .rr-admin-dark .btn-outline--danger {
        border-color: #ef4444;
        color: #ef4444;
        background: transparent;
    }
    .rr-admin-dark .btn-outline--danger:hover {
        background: #ef4444;
        color: #fff;
    }
    .rr-admin-dark .form-control,
    .rr-admin-dark .form-select {
        background: rgba(15, 23, 42, 0.7);
        border: 2px solid rgba(148, 163, 184, 0.2);
        color: var(--rr-text);
    }
    .rr-admin-dark .form-control:focus,
    .rr-admin-dark .form-select:focus {
        border-color: var(--rr-accent);
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.2);
    }
    .rr-admin-dark .modal-content {
        background: linear-gradient(135deg, var(--rr-black-soft) 0%, var(--rr-black) 100%);
        border: 1px solid rgba(249, 115, 22, 0.2);
        color: var(--rr-text);
    }
    .rr-admin-dark .modal-header,
    .rr-admin-dark .modal-footer {
        border-color: rgba(249, 115, 22, 0.2);
    }
    .breadcrumb-plugins .btn {
        background: rgba(15, 23, 42, 0.85);
        border: 1px solid rgba(249, 115, 22, 0.4);
        color: #fff;
        border-radius: 10px;
        padding: 0.45rem 1rem;
        font-weight: 600;
    }
    .breadcrumb-plugins .btn:hover {
        background: rgba(15, 23, 42, 0.95);
        border-color: rgba(249, 115, 22, 0.6);
    }
</style>

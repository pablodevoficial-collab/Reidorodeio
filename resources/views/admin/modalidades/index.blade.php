@extends('admin.layouts.app')

@section('panel')
    <style>
        .modalidades-index-wrapper {
            max-width: 100%;
            margin: 0 auto;
        }

        .modalidades-index-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(249, 115, 22, 0.2);
            overflow: hidden;
        }

        .modalidades-index-header {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            padding: 1.75rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }

        .modalidades-index-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -5%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .modalidades-index-header h5 {
            color: #fff;
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            z-index: 1;
        }

        .modalidades-index-header h5 i {
            font-size: 1.6rem;
        }

        .modalidades-add-btn {
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.95);
            color: #f97316;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
        }

        .modalidades-add-btn:hover {
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            color: #ea580c;
        }

        .modalidades-add-btn i {
            font-size: 1.2rem;
        }

        .modalidades-filters-section {
            background: rgba(30, 41, 59, 0.4);
            padding: 1.75rem;
            border-bottom: 1px solid rgba(249, 115, 22, 0.2);
        }

        .modalidades-filter-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
        }

        .modalidades-filter-item {
            flex: 1;
            min-width: 200px;
        }

        .modalidades-filter-item.search {
            flex: 2;
            min-width: 280px;
        }

        .modalidades-filter-label {
            display: block;
            color: #e2e8f0;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .modalidades-filter-label i {
            color: #f97316;
            margin-right: 0.35rem;
        }

        .modalidades-filter-input,
        .modalidades-filter-select {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(15, 23, 42, 0.6);
            border: 2px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .modalidades-filter-input:focus,
        .modalidades-filter-select:focus {
            outline: none;
            border-color: #f97316;
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
        }

        .modalidades-filter-input::placeholder {
            color: #64748b;
        }

        .modalidades-filter-select option {
            background: #1e293b;
            color: #e2e8f0;
        }

        .modalidades-filter-actions {
            display: flex;
            gap: 0.5rem;
        }

        .modalidades-filter-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .modalidades-filter-btn.primary {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .modalidades-filter-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(249, 115, 22, 0.4);
        }

        .modalidades-filter-btn.secondary {
            background: rgba(71, 85, 105, 0.8);
            color: #e2e8f0;
        }

        .modalidades-filter-btn.secondary:hover {
            background: rgba(71, 85, 105, 1);
        }

        .modalidades-table-wrapper {
            overflow-x: auto;
        }

        .modalidades-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .modalidades-table thead {
            background: rgba(30, 41, 59, 0.6);
        }

        .modalidades-table thead th {
            padding: 1rem 1.25rem;
            color: #f97316;
            font-weight: 700;
            text-align: left;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid rgba(249, 115, 22, 0.3);
        }

        .modalidades-table tbody tr {
            background: rgba(30, 41, 59, 0.3);
            transition: all 0.3s ease;
        }

        .modalidades-table tbody tr:hover {
            background: rgba(30, 41, 59, 0.5);
            box-shadow: inset 0 0 0 1px rgba(249, 115, 22, 0.3);
        }

        .modalidades-table tbody td {
            padding: 1.25rem 1.25rem;
            color: #e2e8f0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            font-size: 0.95rem;
        }

        .modalidades-table tbody tr:last-child td {
            border-bottom: none;
        }

        .modalidades-name {
            font-weight: 700;
            color: #fff;
            font-size: 1rem;
        }

        .modalidades-sub {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.85rem;
            color: #94a3b8;
            margin-top: 0.35rem;
        }

        .competitors-count-badge {
            color: #fbbf24;
            font-weight: 600;
        }

        .modalidades-sub-label {
            color: #94a3b8;
            font-weight: 500;
        }

        .modalidades-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.02em;
        }

        .modalidades-badge.ativo {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
        }

        .modalidades-badge.inativo {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #fff;
        }

        .modalidades-badge.warning {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #1e293b;
        }

        .modalidades-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .modalidades-action-btn {
            padding: 0.5rem 1rem;
            border: 2px solid;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            text-decoration: none;
            background: transparent;
        }

        .modalidades-action-btn.edit {
            border-color: #f97316;
            color: #f97316;
        }

        .modalidades-action-btn.edit:hover {
            background: #f97316;
            color: #fff;
            transform: translateY(-2px);
        }

        .modalidades-action-btn.group {
            border-color: #fbbf24;
            color: #fbbf24;
        }

        .modalidades-action-btn.group:hover {
            background: #fbbf24;
            color: #1e293b;
            transform: translateY(-2px);
        }

        .modalidades-action-btn.users {
            border-color: #10b981;
            color: #10b981;
        }

        .modalidades-action-btn.users:hover {
            background: #10b981;
            color: #fff;
            transform: translateY(-2px);
        }

        .modalidades-action-btn.delete {
            border-color: #ef4444;
            color: #ef4444;
        }

        .modalidades-action-btn.delete:hover {
            background: #ef4444;
            color: #fff;
            transform: translateY(-2px);
        }

        .modalidades-action-btn.pause {
            border-color: #f59e0b;
            color: #f59e0b;
        }

        .modalidades-action-btn.pause:hover {
            background: #f59e0b;
            color: #fff;
            transform: translateY(-2px);
        }

        .modalidades-action-btn.pause.paused {
            background: #f59e0b;
            color: #fff;
            border-color: #f59e0b;
        }

        .modalidades-empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: #94a3b8;
        }

        .modalidades-empty-state i {
            font-size: 4rem;
            color: rgba(249, 115, 22, 0.3);
            margin-bottom: 1rem;
        }

        .modalidades-empty-state p {
            font-size: 1.1rem;
            margin: 0;
        }

        .modalidades-pagination {
            padding: 2rem;
            background: rgba(15, 23, 42, 0.3);
            border-top: 1px solid rgba(249, 115, 22, 0.2);
        }

        /* Botões Laranja */
        .rr-btn-orange {
            background: linear-gradient(135deg, #f97316, #ea580c);
            border: none;
            color: #fff !important;
            font-weight: 600;
            border-radius: 10px;
            padding: 0.5rem 1.25rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(249, 115, 22, 0.3);
        }
        .rr-btn-orange:hover {
            background: linear-gradient(135deg, #fb923c, #f97316);
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
            transform: translateY(-1px);
        }
        .rr-btn-orange:disabled {
            background: linear-gradient(135deg, #78716c, #57534e);
            opacity: 0.7;
            cursor: not-allowed;
            box-shadow: none;
        }
        .rr-btn-orange-outline {
            background: transparent;
            border: 2px solid #f97316;
            color: #f97316 !important;
            font-weight: 600;
            border-radius: 10px;
            padding: 0.45rem 1.2rem;
            transition: all 0.2s ease;
        }
        .rr-btn-orange-outline:hover {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
        }
        .rr-btn-orange.btn-sm,
        .rr-btn-orange-outline.btn-sm {
            padding: 0.35rem 0.9rem;
            font-size: 0.875rem;
        }

        /* Modal Competidores */
        .rr-modalidades-competitors .modal-dialog {
            width: min(1520px, calc(100vw - 2.5rem));
            max-width: min(1520px, calc(100vw - 2.5rem));
            margin: 1.25rem auto;
        }
        .rr-modalidades-competitors .modal-content {
            border-radius: 18px;
            border: 1px solid rgba(249, 115, 22, 0.2);
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.98) 0%, rgba(2, 6, 23, 0.98) 100%);
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.6);
        }
        .rr-modalidades-competitors .modal-header {
            padding: 1.35rem 1.5rem;
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.2), rgba(15, 23, 42, 0.95));
            border-bottom: 1px solid rgba(249, 115, 22, 0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .rr-modalidades-competitors .modal-body {
            padding: 1.25rem 1.5rem 1.5rem;
        }
        .rr-modalidades-competitors__toolbar {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) minmax(220px, 1fr) minmax(240px, 1.15fr);
            gap: 0.9rem;
            margin-bottom: 1rem;
            align-items: end;
        }
        .rr-modalidades-competitors__toolbar-card {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.86), rgba(15, 23, 42, 0.72));
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 14px;
            padding: 0.95rem 1rem;
        }
        .rr-modalidades-competitors__workspace {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 1rem;
            align-items: stretch;
        }
        .rr-modalidades-competitors__column {
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .rr-modalidades-competitors__column-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.9rem;
            padding: 0.95rem 1rem;
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.98), rgba(15, 23, 42, 0.95));
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-bottom: none;
            border-radius: 16px 16px 0 0;
        }
        .rr-modalidades-competitors__column-subtitle {
            color: rgba(148, 163, 184, 0.95);
            font-size: 0.86rem;
            margin-top: 0.25rem;
        }
        .rr-modalidades-competitors__column-tools {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
            margin-bottom: 0.75rem;
        }
        .rr-modalidades-competitors__column .rr-modalidades-competitors__search-bar {
            border-radius: 0;
            margin: 0;
        }
        .rr-modalidades-competitors__column .rr-modalidades-competitors__panel {
            flex: 1 1 auto;
            border-top: none;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
            min-height: 470px;
        }
        .rr-modalidades-competitors__summary-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: rgba(249, 115, 22, 0.12);
            border: 1px solid rgba(249, 115, 22, 0.24);
            border-radius: 999px;
            color: #fed7aa;
            font-size: 0.82rem;
            font-weight: 700;
            padding: 0.35rem 0.75rem;
        }
        .rr-modalidades-competitors .modal-title {
            color: #fff;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 1.15rem;
        }
        .rr-modalidades-competitors__subtitle {
            color: rgba(226, 232, 240, 0.7);
            font-size: 0.95rem;
        }
        .rr-modalidades-competitors__panel {
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 14px;
            background: rgba(15, 23, 42, 0.6);
            padding: 0.85rem;
            min-height: 440px;
            max-height: 62vh;
            overflow: auto;
        }
        .rr-modalidades-competitors__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }
        .rr-modalidades-competitors__title {
            font-weight: 700;
            color: #e2e8f0;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
        }
        .rr-modalidades-competitors__badge {
            background: rgba(249, 115, 22, 0.2);
            color: #f97316;
            font-weight: 700;
            border-radius: 999px;
            padding: 0.3rem 0.8rem;
            font-size: 0.85rem;
        }
        .rr-modalidades-competitors__row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 0.85rem;
            border-radius: 10px;
            background: rgba(15, 23, 42, 0.55);
            border: 1px solid rgba(148, 163, 184, 0.12);
        }
        .rr-modalidades-competitors__row + .rr-modalidades-competitors__row {
            margin-top: 0.5rem;
        }
        .rr-modalidades-competitors__avatar {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #6f42c1, #0d6efd);
            flex: 0 0 42px;
        }
        .rr-modalidades-competitors__meta {
            font-size: 0.88rem;
            color: rgba(148, 163, 184, 0.9);
        }
        .rr-modalidades-competitors__actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .rr-modalidades-competitors__summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            background: rgba(15, 23, 42, 0.55);
        }
        .rr-modalidades-competitors__summary strong {
            color: #fbbf24;
        }
        .rr-modalidades-competitors .form-label {
            color: #dbe5f2;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 0.45rem;
        }
        .rr-modalidades-competitors .form-control,
        .rr-modalidades-competitors .input-group-text,
        .rr-modalidades-competitors .form-select {
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.15);
            color: #e5e7eb;
            min-height: 44px;
        }
        .rr-modalidades-competitors .form-control::placeholder {
            color: rgba(229,231,235,0.65);
        }
        .rr-modalidades-competitors .btn-outline--primary,
        .rr-modalidades-competitors .btn-outline--danger,
        .rr-modalidades-competitors .btn-outline--warning {
            border-radius: 10px;
        }
        /* Barra de pesquisa estilizada */
        .rr-modalidades-competitors__search-bar {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8), rgba(30, 41, 59, 0.9));
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-top: none;
            padding: 0.75rem;
            border-radius: 0;
        }
        .rr-modalidades-competitors__search-bar .input-group {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            overflow: hidden;
        }
        .rr-modalidades-competitors__search-bar .input-group-text {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none;
            color: #fff;
            padding: 0.5rem 0.85rem;
        }
        .rr-modalidades-competitors__search-bar .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: none;
            color: #f1f5f9;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
        }
        .rr-modalidades-competitors__search-bar .form-control:focus {
            background: rgba(255, 255, 255, 0.12);
            box-shadow: none;
            color: #fff;
        }
        .rr-modalidades-competitors__search-bar .form-control::placeholder {
            color: rgba(203, 213, 225, 0.7);
        }
        .rr-modalidades-competitors .row.g-3 {
            --bs-gutter-x: 1rem;
            --bs-gutter-y: 1rem;
        }
        .rr-modalidades-competitors__info-box {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(249, 115, 22, 0.3);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            min-height: 44px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .rr-modalidades-competitors__info-box .badge {
            font-size: 0.8rem;
        }
        .rr-modalidades-competitors__info-box .info-item {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: #e2e8f0;
            font-size: 0.85rem;
        }
        .rr-modalidades-competitors__info-box .info-item i {
            color: #f97316;
        }

        .rr-modalidades-competitors #participantesMainContent .col-lg-6 {
            display: flex;
            flex-direction: column;
        }

        .rr-modalidades-competitors #participantesMainContent .col-lg-6 > .rr-modalidades-competitors__panel {
            flex: 1 1 auto;
        }

        @media (max-width: 1399px) {
            .rr-modalidades-competitors .modal-dialog {
                width: min(1320px, calc(100vw - 2rem));
                max-width: min(1320px, calc(100vw - 2rem));
            }

            .rr-modalidades-competitors__toolbar {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .rr-modalidades-competitors__toolbar-card:last-child {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 1199px) {
            .rr-modalidades-competitors .modal-dialog {
                width: calc(100vw - 1.5rem);
                max-width: calc(100vw - 1.5rem);
            }

            .rr-modalidades-competitors .modal-body {
                padding: 1rem 1rem 1.25rem;
            }

            .rr-modalidades-competitors__panel {
                min-height: 360px;
                max-height: 48vh;
            }

            .rr-modalidades-competitors__toolbar,
            .rr-modalidades-competitors__workspace {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .modalidades-index-header {
                padding: 1.25rem 1.5rem;
            }

            .modalidades-index-header h5 {
                font-size: 1.35rem;
            }

            .modalidades-filters-section {
                padding: 1.25rem;
            }

            .modalidades-filter-item {
                min-width: 100%;
            }

            .modalidades-table thead th,
            .modalidades-table tbody td {
                padding: 0.75rem;
                font-size: 0.85rem;
            }

            .modalidades-actions {
                flex-direction: column;
            }

            .modalidades-action-btn {
                width: 100%;
                justify-content: center;
            }

            .rr-modalidades-competitors .modal-dialog {
                width: calc(100vw - 1rem);
                max-width: calc(100vw - 1rem);
                margin: 0.5rem auto;
            }

            .rr-modalidades-competitors .modal-header,
            .rr-modalidades-competitors .modal-body {
                padding-left: 0.85rem;
                padding-right: 0.85rem;
            }

            .rr-modalidades-competitors__panel {
                min-height: 280px;
                max-height: 36vh;
            }
        }

        /* Modal bootstrap fix (painel pode não carregar CSS do Bootstrap) */
        .modal {
            display: none;
            position: fixed;
            z-index: 1055;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            outline: 0;
        }
        .modal.show {
            display: block;
        }
        .modal.fade .modal-dialog {
            transform: translate(0, -15px);
            transition: transform .2s ease-out;
        }
        .modal.show .modal-dialog {
            transform: none;
        }
        .modal-dialog {
            position: relative;
            width: auto;
            margin: 1rem;
            pointer-events: none;
            max-width: 1140px;
        }
        @media (min-width: 992px) {
            .modal-dialog {
                margin: 1.75rem auto;
            }
        }
        .modal-dialog-scrollable {
            height: calc(100% - 3.5rem);
        }
        .modal-dialog-scrollable .modal-content {
            max-height: 100%;
            overflow: hidden;
        }
        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
        }
        .modal-content {
            pointer-events: auto;
            background-color: #0f172a;
            color: #e5e7eb;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.55);
            background-clip: padding-box;
            outline: 0;
        }
        .modal-header, .modal-footer {
            border-color: rgba(255,255,255,0.08);
        }
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 1050;
            opacity: 0;
            transition: opacity .15s linear;
        }
        .modal-backdrop.show {
            opacity: 1;
        }
        body.modal-open {
            overflow: hidden;
        }
        .btn-close {
            width: 1rem;
            height: 1rem;
            padding: .5rem;
            border: 0;
            border-radius: .5rem;
            background: transparent;
            opacity: .85;
            position: relative;
        }
        .btn-close::before,
        .btn-close::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 14px;
            height: 2px;
            background: rgba(255,255,255,0.85);
            transform-origin: center;
        }
        .btn-close::before { transform: translate(-50%, -50%) rotate(45deg); }
        .btn-close::after { transform: translate(-50%, -50%) rotate(-45deg); }
        .btn-close:hover { opacity: 1; }
    </style>

    <div class="modalidades-index-wrapper">
        <div class="modalidades-index-card">
            <div class="modalidades-index-header">
                <h5><i class="las la-trophy"></i> @lang('Modalidades')</h5>
                <a href="{{ route('admin.modalidades.create') }}" class="modalidades-add-btn">
                    <i class="las la-plus-circle"></i> @lang('Nova Modalidade')
                </a>
            </div>

            <div class="modalidades-filters-section">
                <form method="get">
                    <div class="modalidades-filter-group">
                        <div class="modalidades-filter-item search">
                            <label class="modalidades-filter-label">
                                <i class="las la-search"></i> @lang('Buscar')
                            </label>
                            <input type="search" name="q" value="{{ request('q') }}" class="modalidades-filter-input" placeholder="@lang('Digite o nome da modalidade...')">
                        </div>

                        <div class="modalidades-filter-item">
                            <label class="modalidades-filter-label">
                                <i class="las la-calendar"></i> @lang('Rodeio')
                            </label>
                            <select name="rodeio_id" class="modalidades-filter-select">
                                <option value="">@lang('Todos os rodeios')</option>
                                @foreach(($rodeios ?? []) as $rodeio)
                                    <option value="{{ $rodeio->id }}" @selected(request('rodeio_id') == $rodeio->id)>{{ $rodeio->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="modalidades-filter-item">
                            <label class="modalidades-filter-label">
                                <i class="las la-power-off"></i> @lang('Status')
                            </label>
                            <select name="status" class="modalidades-filter-select">
                                <option value="">@lang('Todos os status')</option>
                                <option value="ativo" @selected(request('status') === 'ativo')>@lang('Ativas')</option>
                                <option value="inativo" @selected(request('status') === 'inativo')>@lang('Inativas')</option>
                                <option value="finalizado" @selected(request('status') === 'finalizado')>@lang('Finalizadas')</option>
                            </select>
                        </div>

                        <div class="modalidades-filter-item">
                            <div class="modalidades-filter-actions">
                                <button type="submit" class="modalidades-filter-btn primary">
                                    <i class="las la-filter"></i> @lang('Filtrar')
                                </button>
                                <a href="{{ route('admin.modalidades.index') }}" class="modalidades-filter-btn secondary">
                                    <i class="las la-redo-alt"></i> @lang('Limpar')
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modalidades-table-wrapper">
                <table class="modalidades-table">
                    <thead>
                        <tr>
                            <th>@lang('Modalidade')</th>
                            <th>@lang('Tipo')</th>
                            <th>@lang('Rodeio')</th>
                            <th>@lang('Divisões')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Ações')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($modalidades as $modalidade)
                            @php
                                $tipo = $modalidade->tipo_participacao ?? 'individual';
                                $size = $modalidade->tamanho_equipe ?? 1;
                                $labels = [
                                    'individual' => 'Individual',
                                    'dupla' => 'Dupla',
                                    'trio' => 'Trio',
                                    'quarteto' => 'Quarteto',
                                    'quinteto' => 'Quinteto',
                                    'sexteto' => 'Sexteto',
                                    'septeto' => 'Septeto',
                                    'octeto' => 'Octeto',
                                    'noneto' => 'Noneto',
                                    'deceto' => 'Deceto',
                                ];
                                $label = $labels[$tipo] ?? 'Individual';
                                $statusClass = in_array($modalidade->status, ['finalizado']) ? 'warning' : $modalidade->status;
                            @endphp
                            <tr>
                                <td>
                                    <span class="modalidades-name">{{ $modalidade->nome }}</span>
                                    <div class="modalidades-sub">
                                        <i class="las la-users"></i>
                                        <span class="competitors-count-badge" data-modalidade-id="{{ $modalidade->id }}">{{ $modalidade->competitors_count ?? 0 }}</span>
                                        <span class="modalidades-sub-label">@lang('competidores')</span>
                                    </div>
                                </td>
                                <td>
                                    <span style="color: #94a3b8;">{{ $label }} ({{ $size }})</span>
                                </td>
                                <td>
                                    <span style="color: #94a3b8;"><i class="las la-calendar-alt"></i> {{ $modalidade->rodeio->name ?? '-' }}</span>
                                </td>
                                <td>
                                    @if($modalidade->tem_divisoes && !empty($modalidade->divisoes_nomes))
                                        <div style="display: flex; flex-wrap: wrap; gap: 0.35rem;">
                                            @foreach($modalidade->divisoes_nomes as $div)
                                                <span class="modalidades-badge warning">{{ $div }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span style="color: #64748b;">@lang('Sem divisões')</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="modalidades-badge {{ $statusClass }}">
                                        @if($modalidade->status == 'ativo') ✓ @elseif($modalidade->status == 'inativo') ✕ @else ★ @endif {{ ucfirst($modalidade->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="modalidades-actions">
                                        <a href="{{ route('admin.modalidades.edit', $modalidade->id) }}" class="modalidades-action-btn edit">
                                            <i class="las la-edit"></i> Editar
                                        </a>
                                        <button type="button" class="modalidades-action-btn users js-open-participantes" 
                                            data-modalidade-id="{{ $modalidade->id }}" 
                                            data-modalidade-nome="{{ $modalidade->nome }}"
                                            data-tamanho-equipe="{{ $modalidade->tamanho_equipe ?? 1 }}"
                                            data-tem-divisoes="{{ $modalidade->tem_divisoes ? '1' : '0' }}"
                                            data-divisoes='@json($modalidade->divisoes_nomes ?? [])'>
                                            <i class="las la-users-cog"></i> Participantes
                                        </button>
                                        <button type="button" 
                                            class="modalidades-action-btn pause js-toggle-pause-x1 {{ $modalidade->pausar_x1 ? 'paused' : '' }}" 
                                            data-modalidade-id="{{ $modalidade->id }}"
                                            data-pause-state="{{ $modalidade->pausar_x1 ? 'true' : 'false' }}">
                                            <i class="las {{ $modalidade->pausar_x1 ? 'la-play' : 'la-pause' }}"></i> 
                                            {{ $modalidade->pausar_x1 ? 'Ativar X1' : 'Pausar X1' }}
                                        </button>
                                        <form method="POST" action="{{ route('admin.modalidades.destroy', $modalidade->id) }}" onsubmit="return confirm('Tem certeza que deseja excluir esta modalidade?');" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="modalidades-action-btn delete">
                                                <i class="las la-trash"></i> Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="modalidades-empty-state">
                                        <i class="las la-inbox"></i>
                                        <p>@lang('Nenhuma modalidade encontrada')</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($modalidades->hasPages())
                <div class="modalidades-pagination">
                    {{ paginateLinks($modalidades) }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Unificado: Participantes (Competidores/Grupos) -->
    <div class="modal fade rr-modalidades-competitors" id="competitorsPopoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="competitorsPopoutTitle"><i class="la la-users-cog"></i> Participantes</h5>
                        <small class="rr-modalidades-competitors__subtitle" id="competitorsPopoutSubtitle">Vincule competidores/grupos à modalidade</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modalidadeIdField" value="">

                    <!-- Seleção de Rodeio e Modalidade -->
                    <div class="rr-modalidades-competitors__toolbar" id="participantesSelectionRow">
                        <div class="rr-modalidades-competitors__toolbar-card">
                            <label class="form-label"><i class="la la-calendar-alt"></i> Rodeio</label>
                            <select id="participantesRodeioSelect" class="form-control">
                                <option value="">Selecione o rodeio</option>
                                @foreach(\App\Models\Rodeio::orderBy('name')->get() as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="rr-modalidades-competitors__toolbar-card">
                            <label class="form-label"><i class="la la-medal"></i> Modalidade</label>
                            <select id="participantesModalidadeSelect" class="form-control" disabled>
                                <option value="">Selecione primeiro o rodeio</option>
                            </select>
                        </div>
                        <div class="rr-modalidades-competitors__toolbar-card">
                            <label class="form-label"><i class="la la-info-circle"></i> Informações</label>
                            <div class="rr-modalidades-competitors__info-box" id="modalidadeInfoBox">
                                <span class="text-muted">Selecione uma modalidade</span>
                            </div>
                        </div>
                    </div>

                    <div id="participantesMainContent" style="display: none;">
                        <div class="rr-modalidades-competitors__toolbar mb-3">
                            <div class="rr-modalidades-competitors__toolbar-card">
                                <label class="form-label"><i class="la la-sitemap"></i> Tipo atual</label>
                                <div class="rr-modalidades-competitors__summary-chip">
                                    <i class="la la-users"></i>
                                    <span id="modalidadeTipoLabel">—</span>
                                </div>
                            </div>
                            <div class="rr-modalidades-competitors__toolbar-card">
                                <label class="form-label"><i class="la la-bolt"></i> Ações rápidas</label>
                                <div class="rr-modalidades-competitors__column-tools">
                                    <button type="button" class="btn rr-btn-orange btn-sm" id="btnSelectAllFiltered">
                                        <i class="la la-plus-circle"></i> Adicionar todos
                                    </button>
                                    <button type="button" class="btn rr-btn-orange-outline btn-sm" id="btnClearSelection">
                                        <i class="la la-trash"></i> Limpar
                                    </button>
                                </div>
                            </div>
                            <div class="rr-modalidades-competitors__toolbar-card">
                                <label class="form-label"><i class="la la-lightbulb"></i> Fluxo</label>
                                <div class="text-muted" style="font-size:0.9rem; line-height:1.5;">
                                    À esquerda ficam os competidores disponíveis. À direita ficam os grupos ou competidores já vinculados à modalidade.
                                </div>
                            </div>
                        </div>

                        <div class="rr-modalidades-competitors__workspace">
                            <div class="rr-modalidades-competitors__column">
                                <div class="rr-modalidades-competitors__column-header">
                                    <div>
                                        <div class="rr-modalidades-competitors__title"><i class="la la-user-plus"></i> Competidores disponíveis</div>
                                        <div class="rr-modalidades-competitors__column-subtitle">Filtre e selecione os participantes que ainda podem ser vinculados.</div>
                                    </div>
                                    <span class="rr-modalidades-competitors__badge" id="availableCount">0</span>
                                </div>
                                <div class="rr-modalidades-competitors__search-bar">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="la la-search"></i></span>
                                        <input id="competitorsSearchInput" type="text" class="form-control" placeholder="Filtrar por nome...">
                                    </div>
                                </div>
                                <div class="rr-modalidades-competitors__panel" id="availableList">
                                    <div class="text-muted text-center py-4">Selecione uma modalidade</div>
                                </div>
                            </div>

                            <div class="rr-modalidades-competitors__column">
                                <div class="rr-modalidades-competitors__column-header">
                                    <div>
                                        <div class="rr-modalidades-competitors__title"><i class="la la-users"></i> <span id="selectedPanelTitle">Vinculados</span></div>
                                        <div class="rr-modalidades-competitors__column-subtitle">Revise os participantes escolhidos e ajuste divisões quando necessário.</div>
                                    </div>
                                    <span class="rr-modalidades-competitors__badge" id="selectedCount">0</span>
                                </div>
                                <div class="rr-modalidades-competitors__panel" id="selectedList">
                                    <div class="text-muted text-center py-4">Selecione uma modalidade</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn rr-btn-orange-outline" data-bs-dismiss="modal">
                        <i class="la la-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn rr-btn-orange" id="btnSaveCompetitors" disabled>
                        <i class="la la-save"></i> Salvar Participantes
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
(function() {
    // Cache de modalidades por rodeio
    const modalidadesCache = {};
    
    function safeNotify(type, message) {
        try {
            if (typeof notify === 'function') {
                notify(type, message);
                return;
            }
        } catch (e) {}
        if (type === 'error' || type === 'danger') alert(message || 'Falha');
        else console.log(type, message);
    }

    function initials(name) {
        const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
        const a = (parts[0] || '').charAt(0);
        const b = (parts[1] || '').charAt(0);
        return (a + b).toUpperCase() || '?';
    }

    function matchesFilter(text, filter) {
        if (!filter) return true;
        return String(text || '').toLowerCase().includes(filter);
    }

    const modalEl = document.getElementById('competitorsPopoutModal');
    const bsModal = modalEl ? new bootstrap.Modal(modalEl) : null;
    
    // Elementos do modal
    const rodeioSelect = document.getElementById('participantesRodeioSelect');
    const modalidadeSelect = document.getElementById('participantesModalidadeSelect');
    const infoBox = document.getElementById('modalidadeInfoBox');
    const mainContent = document.getElementById('participantesMainContent');
    const saveBtn = document.getElementById('btnSaveCompetitors');
    const createGroupBtn = document.getElementById('btnCreateGroupFromModal');

    const state = {
        byId: new Map(),              // Competidores individuais (id => {id, nome, ...})
        selected: new Set(),          // IDs de competidores/grupos vinculados
        groupMode: false,             // Se modalidade é em grupo (dupla, trio, etc.)
        divisaoById: new Map(),
        lastFilter: '',
        saving: false,
        modalidadeId: null,
        modalidadeNome: '',
        modalidadeTemDivisoes: false,
        modalidadeDivisoes: [],
        modalidadeTeamSize: 1,
        modalidadeTipo: 'individual',
        modalidadeStatus: 'ativo',
        hasScores: false,
        hasClassificatoriaGroups: false,
        isClassificatoria: false,
        // Novo: Para formar grupos no modal
        pendingGroup: new Set(),      // IDs de competidores selecionados para formar grupo
        groups: new Map(),            // Grupos já criados (id => {id, nome, members, divisao})
        competitorInGroup: new Map(), // Mapa: competitor_id => group_id (para saber se já está em grupo)
        tempGroups: new Map(),        // Grupos temporários (id negativo => {id, members, divisao})
        nextTempGroupId: -1,          // Próximo ID temporário
    };

    // Carregar modalidades de um rodeio
    async function loadModalidades(rodeioId) {
        if (!rodeioId) {
            modalidadeSelect.innerHTML = '<option value="">Selecione primeiro o rodeio</option>';
            modalidadeSelect.disabled = true;
            return;
        }
        
        if (modalidadesCache[rodeioId]) {
            populateModalidadeSelect(modalidadesCache[rodeioId]);
            return;
        }
        
        modalidadeSelect.innerHTML = '<option value="">Carregando...</option>';
        modalidadeSelect.disabled = true;
        
        try {
            const r = await fetch(`{{ url('api/realtime/modalidades') }}?rodeio_id=${rodeioId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await r.json();
            const list = data.data || [];
            modalidadesCache[rodeioId] = list;
            populateModalidadeSelect(list);
        } catch (e) {
            console.error(e);
            modalidadeSelect.innerHTML = '<option value="">Erro ao carregar</option>';
        }
    }
    
    function populateModalidadeSelect(list) {
        modalidadeSelect.innerHTML = '<option value="">Selecione a modalidade</option>';
        list.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = m.nome;
            opt.dataset.temDivisoes = m.tem_divisoes ? '1' : '0';
            opt.dataset.divisoes = JSON.stringify(m.divisoes || []);
            opt.dataset.tamanhoEquipe = m.tamanho_equipe || 1;
            opt.dataset.tipo = m.tipo_participacao || 'individual';
            modalidadeSelect.appendChild(opt);
        });
        modalidadeSelect.disabled = false;
    }
    
    function updateInfoBox() {
        if (!state.modalidadeId) {
            infoBox.innerHTML = '<span class="text-muted">Selecione uma modalidade</span>';
            return;
        }
        
        const tipoLabels = {
            individual: 'Individual',
            dupla: 'Dupla',
            trio: 'Trio',
            quarteto: 'Quarteto',
            quinteto: 'Quinteto',
        };
        const tipoLabel = tipoLabels[state.modalidadeTipo] || 'Individual';
        
        let html = `
            <span class="info-item"><i class="la la-users"></i> ${tipoLabel} (${state.modalidadeTeamSize})</span>
        `;
        
        if (state.modalidadeTemDivisoes && state.modalidadeDivisoes.length > 0) {
            html += `<span class="info-item"><i class="la la-layer-group"></i> ${state.modalidadeDivisoes.length} divisões</span>`;
        }
        
        if (state.groupMode) {
            html += '<span class="badge bg-info">Modo Grupos</span>';
        }
        
        // Status da modalidade
        const statusLabels = {
            'ativo': { label: 'Ativa', class: 'success' },
            'classificatoria': { label: 'Classificatória', class: 'warning' },
            'em_apuracao': { label: 'Em Apuração', class: 'info' },
            'inicio_finais': { label: 'Início das Finais', class: 'primary' },
            'divisao_finalizada': { label: 'Divisão Finalizada', class: 'secondary' },
            'finalizado': { label: 'Finalizado', class: 'dark' },
        };
        const statusInfo = statusLabels[state.modalidadeStatus] || { label: state.modalidadeStatus, class: 'secondary' };
        html += `<span class="badge bg-${statusInfo.class}">${statusInfo.label}</span>`;
        
        infoBox.innerHTML = html;
        
        // Mostrar alerta se necessário
        showStatusAlert();
    }
    
    function showStatusAlert() {
        // Remover alerta anterior se existir
        const existingAlert = document.getElementById('modalidadeStatusAlert');
        if (existingAlert) existingAlert.remove();
        
        let alertHtml = '';
        let alertClass = '';
        
        // Status que permitem cadastrar sem divisão (preparação)
        const statusSemDivisao = ['programado', 'classificatoria', 'ativo'];
        const podeVincularSemDivisao = statusSemDivisao.includes(state.modalidadeStatus);
        
        // Se tem pontuações e grupos sem divisão
        if (state.hasScores && state.hasClassificatoriaGroups && state.modalidadeTemDivisoes) {
            alertClass = 'warning';
            alertHtml = `
                <i class="la la-exclamation-triangle"></i>
                <strong>Atenção:</strong> A classificatória já foi pontuada no Live Transmission. 
                Agora você precisa <strong>atribuir divisões</strong> aos grupos/competidores classificados.
                <br><small>Selecione cada grupo e atribua a divisão correspondente (Ex: Semifinal A, Final, etc).</small>
            `;
        }
        // Se status é em_apuracao e tem grupos sem divisão
        else if (state.modalidadeStatus === 'em_apuracao' && state.hasClassificatoriaGroups && state.modalidadeTemDivisoes) {
            alertClass = 'info';
            alertHtml = `
                <i class="la la-info-circle"></i>
                <strong>Em Apuração:</strong> Atribua as divisões aos participantes classificados antes de prosseguir para as finais.
            `;
        }
        // Se está em status de preparação (programado, classificatória, ativo) e tem divisões - pode vincular sem divisão
        else if (podeVincularSemDivisao && state.modalidadeTemDivisoes) {
            alertClass = 'info';
            const statusLabel = state.modalidadeStatus === 'programado' ? 'Programado' : 
                               state.modalidadeStatus === 'classificatoria' ? 'Classificatória' : 'Ativo';
            alertHtml = `
                <i class="la la-info-circle"></i>
                <strong>${statusLabel}:</strong> Você pode vincular competidores/grupos sem atribuir divisão agora. 
                As divisões serão atribuídas após os resultados da classificatória.
            `;
            state.isClassificatoria = true;
        }
        // Modo com divisões em fase de finais - precisa atribuir divisão
        else if (state.modalidadeTemDivisoes && !state.isClassificatoria) {
            alertClass = 'secondary';
            alertHtml = `
                <i class="la la-layer-group"></i>
                <strong>Divisões:</strong> Esta modalidade possui divisões. Selecione a divisão ao vincular cada participante.
            `;
        }
        
        if (alertHtml) {
            const alertDiv = document.createElement('div');
            alertDiv.id = 'modalidadeStatusAlert';
            alertDiv.className = `alert alert-${alertClass} mt-3 mb-0`;
            alertDiv.style.cssText = 'border-radius: 8px; display: flex; align-items: flex-start; gap: 10px;';
            alertDiv.innerHTML = `<div>${alertHtml}</div>`;
            
            const mainContent = document.getElementById('participantesMainContent');
            if (mainContent) {
                mainContent.insertBefore(alertDiv, mainContent.firstChild);
            }
        }
    }

    function render() {
        const filter = state.lastFilter;
        const teamSize = state.modalidadeTeamSize;
        const availableEl = document.getElementById('availableList');
        const selectedEl = document.getElementById('selectedList');
        const addGroupBtn = document.getElementById('btnSelectAllFiltered');
        
        if (state.groupMode) {
            // === MODO GRUPO (dupla, trio, etc.) ===
            // Lista de competidores disponíveis (não estão em nenhum grupo vinculado)
            const competitorsInAttachedGroups = new Set();
            state.groups.forEach((g, gid) => {
                if (state.selected.has('group_' + gid)) {
                    (g.members || []).forEach(m => competitorsInAttachedGroups.add(m.id));
                }
            });
            
            const allCompetitors = Array.from(state.byId.values()).filter(c => c.isCompetitor);
            const availableCompetitors = allCompetitors.filter(c => 
                !competitorsInAttachedGroups.has(c.id) && 
                matchesFilter(c.searchText || c.nome, filter)
            );
            
            // Grupos vinculados
            const attachedGroups = Array.from(state.groups.values()).filter(g => 
                state.selected.has('group_' + g.id) && 
                matchesFilter(g.searchText || g.nome, filter)
            );
            
            document.getElementById('availableCount').textContent = String(availableCompetitors.length);
            document.getElementById('selectedCount').textContent = String(attachedGroups.length);
            
            // Renderizar competidores disponíveis (para selecionar e formar grupo)
            if (availableCompetitors.length === 0) {
                availableEl.innerHTML = '<div class="text-muted text-center py-4">Nenhum competidor disponível</div>';
            } else {
                availableEl.innerHTML = availableCompetitors.map(c => {
                    const isPending = state.pendingGroup.has(c.id);
                    const inGroup = state.competitorInGroup.has(c.id);
                    const groupId = inGroup ? state.competitorInGroup.get(c.id) : null;
                    const groupInfo = groupId ? state.groups.get(groupId) : null;
                    const bgStyle = isPending ? 'background:linear-gradient(135deg,#ffc107,#fd7e14);' : '';
                    const checkIcon = isPending ? '<i class="la la-check text-white"></i>' : initials(c.nome);
                    const groupBadge = groupInfo ? `<span class="badge bg-secondary ms-1" title="Grupo: ${groupInfo.nome}">${groupInfo.nome}</span>` : '';
                    
                    return `
                        <div class="rr-modalidades-competitors__row ${isPending ? 'border-warning' : ''}" data-toggle-pending="${c.id}" style="cursor:pointer;">
                            <div class="rr-modalidades-competitors__avatar" style="${bgStyle}">
                                ${checkIcon}
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">${c.nome} ${groupBadge}</div>
                                <div class="rr-modalidades-competitors__meta">ID #${c.id}</div>
                            </div>
                            <span class="badge ${isPending ? 'bg-warning text-dark' : 'bg-light text-dark'}">${isPending ? 'Selecionado' : 'Clique para selecionar'}</span>
                        </div>
                    `;
                }).join('');
            }
            
            // Renderizar grupos vinculados
            if (attachedGroups.length === 0) {
                selectedEl.innerHTML = '<div class="text-muted text-center py-4">Nenhum grupo vinculado</div>';
            } else {
                selectedEl.innerHTML = attachedGroups.map(g => {
                    const members = Array.isArray(g.members) ? g.members.map(m => m.nome).join(' + ') : '';
                    const divisaoSelect = state.modalidadeTemDivisoes ? renderDivisaoSelect('group_' + g.id) : '';
                    const divisaoBadge = g.divisao && !state.modalidadeTemDivisoes ? `<span class="badge badge--primary ms-2">${g.divisao}</span>` : '';
                    const tempBadge = g.isTemporary ? '<span class="badge bg-warning text-dark ms-2" title="Temporário - não salvo">⏳ Temp</span>' : '';
                    return `
                        <div class="rr-modalidades-competitors__row ${g.isTemporary ? 'border-warning' : ''}">
                            <div class="rr-modalidades-competitors__avatar" style="background:linear-gradient(135deg,${g.isTemporary ? '#ffc107,#fd7e14' : '#198754,#0dcaf0'});">
                                ${initials(g.nome)}
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">${g.nome} ${divisaoBadge} ${tempBadge}</div>
                                <div class="rr-modalidades-competitors__meta">${members || 'Sem membros'}</div>
                            </div>
                            ${divisaoSelect}
                            <button class="btn btn-sm rr-btn-orange-outline" data-remove-group="${g.id}"><i class="la la-times"></i></button>
                        </div>
                    `;
                }).join('');
            }
            
            // Atualizar botão "Adicionar Grupo"
            const pendingCount = state.pendingGroup.size;
            if (addGroupBtn) {
                addGroupBtn.innerHTML = `<i class="la la-plus-circle"></i> Adicionar ${state.modalidadeTipo} (${pendingCount}/${teamSize})`;
                addGroupBtn.disabled = pendingCount !== teamSize;
                addGroupBtn.classList.remove('rr-btn-orange-outline');
                addGroupBtn.classList.add('rr-btn-orange');
            }
            
            // Event listeners para toggle seleção
            availableEl.querySelectorAll('[data-toggle-pending]').forEach(row => {
                row.addEventListener('click', () => {
                    const id = parseInt(row.getAttribute('data-toggle-pending'));
                    if (state.pendingGroup.has(id)) {
                        state.pendingGroup.delete(id);
                    } else if (state.pendingGroup.size < teamSize) {
                        state.pendingGroup.add(id);
                    }
                    render();
                });
            });
            
            // Event listeners para remover grupo
            selectedEl.querySelectorAll('[data-remove-group]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = parseInt(btn.getAttribute('data-remove-group'));
                    state.selected.delete('group_' + id);
                    state.groups.delete(id);
                    state.tempGroups.delete(id); // Remover de temporários também
                    // Liberar competidores do grupo
                    state.competitorInGroup.forEach((gid, cid) => {
                        if (gid === id) state.competitorInGroup.delete(cid);
                    });
                    render();
                });
            });
            
        } else {
            // === MODO INDIVIDUAL ===
            const all = Array.from(state.byId.values());
            const selected = all.filter(c => state.selected.has(c.id) && matchesFilter(c.searchText || c.nome, filter));
            const available = all.filter(c => !state.selected.has(c.id) && matchesFilter(c.searchText || c.nome, filter));

            document.getElementById('selectedCount').textContent = String(state.selected.size);
            document.getElementById('availableCount').textContent = String(available.length);

            if (available.length === 0) {
                availableEl.innerHTML = '<div class="text-muted text-center py-4">Nenhum disponível</div>';
            } else {
                availableEl.innerHTML = available.map(c => `
                    <div class="rr-modalidades-competitors__row">
                        <div class="rr-modalidades-competitors__avatar">
                            ${initials(c.nome)}
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">${c.nome}</div>
                            <div class="rr-modalidades-competitors__meta">ID #${c.id}</div>
                        </div>
                        <button class="btn btn-sm rr-btn-orange" data-add="${c.id}"><i class="la la-plus"></i></button>
                    </div>
                `).join('');
            }

            if (selected.length === 0) {
                selectedEl.innerHTML = '<div class="text-muted text-center py-4">Nenhum competidor vinculado</div>';
            } else {
                selectedEl.innerHTML = selected.map(c => {
                    const divisaoSelect = state.modalidadeTemDivisoes ? renderDivisaoSelect(c.id) : '';
                    return `
                        <div class="rr-modalidades-competitors__row">
                            <div class="rr-modalidades-competitors__avatar" style="background:linear-gradient(135deg,#198754,#0dcaf0);">
                                ${initials(c.nome)}
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">${c.nome}</div>
                                <div class="rr-modalidades-competitors__meta">ID #${c.id}</div>
                            </div>
                            ${divisaoSelect}
                            <button class="btn btn-sm rr-btn-orange-outline" data-remove="${c.id}"><i class="la la-times"></i></button>
                        </div>
                    `;
                }).join('');
            }
            
            // Esconder botão de grupo em modo individual
            if (addGroupBtn) {
                addGroupBtn.innerHTML = '<i class="la la-check-square"></i> Adicionar todos';
                addGroupBtn.disabled = false;
            }

            // Event listeners para adicionar/remover
            availableEl.querySelectorAll('[data-add]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = parseInt(btn.getAttribute('data-add'));
                    state.selected.add(id);
                    render();
                });
            });

            selectedEl.querySelectorAll('[data-remove]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = parseInt(btn.getAttribute('data-remove'));
                    state.selected.delete(id);
                    state.divisaoById.delete(id);
                    render();
                });
            });
        }
        
        // Divisão selects (comum para ambos modos)
        selectedEl.querySelectorAll('[data-divisao-select]').forEach(sel => {
            sel.addEventListener('change', () => {
                const idAttr = sel.getAttribute('data-divisao-select');
                const val = sel.value;
                if (val) {
                    state.divisaoById.set(idAttr.startsWith('group_') ? idAttr : parseInt(idAttr), val);
                } else {
                    state.divisaoById.delete(idAttr.startsWith('group_') ? idAttr : parseInt(idAttr));
                }
            });
        });

        updateGroupSummary();
    }
    
    function renderDivisaoSelect(id) {
        if (!state.modalidadeTemDivisoes || !Array.isArray(state.modalidadeDivisoes) || state.modalidadeDivisoes.length === 0) {
            return '';
        }
        const current = String(state.divisaoById.get(id) || '');
        const options = ['<option value="">— Divisão —</option>']
            .concat(state.modalidadeDivisoes.map(d => {
                const val = String(d || '');
                const sel = (val !== '' && val === current) ? 'selected' : '';
                return `<option value="${val.replace(/"/g, '&quot;')}" ${sel}>${val}</option>`;
            }))
            .join('');
        return `<select class="form-select form-select-sm" data-divisao-select="${id}" style="min-width:120px;">${options}</select>`;
    }

    function updateGroupSummary() {
        // Título do painel direito
        const titleEl = document.getElementById('selectedPanelTitle');
        if (titleEl) {
            titleEl.textContent = state.groupMode ? 'Grupos vinculados' : 'Vinculados';
        }

        // Placeholder da busca
        const searchInput = document.getElementById('competitorsSearchInput');
        if (searchInput) {
            searchInput.placeholder = state.groupMode ? 'Filtrar competidores...' : 'Filtrar por nome...';
        }
        
        // Habilitar/desabilitar salvar
        if (saveBtn) {
            saveBtn.disabled = !state.modalidadeId;
        }
        
        // Tipo label
        const tipoLabelEl = document.getElementById('modalidadeTipoLabel');
        if (tipoLabelEl) {
            const labels = {
                individual: 'Individual',
                dupla: 'Dupla',
                trio: 'Trio',
                quarteto: 'Quarteto',
                quinteto: 'Quinteto',
            };
            const label = labels[state.modalidadeTipo] || 'Individual';
            tipoLabelEl.textContent = `${label} (${state.modalidadeTeamSize})`;
        }
    }

    async function loadCompetitors(modalidadeId, modalidadeNome) {
        state.modalidadeId = parseInt(modalidadeId);
        state.modalidadeNome = modalidadeNome;
        document.getElementById('modalidadeIdField').value = String(modalidadeId);
        
        const search = document.getElementById('competitorsSearchInput');
        if (search) search.value = '';
        state.lastFilter = '';

        document.getElementById('availableList').innerHTML = '<div class="text-muted text-center py-4">Carregando...</div>';
        document.getElementById('selectedList').innerHTML = '<div class="text-muted text-center py-4">Carregando...</div>';
        mainContent.style.display = 'block';

        const r = await fetch(`{{ url('admin/modalidades') }}/${modalidadeId}/competitors`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await r.json();

        // Limpar estado
        state.byId.clear();
        state.selected.clear();
        state.groupMode = false;
        state.divisaoById.clear();
        state.pendingGroup.clear();
        state.groups.clear();
        state.competitorInGroup.clear();

        const attached = Array.isArray(data.attached) ? data.attached : [];
        const available = Array.isArray(data.available) ? data.available : [];
        const groupMode = !!data.group_mode;
        const groups = data && data.groups ? data.groups : { attached: [], available: [] };

        try {
            const m = data && data.modalidade ? data.modalidade : null;
            state.modalidadeTemDivisoes = !!(m && m.tem_divisoes);
            state.modalidadeDivisoes = Array.isArray(m && m.divisoes ? m.divisoes : []) ? (m.divisoes || []) : [];
            state.modalidadeTeamSize = parseInt(m && m.tamanho_equipe ? m.tamanho_equipe : 1);
            state.modalidadeTipo = String(m && m.tipo_participacao ? m.tipo_participacao : 'individual');
            state.modalidadeStatus = String(m && m.status ? m.status : 'ativo');
            state.hasScores = !!(m && m.has_scores);
            state.hasClassificatoriaGroups = !!(m && m.has_classificatoria_groups);
            
            const statusSemDivisao = ['programado', 'classificatoria', 'ativo'];
            state.isClassificatoria = statusSemDivisao.includes(state.modalidadeStatus);
        } catch (e) {
            state.modalidadeTemDivisoes = false;
            state.modalidadeDivisoes = [];
            state.modalidadeTeamSize = 1;
            state.modalidadeTipo = 'individual';
            state.modalidadeStatus = 'ativo';
            state.hasScores = false;
            state.hasClassificatoriaGroups = false;
            state.isClassificatoria = true;
        }

        state.groupMode = groupMode;

        // Carregar competidores individuais (sempre disponíveis)
        [...attached, ...available].forEach(c => {
            state.byId.set(c.id, { ...c, searchText: c.nome, isCompetitor: true });
        });

        // Se é modo grupo, carregar grupos e marcar competidores que já estão em grupos
        if (state.groupMode) {
            const attachedGroups = Array.isArray(groups.attached) ? groups.attached : [];
            const availableGroups = Array.isArray(groups.available) ? groups.available : [];
            
            [...attachedGroups, ...availableGroups].forEach(g => {
                const searchText = [g.nome, ...(g.members || []).map(m => m.nome)].join(' ');
                state.groups.set(g.id, { ...g, searchText, isGroup: true });
                
                // Marcar membros como pertencentes a este grupo
                (g.members || []).forEach(m => {
                    state.competitorInGroup.set(m.id, g.id);
                });
            });
            
            // Grupos vinculados vão para selected
            attachedGroups.forEach(g => {
                state.selected.add('group_' + g.id);
                if (g.divisao) state.divisaoById.set('group_' + g.id, g.divisao);
            });
        } else {
            // Modo individual: competidores vinculados
            attached.forEach(c => {
                state.selected.add(c.id);
                const div = String(c.divisao || '').trim();
                if (div) state.divisaoById.set(c.id, div);
            });
        }

        const titleEl = document.getElementById('competitorsPopoutTitle');
        if (titleEl) {
            const tipoLabels = {
                individual: 'Participantes',
                dupla: 'Duplas',
                trio: 'Trios',
                quarteto: 'Quartetos',
                quinteto: 'Quintetos',
            };
            titleEl.innerHTML = `<i class="la la-users-cog"></i> ${tipoLabels[state.modalidadeTipo] || 'Participantes'}`;
        }

        updateInfoBox();
        render();
    }

    async function saveCompetitors() {
        if (state.saving || !state.modalidadeId) return;
        state.saving = true;

        const modalidadeId = state.modalidadeId;
        const btn = document.getElementById('btnSaveCompetitors');
        const old = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Salvando...';

        try {
            let payload;
            
            if (state.groupMode) {
                // Passo 1: Criar grupos temporários no backend
                const tempGroups = Array.from(state.tempGroups.values());
                const createdGroupIds = [];
                
                if (tempGroups.length > 0) {
                    for (const tempGroup of tempGroups) {
                        const competitorIds = tempGroup.members.map(m => m.id);
                        
                        const r = await fetch(`{{ url('admin/modalidades') }}/${modalidadeId}/groups/json`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                competitor_ids: competitorIds,
                                is_classificatoria: state.isClassificatoria
                            })
                        });
                        
                        const data = await r.json();
                        
                        if (r.ok && data.group) {
                            // Mapear ID temporário para ID real
                            const realId = data.group.id;
                            createdGroupIds.push(realId);
                            
                            // Atualizar selected: remover temp, adicionar real
                            state.selected.delete('group_' + tempGroup.id);
                            state.selected.add('group_' + realId);
                            
                            // Atualizar groups map
                            state.groups.delete(tempGroup.id);
                            state.groups.set(realId, {
                                ...tempGroup,
                                id: realId,
                                isTemporary: false
                            });
                        } else {
                            throw new Error(data.message || 'Erro ao criar grupo');
                        }
                    }
                    
                    // Limpar grupos temporários
                    state.tempGroups.clear();
                }
                
                // Passo 2: Enviar todos os grupos (reais + recém-criados)
                const groupIds = Array.from(state.selected).map(s => {
                    if (typeof s === 'string' && s.startsWith('group_')) {
                        return parseInt(s.replace('group_', ''));
                    }
                    return parseInt(s);
                }).filter(id => !isNaN(id) && id > 0); // Filtrar IDs negativos
                
                const divMap = {};
                state.divisaoById.forEach((div, key) => {
                    const gid = typeof key === 'string' && key.startsWith('group_') 
                        ? key.replace('group_', '') 
                        : String(key);
                    const gidNum = parseInt(gid);
                    if (div && gidNum > 0) divMap[gid] = div; // Só divisões de grupos reais
                });
                
                payload = { 
                    group_ids: groupIds, 
                    group_divisoes: divMap,
                    is_classificatoria: state.isClassificatoria 
                };
            } else {
                // Modo individual: enviar IDs dos competidores
                const ids = Array.from(state.selected.values());
                const divMap = {};
                
                if (state.modalidadeTemDivisoes && state.modalidadeDivisoes.length > 0) {
                    for (const id of ids) {
                        const div = String(state.divisaoById.get(id) || '').trim();
                        if (div) {
                            divMap[String(id)] = div;
                        }
                    }
                }
                
                payload = { 
                    competitor_ids: ids, 
                    competitor_divisoes: divMap, 
                    is_classificatoria: state.isClassificatoria 
                };
            }
            
            const r = await fetch(`{{ url('admin/modalidades') }}/${modalidadeId}/competitors/attach`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            const contentType = (r.headers.get('content-type') || '').toLowerCase();
            if (!r.ok) {
                const text = await r.text();
                let msg = `HTTP ${r.status} ${r.statusText}`;
                try {
                    if (text && contentType.includes('application/json')) {
                        const j = JSON.parse(text);
                        if (j && (j.message || j.error)) {
                            msg = j.message || j.error;
                        }
                    }
                } catch (e) {}
                throw new Error(`${msg}${text ? ' :: ' + text : ''}`);
            }
            const resp = contentType.includes('application/json') ? await r.json() : { message: 'Salvo' };
            safeNotify('success', resp.message || 'Participantes salvos!');

            // Atualizar badge na tabela
            const badge = document.querySelector(`.competitors-count-badge[data-modalidade-id="${modalidadeId}"]`);
            if (badge) {
                if (state.groupMode) {
                    let total = 0;
                    state.groups.forEach(g => {
                        if (state.selected.has('group_' + g.id) && Array.isArray(g.members)) {
                            total += g.members.length;
                        }
                    });
                    badge.textContent = String(total);
                } else {
                    badge.textContent = String(state.selected.size);
                }
            }

            if (bsModal) bsModal.hide();
        } catch (err) {
            console.error(err);
            safeNotify('error', err?.message || 'Falha ao salvar');
        } finally {
            state.saving = false;
            btn.disabled = false;
            btn.innerHTML = old;
        }
    }

    // Event: Rodeio selecionado
    if (rodeioSelect) {
        rodeioSelect.addEventListener('change', () => {
            const rodeioId = rodeioSelect.value;
            mainContent.style.display = 'none';
            state.modalidadeId = null;
            infoBox.innerHTML = '<span class="text-muted">Selecione uma modalidade</span>';
            loadModalidades(rodeioId);
        });
    }
    
    // Event: Modalidade selecionada
    if (modalidadeSelect) {
        modalidadeSelect.addEventListener('change', () => {
            const opt = modalidadeSelect.selectedOptions[0];
            if (!opt || !opt.value) {
                mainContent.style.display = 'none';
                state.modalidadeId = null;
                infoBox.innerHTML = '<span class="text-muted">Selecione uma modalidade</span>';
                if (saveBtn) saveBtn.disabled = true;
                return;
            }
            
            state.modalidadeTemDivisoes = opt.dataset.temDivisoes === '1';
            state.modalidadeDivisoes = JSON.parse(opt.dataset.divisoes || '[]');
            state.modalidadeTeamSize = parseInt(opt.dataset.tamanhoEquipe || 1);
            state.modalidadeTipo = opt.dataset.tipo || 'individual';
            
            loadCompetitors(opt.value, opt.textContent).catch(err => {
                console.error(err);
                safeNotify('error', 'Falha ao carregar participantes');
            });
        });
    }

    // Abrir modal pelo botão na tabela
    document.addEventListener('click', function(e) {
        const btn = e.target.closest && e.target.closest('.js-open-participantes');
        if (!btn) return;

        const id = btn.getAttribute('data-modalidade-id');
        const nome = btn.getAttribute('data-modalidade-nome') || '';
        const tamanhoEquipe = parseInt(btn.getAttribute('data-tamanho-equipe') || 1);
        const temDivisoes = btn.getAttribute('data-tem-divisoes') === '1';
        const divisoes = JSON.parse(btn.getAttribute('data-divisoes') || '[]');
        
        if (!id || !bsModal) return;

        // Esconder seleção de rodeio/modalidade e ir direto
        const selectionRow = document.getElementById('participantesSelectionRow');
        if (selectionRow) selectionRow.style.display = 'none';
        
        // Setar estado
        state.modalidadeTemDivisoes = temDivisoes;
        state.modalidadeDivisoes = divisoes;
        state.modalidadeTeamSize = tamanhoEquipe;

        bsModal.show();
        loadCompetitors(id, nome).catch(err => {
            console.error(err);
            safeNotify('error', 'Falha ao carregar participantes');
        });
    });
    
    // Reset ao fechar modal
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', () => {
            const selectionRow = document.getElementById('participantesSelectionRow');
            if (selectionRow) selectionRow.style.display = 'flex';
            if (rodeioSelect) rodeioSelect.value = '';
            if (modalidadeSelect) {
                modalidadeSelect.innerHTML = '<option value="">Selecione primeiro o rodeio</option>';
                modalidadeSelect.disabled = true;
            }
            mainContent.style.display = 'none';
            state.modalidadeId = null;
            infoBox.innerHTML = '<span class="text-muted">Selecione uma modalidade</span>';
            
            // Limpar grupos temporários não salvos
            state.tempGroups.clear();
            state.pendingGroup.clear();
            state.nextTempGroupId = -1;
            
            // Remover grupos temporários do state.groups
            Array.from(state.groups.entries()).forEach(([gid, g]) => {
                if (g.isTemporary) {
                    state.groups.delete(gid);
                    state.selected.delete('group_' + gid);
                    // Limpar competidores que estavam marcados nesse grupo
                    if (Array.isArray(g.members)) {
                        g.members.forEach(m => {
                            if (state.competitorInGroup.get(m.id) === gid) {
                                state.competitorInGroup.delete(m.id);
                            }
                        });
                    }
                }
            });
        });
    }

    // Pesquisa
    const searchInput = document.getElementById('competitorsSearchInput');
    if (searchInput) searchInput.addEventListener('input', (e) => {
        state.lastFilter = String(e.target.value || '').toLowerCase().trim();
        render();
    });

    // Botão principal: Adicionar todos (individual) ou Criar grupo (grupo)
    const btnAll = document.getElementById('btnSelectAllFiltered');
    if (btnAll) btnAll.addEventListener('click', async () => {
        if (state.groupMode) {
            // Modo grupo: criar grupo TEMPORARIAMENTE (não salva no banco ainda)
            if (state.pendingGroup.size !== state.modalidadeTeamSize) {
                safeNotify('warning', `Selecione ${state.modalidadeTeamSize} competidores para formar ${state.modalidadeTipo}`);
                return;
            }
            
            // Criar grupo temporário com ID negativo
            const competitorIds = Array.from(state.pendingGroup);
            const members = competitorIds.map(id => state.byId.get(id)).filter(Boolean);
            const groupName = members.map(m => m.nome).join(' + ');
            const tempId = state.nextTempGroupId--;
            
            const newGroup = {
                id: tempId,
                nome: groupName,
                members: members,
                divisao: '',
                searchText: groupName,
                isGroup: true,
                isTemporary: true // Flag para indicar que é temporário
            };
            
            // Adicionar ao state temporário
            state.tempGroups.set(tempId, newGroup);
            state.groups.set(tempId, newGroup);
            state.selected.add('group_' + tempId);
            
            // Marcar competidores como pertencentes ao grupo
            competitorIds.forEach(cid => {
                state.competitorInGroup.set(cid, tempId);
            });
            
            // Limpar seleção pendente
            state.pendingGroup.clear();
            
            safeNotify('success', `${state.modalidadeTipo.charAt(0).toUpperCase() + state.modalidadeTipo.slice(1)} adicionado! Clique em "Salvar Participantes" para confirmar.`);
            render();
        } else {
            // Modo individual: adicionar todos os filtrados
            const filter = state.lastFilter;
            const all = Array.from(state.byId.values());
            all.forEach(c => {
                if (!state.selected.has(c.id) && matchesFilter(c.searchText || c.nome, filter)) {
                    state.selected.add(c.id);
                }
            });
            render();
        }
    });

    // Limpar seleção
    const btnClear = document.getElementById('btnClearSelection');
    if (btnClear) btnClear.addEventListener('click', () => {
        state.selected.clear();
        state.divisaoById.clear();
        state.pendingGroup.clear();
        render();
    });

    // Salvar
    if (saveBtn) saveBtn.addEventListener('click', saveCompetitors);
    
    // Criar grupo - redireciona para página de grupos
    if (createGroupBtn) {
        createGroupBtn.addEventListener('click', () => {
            if (state.modalidadeId) {
                window.location.href = `{{ url('admin/modalidades') }}/${state.modalidadeId}/groups`;
            }
        });
    }
    document.addEventListener('click', function(e) {
        const btn = e.target.closest && e.target.closest('.js-open-competitors');
        if (!btn) return;

        const id = btn.getAttribute('data-modalidade-id');
        const nome = btn.getAttribute('data-modalidade-nome') || '';
        if (!id || !bsModal) return;

        bsModal.show();
        loadCompetitors(id, nome).catch(err => {
            console.error(err);
            safeNotify('error', 'Falha ao carregar lista');
        });
    });

    // Toggle Pausar X1
    document.addEventListener('click', function(e) {
        const btn = e.target.closest && e.target.closest('.js-toggle-pause-x1');
        if (!btn) return;

        e.preventDefault();
        
        const modalidadeId = btn.getAttribute('data-modalidade-id');
        if (!modalidadeId) return;
        
        // Desabilitar botão durante requisição
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="las la-spinner la-spin"></i> Processando...';
        
        fetch(`{{ url('admin/modalidades') }}/${modalidadeId}/toggle-pause-x1`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Atualizar estado do botão
                const isPaused = data.pausar_x1;
                btn.classList.toggle('paused', isPaused);
                btn.setAttribute('data-pause-state', isPaused ? 'true' : 'false');
                
                // Atualizar ícone e texto
                const icon = btn.querySelector('i');
                icon.className = isPaused ? 'las la-play' : 'las la-pause';
                btn.innerHTML = `<i class="${icon.className}"></i> ${isPaused ? 'Ativar X1' : 'Pausar X1'}`;
                
                // Mostrar notificação
                safeNotify('success', data.message);
            } else {
                safeNotify('error', data.message || 'Erro ao alterar status');
                btn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            safeNotify('error', 'Erro na requisição');
            btn.innerHTML = originalText;
        })
        .finally(() => {
            btn.disabled = false;
        });
    });
})();
</script>
@endpush

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.dashboard') }}" />
@endpush

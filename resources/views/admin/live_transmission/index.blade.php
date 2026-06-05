@extends('admin.layouts.app')
@section('panel')

<style>
/* ===== LIVE TRANSMISSION - REDESIGN OTIMIZADO PC ===== */
:root {
    --lt-bg-primary: #0a0e1a;
    --lt-bg-secondary: #111827;
    --lt-bg-surface: #1a1f2e;
    --lt-border: rgba(255, 255, 255, 0.08);
    --lt-accent: #f97316;
    --lt-success: #10b981;
    --lt-danger: #ef4444;
    --lt-text: #e2e8f0;
    --lt-text-muted: #94a3b8;
}

body {
    background: var(--lt-bg-primary) !important;
    color: var(--lt-text);
}

.lt-container {
    max-width: 100%;
    padding: 1rem;
    height: 100vh;
    overflow: hidden;
}

/* ===== VIDEO SECTION (TOPO) ===== */
.lt-video-section {
    background: var(--lt-bg-surface);
    border-radius: 16px;
    border: 1px solid var(--lt-border);
    margin-bottom: 1rem;
    overflow: hidden;
}

.lt-video-wrapper {
    position: relative;
    width: 100%;
    padding-top: 42%; /* 21:9 aspect ratio */
    background: #000;
}

.lt-video-wrapper iframe,
.lt-video-wrapper .lt-video-placeholder {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.lt-video-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 1rem;
    background: linear-gradient(135deg, #1a1f2e 0%, #0a0e1a 100%);
}

.lt-video-placeholder i {
    font-size: 4rem;
    color: var(--lt-text-muted);
}

/* ===== CONTROLES INLINE (ABAIXO DO VÍDEO) ===== */
.lt-controls {
    display: grid;
    grid-template-columns: 2fr 1.35fr 1.35fr 1fr auto;
    gap: 0.75rem;
    padding: 1rem;
    background: var(--lt-bg-secondary);
    align-items: end;
}

.lt-control-group label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--lt-text-muted);
    margin-bottom: 0.375rem;
    letter-spacing: 0.5px;
}

.lt-control-group input,
.lt-control-group select {
    width: 100%;
    padding: 0.625rem 0.875rem;
    background: var(--lt-bg-primary);
    border: 1px solid var(--lt-border);
    border-radius: 8px;
    color: var(--lt-text);
    font-size: 0.875rem;
    transition: all 0.2s;
}

.lt-control-group select:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: rgba(0, 0, 0, 0.3);
}

.lt-control-group input:focus,
.lt-control-group select:focus {
    outline: none;
    border-color: var(--lt-accent);
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
}

.lt-btn-save {
    padding: 0.625rem 1.5rem;
    background: var(--lt-accent);
    border: none;
    border-radius: 8px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.lt-btn-save:hover {
    background: #ea580c;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
}

.lt-btn-save:active {
    transform: translateY(0);
}

.lt-btn-finalize {
    padding: 0.625rem 1.5rem;
    background: var(--lt-success);
    border: none;
    border-radius: 8px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.lt-btn-finalize:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.lt-btn-finalize:active {
    transform: translateY(0);
}

.lt-btn-finalize:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ===== LAYOUT PRINCIPAL (SPLIT) ===== */
.lt-main-split {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 1rem;
    height: calc(100vh - 380px); /* Ajustar baseado na altura do vídeo + controles */
    overflow: hidden;
}

/* ===== LISTA DE COMPETIDORES (ESQUERDA) ===== */
.lt-competitors {
    background: var(--lt-bg-surface);
    border-radius: 16px;
    border: 1px solid var(--lt-border);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.lt-competitors-header {
    padding: 1rem;
    border-bottom: 1px solid var(--lt-border);
}

.lt-competitors-header h3 {
    font-size: 1rem;
    font-weight: 700;
    margin: 0 0 0.75rem 0;
    color: var(--lt-text);
}

.lt-search-box {
    position: relative;
}

.lt-search-box i {
    position: absolute;
    left: 0.875rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--lt-text-muted);
    font-size: 1.125rem;
}

.lt-search-box input {
    width: 100%;
    padding: 0.625rem 0.875rem 0.625rem 2.75rem;
    background: var(--lt-bg-primary);
    border: 1px solid var(--lt-border);
    border-radius: 8px;
    color: var(--lt-text);
    font-size: 0.875rem;
}

.lt-competitors-list {
    flex: 1;
    overflow-y: auto;
    padding: 0.5rem;
}

.lt-competitor-item {
    padding: 0.875rem 1rem;
    background: var(--lt-bg-secondary);
    border: 2px solid transparent;
    border-radius: 10px;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.lt-competitor-item:hover {
    background: var(--lt-bg-primary);
    border-color: var(--lt-border);
}

.lt-competitor-item.active {
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(251, 146, 60, 0.1));
    border-color: var(--lt-accent);
    box-shadow: 0 0 20px rgba(249, 115, 22, 0.2);
}

.lt-competitor-name {
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--lt-text);
}

.lt-competitor-score {
    font-size: 0.75rem;
    color: var(--lt-text-muted);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.lt-competitor-score .badge {
    background: var(--lt-accent);
    color: white;
    padding: 0.25rem 0.625rem;
    border-radius: 999px;
    font-weight: 700;
    font-size: 0.75rem;
}

/* ===== PAINEL DE PONTUAÇÃO (DIREITA) ===== */
.lt-scoring-panel {
    background: var(--lt-bg-surface);
    border-radius: 16px;
    border: 1px solid var(--lt-border);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.lt-scoring-header {
    padding: 1rem 1.25rem;
    background: var(--lt-bg-secondary);
    border-bottom: 1px solid var(--lt-border);
}

.lt-scoring-header h3 {
    font-size: 1rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    color: var(--lt-text);
}

.lt-selected-competitor {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background: var(--lt-bg-primary);
    border-radius: 8px;
    margin-top: 0.5rem;
}

.lt-selected-competitor-name {
    font-weight: 600;
    font-size: 0.875rem;
}

.lt-selected-competitor-total {
    font-weight: 700;
    font-size: 1.25rem;
    color: var(--lt-accent);
}

.lt-scoring-body {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
}

.lt-scoring-section {
    margin-bottom: 1.5rem;
}

.lt-scoring-section-title {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--lt-text-muted);
    margin-bottom: 0.75rem;
    letter-spacing: 0.5px;
}

.lt-scoring-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
}

.lt-score-btn {
    padding: 0.875rem 1rem;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    overflow: hidden;
}

.lt-score-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    transform: translateX(-100%);
    transition: transform 0.6s;
}

.lt-score-btn:hover::before {
    transform: translateX(100%);
}

.lt-score-btn.success {
    background: var(--lt-success);
    color: white;
}

.lt-score-btn.success:hover {
    background: #059669;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.lt-score-btn.danger {
    background: var(--lt-danger);
    color: white;
}

.lt-score-btn.danger:hover {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.lt-score-btn:active {
    transform: translateY(0);
}

.lt-score-btn .badge {
    background: rgba(255, 255, 255, 0.25);
    color: white;
    padding: 0.25rem 0.625rem;
    border-radius: 999px;
    font-weight: 700;
    font-size: 0.75rem;
}

/* Animação de sucesso */
.lt-score-btn.saving {
    opacity: 0.6;
    pointer-events: none;
}

.lt-score-btn.success-flash {
    animation: successFlash 0.5s ease;
}

@keyframes successFlash {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; transform: scale(1.05); }
}

/* Custom Score */
.lt-custom-score {
    margin-top: 1rem;
    padding: 1rem;
    background: var(--lt-bg-primary);
    border-radius: 10px;
    border: 1px solid var(--lt-border);
}

.lt-custom-score-title {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--lt-text-muted);
    margin-bottom: 0.75rem;
}

.lt-custom-score-inputs {
    display: grid;
    grid-template-columns: 100px 1fr auto;
    gap: 0.5rem;
    align-items: end;
}

.lt-custom-score-inputs input {
    padding: 0.625rem;
    background: var(--lt-bg-secondary);
    border: 1px solid var(--lt-border);
    border-radius: 8px;
    color: var(--lt-text);
    font-size: 0.875rem;
}

.lt-btn-custom-add {
    padding: 0.625rem 1.25rem;
    background: var(--lt-accent);
    border: none;
    border-radius: 8px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.lt-btn-custom-add:hover {
    background: #ea580c;
}

/* ===== TOAST NOTIFICATIONS ===== */
.lt-toast {
    position: fixed;
    top: 2rem;
    right: 2rem;
    min-width: 300px;
    background: var(--lt-bg-secondary);
    border: 1px solid var(--lt-border);
    border-radius: 12px;
    padding: 1rem 1.5rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    z-index: 99999;
    opacity: 0;
    transform: translateX(400px);
    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.lt-toast.lt-toast-show {
    opacity: 1;
    transform: translateX(0);
}

.lt-toast-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.lt-toast-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.lt-toast-message {
    color: var(--lt-text);
    font-weight: 600;
    font-size: 0.95rem;
}

.lt-toast-success {
    border-left: 4px solid var(--lt-success);
}

.lt-toast-danger {
    border-left: 4px solid var(--lt-danger);
}

.lt-toast-warning {
    border-left: 4px solid #f59e0b;
}

/* ===== BOTÃO TOGGLE POPOUT (INLINE) ===== */
.lt-btn-toggle-popout {
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #1d4ed8, #4f46e5);
    border: none;
    border-radius: 8px;
    color: white;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.lt-btn-toggle-popout:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(124, 58, 237, 0.4);
}

.lt-btn-toggle-popout i {
    font-size: 1.125rem;
}

/* ===== POPOUT DRAGGABLE CONTAINER ===== */
.lt-popout-container {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 85vw;
    height: 80vh;
    max-width: 1600px;
    background: var(--lt-bg-primary);
    border: 2px solid var(--lt-border);
    border-radius: 16px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.7);
    z-index: 9999;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: opacity 0.3s, transform 0.3s;
}

.lt-popout-container.closed {
    display: none;
}

.lt-popout-container.minimized {
    height: auto;
}

.lt-popout-container.minimized .lt-popout-body {
    display: none;
}

.lt-popout-container.maximized {
    width: 95vw;
    height: 90vh;
    top: 5vh;
    left: 2.5vw;
    transform: none;
    max-width: none;
}

/* ===== POPOUT HEADER ===== */
.lt-popout-header {
    background: linear-gradient(135deg, var(--lt-bg-secondary), var(--lt-bg-primary));
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--lt-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: move;
    user-select: none;
}

.lt-popout-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 700;
    font-size: 1rem;
    color: var(--lt-text);
}

.lt-popout-title i {
    font-size: 1.5rem;
    color: var(--lt-accent);
}

.lt-popout-controls {
    display: flex;
    gap: 0.5rem;
}

.lt-popout-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    color: var(--lt-text);
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.lt-popout-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: scale(1.1);
}

.lt-popout-btn:active {
    transform: scale(0.95);
}

/* ===== POPOUT BODY ===== */
.lt-popout-body {
    flex: 1;
    overflow: hidden;
}

.lt-popout-body .lt-main-split {
    height: 100%;
}

/* ===== RESIZE HANDLES ===== */
.lt-popout-resize {
    position: absolute;
    z-index: 10;
}

.lt-popout-resize-n {
    top: 0;
    left: 0;
    right: 0;
    height: 8px;
    cursor: ns-resize;
}

.lt-popout-resize-s {
    bottom: 0;
    left: 0;
    right: 0;
    height: 8px;
    cursor: ns-resize;
}

.lt-popout-resize-e {
    top: 0;
    right: 0;
    bottom: 0;
    width: 8px;
    cursor: ew-resize;
}

.lt-popout-resize-w {
    top: 0;
    left: 0;
    bottom: 0;
    width: 8px;
    cursor: ew-resize;
}

.lt-popout-resize-nw {
    top: 0;
    left: 0;
    width: 16px;
    height: 16px;
    cursor: nwse-resize;
}

.lt-popout-resize-ne {
    top: 0;
    right: 0;
    width: 16px;
    height: 16px;
    cursor: nesw-resize;
}

.lt-popout-resize-sw {
    bottom: 0;
    left: 0;
    width: 16px;
    height: 16px;
    cursor: nesw-resize;
}

.lt-popout-resize-se {
    bottom: 0;
    right: 0;
    width: 16px;
    height: 16px;
    cursor: nwse-resize;
}

/* ===== SCROLLBARS ===== */
.lt-competitors-list::-webkit-scrollbar,
.lt-scoring-body::-webkit-scrollbar {
    width: 8px;
}

.lt-competitors-list::-webkit-scrollbar-track,
.lt-scoring-body::-webkit-scrollbar-track {
    background: transparent;
}

.lt-competitors-list::-webkit-scrollbar-thumb,
.lt-scoring-body::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 999px;
}

.lt-competitors-list::-webkit-scrollbar-thumb:hover,
.lt-scoring-body::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}

/* ===== EMPTY STATES ===== */
.lt-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    gap: 1rem;
    color: var(--lt-text-muted);
}

.lt-empty-state i {
    font-size: 3rem;
}

.lt-empty-state p {
    margin: 0;
    font-size: 0.875rem;
}

/* ===== LOADING SPINNER ===== */
.lt-loading {
    display: inline-block;
    width: 14px;
    height: 14px;
    border: 2px solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1400px) {
    .lt-main-split {
        grid-template-columns: 300px 1fr;
    }
}

@media (max-width: 1200px) {
    .lt-controls {
        grid-template-columns: 1fr;
    }
    
    .lt-main-split {
        grid-template-columns: 1fr;
        height: auto;
    }
    
    .lt-competitors {
        max-height: 400px;
    }
}

@media (max-width: 767px) {
    .lt-container {
        height: auto;
        min-height: 100svh;
        padding: 0.75rem;
        overflow: visible;
    }

    .lt-video-wrapper {
        padding-top: 56%;
    }

    .lt-controls {
        padding: 0.85rem;
    }

    .lt-control-group input,
    .lt-control-group select,
    .lt-btn-save,
    .lt-btn-finalize,
    .lt-btn-toggle-popout {
        min-height: 46px;
    }

    .lt-main-split {
        gap: 0.75rem;
    }

    .lt-competitors {
        max-height: 320px;
    }

    .lt-competitors-header,
    .lt-scoring-panel {
        padding: 0.85rem;
    }

    .lt-scoring-buttons {
        grid-template-columns: 1fr;
    }

    .lt-score-btn {
        min-height: 58px;
    }

    .lt-custom-score-inputs {
        grid-template-columns: 1fr;
    }
}

/* ============================================
   MODAL DE DESQUALIFICAÇÃO
   ============================================ */
.lt-disqualify-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 99999;
    align-items: center;
    justify-content: center;
}

.lt-disqualify-modal.active {
    display: flex;
}

.lt-disqualify-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 1;
}

.lt-disqualify-content {
    position: relative;
    z-index: 2;
    background: var(--rr-bg-card, rgba(15, 23, 42, 0.95));
    border: 2px solid rgba(239, 68, 68, 0.3);
    border-radius: 16px;
    width: min(550px, 90vw);
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 8px 32px rgba(239, 68, 68, 0.4), 0 20px 60px rgba(0, 0, 0, 0.8);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.lt-disqualify-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24px;
    border-bottom: 1px solid rgba(239, 68, 68, 0.2);
    background: rgba(239, 68, 68, 0.05);
}

.lt-disqualify-header h3 {
    color: #ef4444;
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
}

.lt-disqualify-close {
    background: none;
    border: none;
    color: #94a3b8;
    font-size: 2rem;
    cursor: pointer;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.lt-disqualify-close:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.lt-disqualify-body {
    padding: 32px 24px;
    color: #e2e8f0;
}

.lt-disqualify-error-info {
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(239, 68, 68, 0.2);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    text-align: left;
}

.lt-disqualify-competitor,
.lt-disqualify-error-action,
.lt-disqualify-error-points {
    margin: 8px 0;
    font-size: 1rem;
    color: #94a3b8;
}

.lt-disqualify-competitor strong,
.lt-disqualify-error-action strong,
.lt-disqualify-error-points strong {
    color: #e2e8f0;
    font-size: 1.1rem;
}

.lt-disqualify-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.05));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ef4444;
    animation: iconPulse 2s ease-in-out infinite;
}

.lt-disqualify-icon svg {
    width: 48px;
    height: 48px;
}

@keyframes iconPulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 0 20px rgba(239, 68, 68, 0);
    }
}

.lt-disqualify-question {
    font-size: 1.1rem;
    text-align: center;
    margin-bottom: 24px;
    line-height: 1.6;
}

.lt-disqualify-warning {
    color: #fbbf24;
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 16px;
    text-align: center;
}

.lt-disqualify-list {
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    padding: 20px 24px;
    list-style: none;
    margin: 0 0 24px 0;
}

.lt-disqualify-list li {
    padding: 10px 0;
    font-size: 0.95rem;
    color: #94a3b8;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.lt-disqualify-list li:last-child {
    border-bottom: none;
}

.lt-disqualify-footer {
    display: flex;
    gap: 12px;
    padding: 20px 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    background: rgba(0, 0, 0, 0.2);
}

.lt-btn {
    flex: 1;
    padding: 14px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
}

.lt-btn-secondary {
    background: rgba(255, 255, 255, 0.05);
    color: #94a3b8;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.lt-btn-secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #e2e8f0;
}

.lt-btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #ffffff;
    box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3);
}

.lt-btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(239, 68, 68, 0.5);
}

.lt-btn-danger:active {
    transform: translateY(0);
}
</style>

<div class="lt-container">
    <!-- VIDEO + CONTROLES -->
    <div class="lt-video-section">
        <div class="lt-video-wrapper" id="videoWrapper">
            <div class="lt-video-placeholder">
                <i class="las la-video"></i>
                <p style="color: var(--lt-text-muted);">Adicione uma URL de transmissão para carregar o stream</p>
            </div>
        </div>
        
        <div class="lt-controls">
            <div class="lt-control-group">
                <label>Live</label>
                <input type="url" id="streamUrl" placeholder="https://youtube.com/watch?v=...">
            </div>
            
            <div class="lt-control-group">
                <label>Rodeio</label>
                <select id="activeRodeio" onchange="loadRodeioData()">
                    <option value="">Selecione o Rodeio</option>
                    @foreach(\App\Models\Rodeio::orderBy('id', 'desc')->get() as $rodeio)
                        <option value="{{ $rodeio->id }}">{{ $rodeio->nome ?? $rodeio->titulo ?? $rodeio->name ?? ('Rodeio #' . $rodeio->id) }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="lt-control-group">
                <label>Modalidade</label>
                <select id="currentModalidade" onchange="loadModalidadeCompetitors()" disabled>
                    <option value="">Selecione a Modalidade</option>
                </select>
            </div>
            
            <div class="lt-control-group">
                <label>Divisão</label>
                <select id="currentDivisao" onchange="onDivisaoChanged()" disabled>
                    <option value="">Selecione a Divisão</option>
                </select>
            </div>
            
            <div class="lt-control-group">
                <label>&nbsp;</label>
                <button class="lt-btn-toggle-popout" id="btnTogglePopout" onclick="togglePopout()">
                    <i class="las la-trophy"></i> Abrir Pontuação
                </button>
            </div>
        </div>
    </div>
    
    <!-- POPOUT DRAGGABLE: COMPETIDORES + PONTUAÇÃO -->
    <div class="lt-popout-container closed" id="popoutContainer">
        <div class="lt-popout-header" id="popoutHeader">
            <div class="lt-popout-title">
                <i class="las la-users"></i>
                <span>Painel de Pontuação</span>
            </div>
            <div class="lt-popout-controls">
                <button class="lt-popout-btn" onclick="undoLastScore()" title="Desfazer Última Pontuação" style="color: #f59e0b;">
                    <i class="las la-undo-alt"></i>
                </button>
                <button class="lt-popout-btn" onclick="minimizePopout()" title="Minimizar">
                    <i class="las la-minus"></i>
                </button>
                <button class="lt-popout-btn" onclick="maximizePopout()" title="Maximizar">
                    <i class="las la-expand"></i>
                </button>
                <button class="lt-popout-btn" onclick="closePopout()" title="Fechar">
                    <i class="las la-times"></i>
                </button>
            </div>
        </div>
        
        <div class="lt-popout-body" id="popoutBody">
            <div class="lt-main-split">
                <!-- LISTA DE COMPETIDORES -->
                <div class="lt-competitors">
                    <div class="lt-competitors-header">
                        <h3>👥 Competidores</h3>
                        <div class="lt-search-box">
                            <i class="las la-search"></i>
                            <input type="text" id="competitorSearch" placeholder="Buscar competidores...">
                        </div>
                    </div>
                    
                    <div class="lt-competitors-list" id="competitorsList">
                        <div class="lt-empty-state">
                            <i class="las la-users"></i>
                            <p>Selecione um rodeio e modalidade</p>
                        </div>
                    </div>
                </div>
                
                <!-- PAINEL DE PONTUAÇÃO -->
                <div class="lt-scoring-panel">
                    <div class="lt-scoring-header">
                        <h3>🎯 Painel de Pontuação</h3>
                        <div class="lt-selected-competitor" id="selectedCompetitorInfo" style="display:none;">
                            <div>
                                <div style="font-size: 0.7rem; color: var(--lt-text-muted); margin-bottom: 0.25rem;">COMPETIDOR SELECIONADO</div>
                                <div class="lt-selected-competitor-name" id="selectedCompetitorName">-</div>
                            </div>
                            <div class="lt-selected-competitor-total" id="selectedCompetitorTotal">0</div>
                        </div>
                    </div>
                    
                    <div class="lt-scoring-body" id="scoringBody">
                        <div class="lt-empty-state">
                            <i class="las la-hand-pointer"></i>
                            <p>Selecione um competidor para pontuar</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- RESIZE HANDLES -->
        <div class="lt-popout-resize lt-popout-resize-n"></div>
        <div class="lt-popout-resize lt-popout-resize-s"></div>
        <div class="lt-popout-resize lt-popout-resize-e"></div>
        <div class="lt-popout-resize lt-popout-resize-w"></div>
        <div class="lt-popout-resize lt-popout-resize-nw"></div>
        <div class="lt-popout-resize lt-popout-resize-ne"></div>
        <div class="lt-popout-resize lt-popout-resize-sw"></div>
        <div class="lt-popout-resize lt-popout-resize-se"></div>
    </div>
</div>

<!-- MODAL DE DESQUALIFICAÇÃO -->
<div id="disqualifyModal" class="lt-disqualify-modal">
    <div class="lt-disqualify-overlay"></div>
    <div class="lt-disqualify-content">
        <div class="lt-disqualify-header">
            <h3>⚠️ Desqualificação</h3>
            <button onclick="closeDisqualifyModal()" class="lt-disqualify-close">&times;</button>
        </div>
        <div class="lt-disqualify-body">
            <div class="lt-disqualify-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div class="lt-disqualify-error-info">
                <p class="lt-disqualify-competitor">
                    Competidor: <strong id="disqualifyCompetitorName"></strong>
                </p>
                <p class="lt-disqualify-error-action">
                    Erro: <strong id="disqualifyErrorLabel" style="color: #ef4444;"></strong>
                </p>
                <p class="lt-disqualify-error-points">
                    Ação: <strong id="disqualifyErrorPoints" style="color: #fbbf24;"></strong>
                </p>
            </div>
            <p class="lt-disqualify-question">
                O competidor foi <strong style="color: #ef4444;">DESQUALIFICADO</strong>?
            </p>
            <p class="lt-disqualify-warning">
                ⚠️ Se <strong>SIM</strong>, a desqualificação irá:
            </p>
            <ul class="lt-disqualify-list">
                <li>❌ Tirar o competidor da lista de escolha no bolão</li>
                <li>🎯 Se estiver em grupo, tirar o grupo inteiro da disponibilidade</li>
                <li>🏆 Dar <strong>VITÓRIA AUTOMÁTICA</strong> para oponentes nas Salas X1</li>
                <li>💰 Processar pagamentos das salas (oponentes recebem prêmio)</li>
                <li>📊 Manter intactas as estatísticas e pontuações já conquistadas</li>
                <li>📧 Notificar usuários afetados</li>
            </ul>
            <p class="lt-disqualify-warning" style="margin-top: 1rem; color: #10b981;">
                ℹ️ Se <strong>NÃO</strong>, apenas a pontuação negativa e estatística serão salvas.
            </p>
        </div>
        <div class="lt-disqualify-footer">
            <button onclick="closeDisqualifyModal()" class="lt-btn lt-btn-secondary">
                Cancelar
            </button>
            <button onclick="addScoreOnly()" class="lt-btn lt-btn-warning" style="background: #f59e0b; color: #000;">
                ⬇️ Não, Apenas Pontuar
            </button>
            <button onclick="confirmDisqualify()" class="lt-btn lt-btn-danger">
                ✅ Sim, Desqualificar
            </button>
        </div>
    </div>
</div>

@php
    $select = ['id', 'rodeio_id', 'nome', 'status', 'inicio'];
    if (\Illuminate\Support\Facades\Schema::hasColumn('modalidades', 'tem_divisoes')) {
        $select[] = 'tem_divisoes';
    }
    if (\Illuminate\Support\Facades\Schema::hasColumn('modalidades', 'divisoes')) {
        $select[] = 'divisoes';
    }

    $preloadedModalidadesByRodeio = \App\Models\Modalidade::select($select)
        ->orderBy('rodeio_id')
        ->orderBy('inicio')
        ->orderBy('nome')
        ->orderBy('id')
        ->get()
        ->groupBy('rodeio_id')
        ->map(function ($items) {
            return $items->map(function ($m) {
                // Extrair nomes das divisões (pode ser array de strings ou array de objetos)
                $divisoesNomes = [];
                if ($m->divisoes) {
                    foreach ($m->divisoes as $div) {
                        if (is_array($div) && isset($div['nome'])) {
                            $divisoesNomes[] = $div['nome'];
                        } elseif (is_string($div)) {
                            $divisoesNomes[] = $div;
                        }
                    }
                }
                
                return [
                    'id' => $m->id,
                    'nome' => $m->nome,
                    'status' => $m->status,
                    'tem_divisoes' => (bool) ($m->tem_divisoes ?? false),
                    'divisoes' => $divisoesNomes,
                ];
            })->values();
        });

    // Preload rodeio status e modalidade_atual para restaurar ao selecionar
    $rodeioSelect = ['id'];
    if (\Illuminate\Support\Facades\Schema::hasColumn('rodeios', 'status_transmissao')) $rodeioSelect[] = 'status_transmissao';
    if (\Illuminate\Support\Facades\Schema::hasColumn('rodeios', 'modalidade_atual')) $rodeioSelect[] = 'modalidade_atual';
    if (\Illuminate\Support\Facades\Schema::hasColumn('rodeios', 'divisao_atual')) $rodeioSelect[] = 'divisao_atual';
    if (\Illuminate\Support\Facades\Schema::hasColumn('rodeios', 'stream_url')) $rodeioSelect[] = 'stream_url';
    $preloadedRodeioState = \App\Models\Rodeio::select($rodeioSelect)
        ->get()
        ->keyBy('id')
        ->map(function ($r) {
            return [
                'status_transmissao' => $r->status_transmissao ?? '',
                'modalidade_atual' => $r->modalidade_atual ?? '',
                'divisao_atual' => $r->divisao_atual ?? '',
                'stream_url' => $r->stream_url ?? '',
            ];
        });
@endphp

<script>
// ===== VARIÁVEIS GLOBAIS =====
let currentCompetitor = null;
let selectedRodeio = null;
let selectedModalidade = null;
let selectedDivisao = null;
let modalidadeRequiresDivisao = false;
let competitors = [];
let maxLogs = 20;
let pendingDisqualification = null; // Armazena dados da desqualificação pendente

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const PRELOADED_MODALIDADES_BY_RODEIO = @json($preloadedModalidadesByRodeio);
const PRELOADED_RODEIO_STATE = @json($preloadedRodeioState);

// ===== BOTÕES DE PONTUAÇÃO =====
const SCORE_BUTTONS = @json($scoreButtonSections);

// ===== HELPERS =====
function el(id) {
    return document.getElementById(id);
}

function getBaseUrl() {
    const origin = window.location.origin;
    const path = window.location.pathname;
    
    // Se estiver em /admin/live-transmission, retornar até /admin
    if (path.includes('/admin/')) {
        const adminIndex = path.indexOf('/admin/');
        return origin + path.substring(0, adminIndex) + '/admin';
    }
    
    return origin;
}

function showToast(message, type = 'success') {
    // Criar elemento de toast
    const toast = document.createElement('div');
    toast.className = `lt-toast lt-toast-${type}`;
    
    const icon = type === 'success' ? '✅' : type === 'danger' ? '❌' : '⚠️';
    toast.innerHTML = `
        <div class="lt-toast-content">
            <span class="lt-toast-icon">${icon}</span>
            <span class="lt-toast-message">${message}</span>
        </div>
    `;
    
    // Adicionar ao body
    document.body.appendChild(toast);
    
    // Animar entrada
    setTimeout(() => {
        toast.classList.add('lt-toast-show');
    }, 10);
    
    // Remover após 4 segundos
    setTimeout(() => {
        toast.classList.remove('lt-toast-show');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 4000);
}

function addLog(message, type = 'info') {
    // Logs desabilitados - removemos a seção de logs
    console.log(`[${type.toUpperCase()}] ${message}`);
}

// ===== CARREGAR STREAM =====
el('streamUrl').addEventListener('change', function() {
    const url = this.value.trim();
    if (!url) {
        saveEventConfig(true);
        return;
    }
    
    const videoWrapper = el('videoWrapper');
    
    // Tentar extrair ID do YouTube
    let videoId = null;
    const patterns = [
        /youtube\.com\/watch\?v=([^&]+)/,
        /youtu\.be\/([^?]+)/,
        /youtube\.com\/embed\/([^?]+)/,
        /youtube\.com\/live\/([^?]+)/
    ];
    
    for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match) {
            videoId = match[1];
            break;
        }
    }
    
    if (videoId) {
        videoWrapper.innerHTML = `
            <iframe 
                src="https://www.youtube.com/embed/${videoId}?autoplay=0&modestbranding=1&rel=0" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen>
            </iframe>
        `;
        console.log('✅ Stream carregado com sucesso');
    } else {
        // Tentar carregar URL diretamente
        videoWrapper.innerHTML = `<iframe src="${url}" frameborder="0" allowfullscreen></iframe>`;
        console.log('✅ Stream carregado');
    }

    saveEventConfig(true);
});

// ===== CARREGAR RODEIO =====
function loadRodeioData() {
    const rodeioId = el('activeRodeio').value;
    const modalidadeSelect = el('currentModalidade');
    const divisaoSelect = el('currentDivisao');
    
    if (!rodeioId) {
        modalidadeSelect.innerHTML = '<option value="">Selecione a Modalidade</option>';
        modalidadeSelect.disabled = true;
        divisaoSelect.innerHTML = '<option value="">Selecione a Divisão</option>';
        divisaoSelect.disabled = true;
        return;
    }
    
    selectedRodeio = rodeioId;
    
    // Carregar modalidades do rodeio
    const modalidades = PRELOADED_MODALIDADES_BY_RODEIO[rodeioId] || [];
    
    let html = '<option value="">Selecione a Modalidade</option>';
    modalidades.forEach(m => {
        html += `<option value="${m.id}">${m.nome}</option>`;
    });
    
    modalidadeSelect.innerHTML = html;
    modalidadeSelect.disabled = false;
    
    // Reseta divisão
    divisaoSelect.innerHTML = '<option value="">Selecione a Divisão</option>';
    divisaoSelect.disabled = true;
    
    // Restaurar estado salvo do rodeio (modalidade, divisão, stream)
    const state = PRELOADED_RODEIO_STATE[rodeioId];
    if (state) {
        if (state.stream_url) {
            el('streamUrl').value = state.stream_url;
        }
        if (state.modalidade_atual) {
            modalidadeSelect.value = state.modalidade_atual;
            if (modalidadeSelect.value) {
                selectedModalidade = state.modalidade_atual;
                // Carregar divisões e competidores da modalidade restaurada
                loadModalidadeCompetitors();
                // Se tinha divisão salva, restaurar após o dropdown ser populado
                if (state.divisao_atual) {
                    setTimeout(() => {
                        divisaoSelect.value = state.divisao_atual;
                        if (divisaoSelect.value === state.divisao_atual) {
                            selectedDivisao = state.divisao_atual;
                            fetchCompetitors();
                        }
                    }, 100);
                }
            }
        }
    }
    
    console.log(`✅ Rodeio selecionado`, state ? '(estado restaurado)' : '');

    saveEventConfig(true);
}

// ===== CARREGAR COMPETIDORES =====
function loadModalidadeCompetitors() {
    const modalidadeId = el('currentModalidade').value;
    const divisaoSelect = el('currentDivisao');
    
    if (!modalidadeId) {
        renderCompetitors([]);
        divisaoSelect.disabled = true;
        divisaoSelect.innerHTML = '<option value="">Selecione a Divisão</option>';
        return;
    }
    
    selectedModalidade = modalidadeId;
    saveEventConfig(true);
    
    // Verificar se modalidade tem divisões
    const rodeioId = el('activeRodeio').value;
    const modalidades = PRELOADED_MODALIDADES_BY_RODEIO[rodeioId] || [];
    const modalidade = modalidades.find(m => m.id == modalidadeId);
    
    if (modalidade && modalidade.tem_divisoes) {
        modalidadeRequiresDivisao = true;
        
        let html = '<option value="">Todas / sem divisão</option>';
        
        if (modalidade.divisoes && modalidade.divisoes.length > 0) {
            modalidade.divisoes.forEach(div => {
                html += `<option value="${div}">${div}</option>`;
            });
        }
        
        divisaoSelect.innerHTML = html;
        divisaoSelect.disabled = false;
        fetchCompetitors();
        return;
    } else {
        modalidadeRequiresDivisao = false;
        divisaoSelect.innerHTML = '<option value="">Sem divisão</option>';
        divisaoSelect.disabled = true;
    }
    
    fetchCompetitors();
}

function onDivisaoChanged() {
    selectedDivisao = el('currentDivisao').value;
    fetchCompetitors();
    saveEventConfig(true);
}

function fetchCompetitors() {
    const rodeioId = el('activeRodeio').value;
    const modalidadeId = el('currentModalidade').value;
    
    if (!rodeioId || !modalidadeId) return;
    
    const baseUrl = getBaseUrl();
    
    // Construir URL com parâmetros
    const params = new URLSearchParams({
        rodeio_id: rodeioId,
        modalidade_id: modalidadeId
    });
    
    if (selectedDivisao) {
        params.append('divisao', selectedDivisao);
    }
    
    fetch(`${baseUrl}/live-transmission/transmission-data?${params.toString()}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(res => res.json())
    .then(data => {
        console.log('📦 Dados recebidos da API:', data);
        if (data.success) {
            competitors = data.competitors || [];
            console.log(`✅ ${competitors.length} competidores carregados:`, competitors);
            renderCompetitors(competitors);
        }
    })
    .catch(err => {
        console.error('❌ Erro ao carregar competidores:', err);
    });
}

// ===== RENDERIZAR COMPETIDORES =====
function renderCompetitors(comps) {
    const container = el('competitorsList');
    
    console.log('🎨 Renderizando competidores:', comps);
    
    if (comps.length === 0) {
        container.innerHTML = `
            <div class="lt-empty-state">
                <i class="las la-users"></i>
                <p>Nenhum competidor encontrado</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    comps.forEach(comp => {
        console.log('👤 Competidor:', comp);
        const total = comp.stats?.pontuacao_total || 0;
        html += `
            <div class="lt-competitor-item" onclick="selectCompetitor(${comp.id}, '${comp.nome}', ${total})">
                <div class="lt-competitor-name">${comp.nome}</div>
                <div class="lt-competitor-score">
                    <span class="badge">${total}</span>
                    pts
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// ===== SELECIONAR COMPETIDOR =====
function selectCompetitor(id, name, total) {
    currentCompetitor = { id, name, total };
    
    // Destacar competidor na lista
    document.querySelectorAll('.lt-competitor-item').forEach(item => {
        item.classList.remove('active');
    });
    event.currentTarget.classList.add('active');
    
    // Atualizar header do painel
    el('selectedCompetitorInfo').style.display = 'flex';
    el('selectedCompetitorName').textContent = name;
    el('selectedCompetitorTotal').textContent = total;
    
    // Renderizar botões de pontuação
    renderScoringButtons();
    
    console.log(`✅ Competidor selecionado: ${name}`);
}

// ===== RENDERIZAR BOTÕES DE PONTUAÇÃO =====
function renderScoringButtons() {
    const container = el('scoringBody');
    
    let html = '';
    
    SCORE_BUTTONS.forEach(section => {
        html += `
            <div class="lt-scoring-section">
                <div class="lt-scoring-section-title">${section.section}</div>
                <div class="lt-scoring-grid">
        `;
        
        section.buttons.forEach(btn => {
            html += `
                <button class="lt-score-btn ${btn.variant}" 
                        onclick="addScore('${btn.action}', ${btn.points}, '${btn.label}')">
                    <span>${btn.label}</span>
                    <span class="badge">${btn.points > 0 ? '+' : ''}${btn.points}</span>
                </button>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    });
    
    // Custom Score
    html += `
        <div class="lt-custom-score">
            <div class="lt-custom-score-title">✏️ Pontuação Personalizada</div>
            <div class="lt-custom-score-inputs">
                <input type="number" id="customPoints" placeholder="Pontos">
                <input type="text" id="customAction" placeholder="Descrição da ação">
                <button class="lt-btn-custom-add" onclick="addCustomScore()">
                    <i class="las la-plus"></i> Adicionar
                </button>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

// ===== ADICIONAR PONTUAÇÃO =====
function addScore(action, points, label) {
    if (!currentCompetitor) {
        showToast('Selecione um competidor primeiro', 'warning');
        return;
    }
    
    // ✅ DETECTAR PONTUAÇÃO NEGATIVA: perguntar se quer desqualificar
    if (points < 0) {
        console.log('⚠️ Pontuação negativa detectada - abrindo modal de desqualificação');
        openDisqualifyModal(action, points, label);
        return;
    }
    
    // Pontuação positiva ou zero: salvar normalmente
    saveScore(action, points, label);
}

// ===== SALVAR PONTUAÇÃO (SEPARADA PARA REUTILIZAR) =====
function saveScore(action, points, label, skipDisqualifyCheck = false) {
    if (!currentCompetitor) return;
    
    const btn = event?.currentTarget;
    if (btn) btn.classList.add('saving');
    
    const baseUrl = getBaseUrl();
    
    fetch(`${baseUrl}/live-transmission/add-score`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            competitor_id: currentCompetitor.id,
            rodeio_id: selectedRodeio,
            modalidade_id: selectedModalidade,
            divisao: el('currentDivisao')?.value || '',
            action: action,
            points: points,
            description: label
        })
    })
    .then(res => res.json())
    .then(data => {
        if (btn) btn.classList.remove('saving');
        
        if (data.success) {
            if (btn) {
                btn.classList.add('success-flash');
                setTimeout(() => btn.classList.remove('success-flash'), 500);
            }
            
            // Atualizar total do competidor
            const newTotal = data.new_score || data.new_total || (currentCompetitor.total + points);
            currentCompetitor.total = newTotal;
            el('selectedCompetitorTotal').textContent = newTotal;
            
            // Atualizar na lista
            const compItem = document.querySelector(`.lt-competitor-item.active .badge`);
            if (compItem) compItem.textContent = newTotal;
            
            // Se competidor foi marcado como indisponível (erro), remover da lista
            if (data.competitor_marked_out) {
                const compId = data.competitor_id;
                const compEl = document.querySelector(`.lt-competitor-item[onclick*="${compId}"]`);
                if (compEl) {
                    compEl.style.transition = 'opacity 0.3s, transform 0.3s';
                    compEl.style.opacity = '0';
                    compEl.style.transform = 'translateX(-20px)';
                    setTimeout(() => compEl.remove(), 350);
                }
                // Limpar seleção
                currentCompetitor = null;
                const scoringPanel = el('scoringPanel');
                if (scoringPanel) scoringPanel.style.display = 'none';
                showToast(`${label} → Competidor removido da lista`, 'warning');
            }
            
            console.log(`✅ ${label} → ${currentCompetitor?.name || 'Competidor'} (${points > 0 ? '+' : ''}${points} pts) | Total: ${newTotal}`);
        } else {
            console.log(`❌ Erro: ${data.message || 'Falha ao salvar pontuação'}`);
        }
    })
    .catch(err => {
        if (btn) btn.classList.remove('saving');
        console.error('❌ Erro ao salvar pontuação:', err);
    });
}

// ===== PONTUAÇÃO PERSONALIZADA =====
function addCustomScore() {
    const points = parseInt(el('customPoints').value);
    const description = el('customAction').value.trim();
    
    if (!points || !description) {
        showToast('Preencha pontos e descrição', 'warning');
        return;
    }
    
    addScore('custom', points, description);
    
    // Limpar campos
    el('customPoints').value = '';
    el('customAction').value = '';
}

// ===== MODAL DE DESQUALIFICAÇÃO =====
function openDisqualifyModal(action, points, label) {
    const modal = el('disqualifyModal');
    const competitorName = el('disqualifyCompetitorName');
    const errorLabel = el('disqualifyErrorLabel');
    const errorPoints = el('disqualifyErrorPoints');
    
    if (!currentCompetitor) return;
    
    // Armazenar dados da desqualificação pendente
    pendingDisqualification = {
        competitor_id: currentCompetitor.id,
        competitor_name: currentCompetitor.name,
        action: action,
        points: points,
        label: label,
        rodeio_id: selectedRodeio,
        modalidade_id: selectedModalidade
    };
    
    // Atualizar informações no modal
    if (competitorName) {
        competitorName.textContent = currentCompetitor.name;
    }
    if (errorLabel) {
        errorLabel.textContent = label;
    }
    if (errorPoints) {
        errorPoints.textContent = label;
    }
    
    // Mostrar modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    console.log('⚠️ Modal de desqualificação aberto:', {
        competidor: currentCompetitor.name,
        erro: label,
        pontos: points
    });
}

function closeDisqualifyModal() {
    const modal = el('disqualifyModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
    pendingDisqualification = null;
    
    // Resetar estado do botão de confirmação
    const confirmBtn = modal.querySelector('.lt-btn-danger');
    if (confirmBtn) {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '✅ Sim, Desqualificar';
    }
    
    console.log('✅ Modal de desqualificação fechado');
}

// ===== FUNÇÃO NOVA: ADICIONAR PONTUAÇÃO SEM DESQUALIFICAR =====
function addScoreOnly() {
    if (!pendingDisqualification) {
        console.error('❌ Nenhuma desqualificação pendente');
        return;
    }
    
    console.log('📝 Salvando apenas pontuação negativa (sem desqualificar):', pendingDisqualification);
    
    const data = pendingDisqualification;
    
    // Fechar modal
    closeDisqualifyModal();
    
    // Salvar pontuação negativa normalmente (sem passar event)
    const baseUrl = getBaseUrl();
    
    fetch(`${baseUrl}/live-transmission/add-score`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            competitor_id: currentCompetitor.id,
            rodeio_id: selectedRodeio,
            modalidade_id: selectedModalidade,
            divisao: el('currentDivisao')?.value || '',
            action: data.action,
            points: data.points,
            description: data.label,
            skip_disqualify: true
        })
    })
    .then(res => res.json())
    .then(response => {
        if (response.success) {
            // Atualizar total do competidor
            const newTotal = response.new_score || response.new_total || (currentCompetitor.total + data.points);
            currentCompetitor.total = newTotal;
            el('selectedCompetitorTotal').textContent = newTotal;
            
            // Atualizar na lista
            const compItem = document.querySelector(`.lt-competitor-item.active .badge`);
            if (compItem) compItem.textContent = newTotal;
            
            // Se competidor foi marcado como indisponível (erro), remover da lista
            if (response.competitor_marked_out) {
                const compId = response.competitor_id;
                const compEl = document.querySelector(`.lt-competitor-item[onclick*="${compId}"]`);
                if (compEl) {
                    compEl.style.transition = 'opacity 0.3s, transform 0.3s';
                    compEl.style.opacity = '0';
                    compEl.style.transform = 'translateX(-20px)';
                    setTimeout(() => compEl.remove(), 350);
                }
                currentCompetitor = null;
                const scoringPanel = el('scoringPanel');
                if (scoringPanel) scoringPanel.style.display = 'none';
                showToast(`✅ Pontuação salva: ${data.points} pts → Competidor removido`, 'warning');
            } else {
                showToast(`✅ Pontuação salva: ${data.points} pts (Total: ${newTotal})`, 'success');
            }
            console.log(`✅ ${data.label} → ${currentCompetitor?.name || 'Competidor'} (${data.points} pts) | Total: ${newTotal}`);
        } else {
            showToast(`❌ Erro: ${response.message || 'Falha ao salvar pontuação'}`, 'error');
            console.log(`❌ Erro: ${response.message || 'Falha ao salvar pontuação'}`);
        }
    })
    .catch(err => {
        console.error('❌ Erro ao salvar pontuação:', err);
        showToast('❌ Erro ao salvar pontuação', 'error');
    });
}

function confirmDisqualify() {
    if (!pendingDisqualification) {
        console.error('❌ Nenhuma desqualificação pendente');
        return;
    }
    
    console.log('🔥 Iniciando processo de desqualificação:', pendingDisqualification);
    
    const baseUrl = getBaseUrl();
    const modal = el('disqualifyModal');
    
    // Desabilitar botão durante processamento
    const confirmBtn = modal.querySelector('.lt-btn-danger');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="lt-loading"></span> Processando...';
    }
    
    // Enviar requisição de desqualificação
    fetch(`${baseUrl}/live-transmission/disqualify-competitor`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(pendingDisqualification)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Desqualificação processada com sucesso:', data);
            showToast(`${pendingDisqualification.competitor_name} foi desqualificado!`, 'success');
            
            // Armazenar ID antes de limpar o objeto global
            const disqualifiedId = pendingDisqualification.competitor_id;
            
            // Fechar modal
            closeDisqualifyModal();
            
            // Remover competidor da lista (UI)
            const compItem = document.querySelector(`.lt-competitor-item[onclick*="${disqualifiedId}"]`);
            if (compItem) {
                // Remover do DOM visualmente
                compItem.style.display = 'none'; 
                // Também remover do array global de competidores para não aparecer em buscas
                competitors = competitors.filter(c => c.id != disqualifiedId);
                
                // Se era o selecionado, limpar seleção
                if (currentCompetitor && currentCompetitor.id == disqualifiedId) {
                    currentCompetitor = null;
                    el('selectedCompetitorName').textContent = '-';
                    el('selectedCompetitorTotal').textContent = '0';
                    el('selectedCompetitorInfo').style.display = 'none';
                    el('scoringBody').innerHTML = `
                        <div class="lt-empty-state">
                            <i class="las la-hand-pointer"></i>
                            <p>Selecione um competidor para pontuar</p>
                        </div>
                    `;
                }
            }
            
            // Limpar seleção atual
            currentCompetitor = null;
            el('selectedCompetitorName').textContent = 'Nenhum';
            el('selectedCompetitorTotal').textContent = '0';
            
            // Exibir resumo
            if (data.affected) {
                console.log('📊 Resumo da desqualificação:');
                console.log('  - X1 rooms finalizadas:', data.affected.x1_rooms_completed || 0);
                console.log('  - Vencedores declarados:', data.affected.winners || 0);
                console.log('  - Pagamentos processados:', data.affected.payments_processed || 0);
                console.log('  - Usuários notificados:', data.affected.users_notified || 0);
                
                const summary = [];
                if (data.affected.x1_rooms_completed > 0) {
                    summary.push(`${data.affected.x1_rooms_completed} sala(s) X1 finalizada(s)`);
                }
                if (data.affected.winners > 0) {
                    summary.push(`${data.affected.winners} vencedor(es) declarado(s)`);
                }
                
                if (summary.length > 0) {
                    showToast(`✅ ${summary.join(' | ')}`, 'success');
                }
            }
            
            // Recarregar competidores
            setTimeout(() => {
                fetchCompetitors();
            }, 2000);
            
        } else {
            console.error('❌ Erro ao desqualificar:', data.message);
            showToast(data.message || 'Erro ao desqualificar competidor', 'error');
            
            // Reabilitar botão
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '✅ Sim, Desqualificar';
            }
        }
    })
    .catch(err => {
        console.error('❌ Erro na requisição de desqualificação:', err);
        showToast('Erro ao processar desqualificação', 'error');
        
        // Reabilitar botão
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '✅ Sim, Desqualificar';
        }
    });
}

// ===== DESFAZER ÚLTIMA PONTUAÇÃO =====
function undoLastScore() {
    if (!selectedModalidade || !selectedRodeio) {
        showToast('Selecione um rodeio e modalidade primeiro.', 'warning');
        return;
    }

    if (!confirm('Deseja desfazer a última pontuação realizada?')) {
        return;
    }

    const baseUrl = getBaseUrl();
    
    fetch(`${baseUrl}/live-transmission/undo-last-score`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            rodeio_id: selectedRodeio,
            modalidade_id: selectedModalidade,
            competitor_id: currentCompetitor ? currentCompetitor.id : null 
        })
    })
    .then(res => {
        if (!res.ok) {
            return res.json().then(d => { throw d; });
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            const compName = data.competitor_name || 'Competidor';
            showToast(`↩️ ${compName}: desfeito ${data.undone_action} (${data.undone_points} pts)`, 'success');
            
            // Atualizar UI do competidor afetado
            const affectedId = data.competitor_id;
            if (affectedId) {
                const comp = competitors.find(c => c.id == affectedId);
                if (comp) {
                    comp.stats = comp.stats || {};
                    comp.stats.pontuacao_total = data.new_total;
                    // Se for o selecionado, atualizar painel
                    if (currentCompetitor && currentCompetitor.id == affectedId) {
                        currentCompetitor.total = data.new_total;
                        const totalEl = el('selectedCompetitorTotal');
                        if (totalEl) totalEl.textContent = data.new_total;
                    }
                    // Atualizar badge na lista
                    const badge = document.querySelector(`.lt-competitor-item[onclick*="${affectedId}"] .badge`);
                    if (badge) badge.textContent = data.new_total;
                }
            }
        } else {
            showToast(data.message || 'Erro ao desfazer ação', 'error');
        }
    })
    .catch(err => {
        console.error('❌ Erro ao desfazer:', err);
        const msg = (err && err.message) ? err.message : 'Erro ao desfazer última pontuação';
        showToast(msg, 'error');
    });
}

// ===== VERIFICAR SE TEM CLASSIFICATÓRIA NÃO FINALIZADA =====
function checkUnfinalizedClassificatoria(rodeioId, modalidadeId) {
    if (!rodeioId || !modalidadeId) return;
    
    const baseUrl = getBaseUrl();
}

// ===== SALVAR CONFIGURAÇÃO DO EVENTO =====
function saveEventConfig(silent = false) {
    const rodeioId = el('activeRodeio').value;
    const modalidadeId = el('currentModalidade').value;
    const divisao = el('currentDivisao').value;
    const streamUrl = el('streamUrl').value;
    
    if (!rodeioId) {
        console.log('❌ Selecione um rodeio primeiro');
        if (!silent) showToast('Selecione um rodeio', 'warning');
        return;
    }
    
    const baseUrl = getBaseUrl();
    console.log('🔍 Base URL:', baseUrl);
    console.log('🔍 Full URLs que serão chamadas:');
    console.log('  - Stream:', `${baseUrl}/live-transmission/stream-url`);
    console.log('  - Modalidade:', `${baseUrl}/live-transmission/save-modalidade`);
    
    const triggerEvent = typeof event !== 'undefined' ? event : null;
    const btn = triggerEvent?.currentTarget || null;
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="lt-loading"></span> Salvando...';
    }
    
    let savePromises = [];
    
    // Salvar URL do stream
    if (streamUrl) {
        console.log('📡 Salvando URL do stream...');
        const streamPromise = fetch(`${baseUrl}/live-transmission/stream-url`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                rodeio_id: rodeioId,
                stream_url: streamUrl
            })
        })
        .then(res => {
            console.log('📡 Stream Response Status:', res.status);
            if (!res.ok) {
                return res.text().then(text => {
                    console.error('📡 Stream Response Error:', text);
                    throw new Error(`HTTP ${res.status}: ${text}`);
                });
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                console.log('✅ URL do stream salva');
            }
            return data;
        })
        .catch(err => {
            console.error('❌ Erro ao salvar stream URL:', err);
            return { success: false };
        });
        savePromises.push(streamPromise);
    }
    
    // Salvar modalidade atual
    if (modalidadeId) {
        console.log('🎯 Salvando modalidade...');
        const modalidadePromise = fetch(`${baseUrl}/live-transmission/save-modalidade`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                rodeio_id: rodeioId,
                modalidade_id: modalidadeId,
                divisao: divisao
            })
        })
        .then(res => {
            console.log('🎯 Modalidade Response Status:', res.status);
            if (!res.ok) {
                return res.text().then(text => {
                    console.error('🎯 Modalidade Response Error:', text);
                    throw new Error(`HTTP ${res.status}: ${text}`);
                });
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                console.log('✅ Modalidade salva');
            }
            return data;
        })
        .catch(err => {
            console.error('❌ Erro ao salvar modalidade:', err);
            return { success: false };
        });
        savePromises.push(modalidadePromise);
    }
    
    // Aguardar todas as promises
    Promise.all(savePromises)
        .then(results => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="las la-save"></i> Salvar';
            }
            
            const allSuccess = results.every(r => r && r.success);
            if (allSuccess) {
                console.log('🎉 CONFIGURAÇÃO SALVA COM SUCESSO!');
                if (!silent) showToast('✅ Live atualizada no frontend!', 'success');
                
                // Disparar evento customizado para notificar o frontend
                if (typeof window !== 'undefined') {
                    const event = new CustomEvent('liveConfigUpdated', {
                        detail: {
                            rodeioId,
                            modalidadeId,
                            divisao,
                            streamUrl
                        }
                    });
                    window.dispatchEvent(event);
                }
            } else {
                console.log('⚠️ Algumas configurações não foram salvas');
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="las la-save"></i> Salvar';
            }
            console.error('❌ Erro geral:', err);
        });
}

// ===== BUSCA DE COMPETIDORES =====
el('competitorSearch').addEventListener('input', function() {
    const search = this.value.toLowerCase();
    const items = document.querySelectorAll('.lt-competitor-item');
    
    items.forEach(item => {
        const name = item.querySelector('.lt-competitor-name').textContent.toLowerCase();
        item.style.display = name.includes(search) ? 'flex' : 'none';
    });
});

// ===== POPOUT DRAGGABLE =====
let popoutState = {
    isDragging: false,
    isResizing: false,
    currentX: 0,
    currentY: 0,
    initialX: 0,
    initialY: 0,
    xOffset: 0,
    yOffset: 0,
    resizeHandle: null
};

function togglePopout() {
    const popout = el('popoutContainer');
    
    if (popout.classList.contains('closed')) {
        popout.classList.remove('closed');
        console.log('✅ Painel de pontuação aberto');
    } else {
        popout.classList.add('closed');
        console.log('❌ Painel de pontuação fechado');
    }
}

function closePopout() {
    const popout = el('popoutContainer');
    popout.classList.add('closed');
    console.log('❌ Painel de pontuação fechado');
}

function minimizePopout() {
    // Minimizar agora fecha também (comportamento igual ao fechar)
    closePopout();
}

function maximizePopout() {
    const popout = el('popoutContainer');
    popout.classList.toggle('maximized');
    console.log('🔄 Painel maximizado/restaurado');
}

// Drag functionality
const popoutHeader = el('popoutHeader');
const popoutContainer = el('popoutContainer');

popoutHeader.addEventListener('mousedown', dragStart);
document.addEventListener('mousemove', drag);
document.addEventListener('mouseup', dragEnd);

function dragStart(e) {
    if (e.target.closest('.lt-popout-btn')) return; // Não arrastar ao clicar nos botões
    if (popoutContainer.classList.contains('maximized')) return; // Não arrastar quando maximizado
    
    popoutState.isDragging = true;
    popoutHeader.style.cursor = 'grabbing';
    
    const rect = popoutContainer.getBoundingClientRect();
    popoutState.initialX = e.clientX - rect.left;
    popoutState.initialY = e.clientY - rect.top;
}

function drag(e) {
    if (!popoutState.isDragging) return;
    
    e.preventDefault();
    
    popoutState.currentX = e.clientX - popoutState.initialX;
    popoutState.currentY = e.clientY - popoutState.initialY;
    
    // Limitar às bordas da janela
    const maxX = window.innerWidth - popoutContainer.offsetWidth;
    const maxY = window.innerHeight - popoutContainer.offsetHeight;
    
    popoutState.currentX = Math.max(0, Math.min(popoutState.currentX, maxX));
    popoutState.currentY = Math.max(0, Math.min(popoutState.currentY, maxY));
    
    popoutContainer.style.left = `${popoutState.currentX}px`;
    popoutContainer.style.top = `${popoutState.currentY}px`;
    popoutContainer.style.transform = 'none';
}

function dragEnd() {
    if (popoutState.isDragging) {
        popoutState.isDragging = false;
        popoutHeader.style.cursor = 'move';
    }
}

// Resize functionality
const resizeHandles = document.querySelectorAll('.lt-popout-resize');
resizeHandles.forEach(handle => {
    handle.addEventListener('mousedown', resizeStart);
});

function resizeStart(e) {
    if (popoutContainer.classList.contains('maximized')) return;
    
    e.preventDefault();
    popoutState.isResizing = true;
    popoutState.resizeHandle = e.target;
    popoutState.initialX = e.clientX;
    popoutState.initialY = e.clientY;
    
    const rect = popoutContainer.getBoundingClientRect();
    popoutState.initialWidth = rect.width;
    popoutState.initialHeight = rect.height;
    popoutState.initialTop = rect.top;
    popoutState.initialLeft = rect.left;
    
    document.addEventListener('mousemove', resize);
    document.addEventListener('mouseup', resizeEnd);
}

function resize(e) {
    if (!popoutState.isResizing) return;
    
    e.preventDefault();
    
    const deltaX = e.clientX - popoutState.initialX;
    const deltaY = e.clientY - popoutState.initialY;
    
    const handle = popoutState.resizeHandle;
    const minWidth = 600;
    const minHeight = 400;
    
    if (handle.classList.contains('lt-popout-resize-e')) {
        const newWidth = Math.max(minWidth, popoutState.initialWidth + deltaX);
        popoutContainer.style.width = `${newWidth}px`;
    }
    
    if (handle.classList.contains('lt-popout-resize-w')) {
        const newWidth = Math.max(minWidth, popoutState.initialWidth - deltaX);
        if (newWidth >= minWidth) {
            popoutContainer.style.width = `${newWidth}px`;
            popoutContainer.style.left = `${popoutState.initialLeft + deltaX}px`;
        }
    }
    
    if (handle.classList.contains('lt-popout-resize-s')) {
        const newHeight = Math.max(minHeight, popoutState.initialHeight + deltaY);
        popoutContainer.style.height = `${newHeight}px`;
    }
    
    if (handle.classList.contains('lt-popout-resize-n')) {
        const newHeight = Math.max(minHeight, popoutState.initialHeight - deltaY);
        if (newHeight >= minHeight) {
            popoutContainer.style.height = `${newHeight}px`;
            popoutContainer.style.top = `${popoutState.initialTop + deltaY}px`;
        }
    }
    
    // Cantos (combinações)
    if (handle.classList.contains('lt-popout-resize-se')) {
        const newWidth = Math.max(minWidth, popoutState.initialWidth + deltaX);
        const newHeight = Math.max(minHeight, popoutState.initialHeight + deltaY);
        popoutContainer.style.width = `${newWidth}px`;
        popoutContainer.style.height = `${newHeight}px`;
    }
    
    if (handle.classList.contains('lt-popout-resize-sw')) {
        const newWidth = Math.max(minWidth, popoutState.initialWidth - deltaX);
        const newHeight = Math.max(minHeight, popoutState.initialHeight + deltaY);
        if (newWidth >= minWidth) {
            popoutContainer.style.width = `${newWidth}px`;
            popoutContainer.style.left = `${popoutState.initialLeft + deltaX}px`;
        }
        popoutContainer.style.height = `${newHeight}px`;
    }
    
    if (handle.classList.contains('lt-popout-resize-ne')) {
        const newWidth = Math.max(minWidth, popoutState.initialWidth + deltaX);
        const newHeight = Math.max(minHeight, popoutState.initialHeight - deltaY);
        popoutContainer.style.width = `${newWidth}px`;
        if (newHeight >= minHeight) {
            popoutContainer.style.height = `${newHeight}px`;
            popoutContainer.style.top = `${popoutState.initialTop + deltaY}px`;
        }
    }
    
    if (handle.classList.contains('lt-popout-resize-nw')) {
        const newWidth = Math.max(minWidth, popoutState.initialWidth - deltaX);
        const newHeight = Math.max(minHeight, popoutState.initialHeight - deltaY);
        if (newWidth >= minWidth) {
            popoutContainer.style.width = `${newWidth}px`;
            popoutContainer.style.left = `${popoutState.initialLeft + deltaX}px`;
        }
        if (newHeight >= minHeight) {
            popoutContainer.style.height = `${newHeight}px`;
            popoutContainer.style.top = `${popoutState.initialTop + deltaY}px`;
        }
    }
}

function resizeEnd() {
    popoutState.isResizing = false;
    popoutState.resizeHandle = null;
    document.removeEventListener('mousemove', resize);
    document.removeEventListener('mouseup', resizeEnd);
}

// ===== INICIALIZAÇÃO =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Sistema de transmissão ao vivo inicializado');
    
    // Popout inicia fechado
    const popout = el('popoutContainer');
    popout.classList.add('closed');
    
    // Se tiver rodeioId e selectedCompetitorIds na URL (vindos de outra página)
    const urlParams = new URLSearchParams(window.location.search);
    const rodeioId = urlParams.get('rodeio_id');
    
    if (rodeioId) {
        el('activeRodeio').value = rodeioId;
        loadRodeioData();
    }
});
</script>

@endsection

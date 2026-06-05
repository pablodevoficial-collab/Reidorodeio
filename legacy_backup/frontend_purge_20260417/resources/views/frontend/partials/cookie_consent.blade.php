{{-- Cookie Consent Banner --}}
<div id="cookieConsentBanner" style="display: none;">
    <div class="cookie-consent-overlay"></div>
    <div class="cookie-consent-modal">
        <div class="cookie-consent-header">
            <i class="fas fa-cookie-bite"></i>
            <h3>Cookies & Privacidade</h3>
        </div>
        
        <div class="cookie-consent-body">
            <p>
                Utilizamos cookies para melhorar sua experiência, personalizar conteúdo e analisar o tráfego do site. 
                Também armazenamos informações de indicação (afiliados) para garantir que você receba os benefícios adequados.
            </p>
            <p class="cookie-consent-small">
                Ao continuar navegando, você concorda com nossa 
                <a href="/politica-privacidade" target="_blank">Política de Privacidade</a> e 
                <a href="/termos-uso" target="_blank">Termos de Uso</a>.
            </p>
        </div>
        
        <div class="cookie-consent-actions">
            <button type="button" class="cookie-consent-btn cookie-consent-btn-accept" onclick="acceptCookies()">
                <i class="fas fa-check"></i> Aceitar e Continuar
            </button>
            <button type="button" class="cookie-consent-btn cookie-consent-btn-essentials" onclick="essentialCookiesOnly()">
                <i class="fas fa-shield-alt"></i> Apenas Essenciais
            </button>
        </div>
    </div>
</div>

<style>
.cookie-consent-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
    z-index: 999998;
    animation: fadeIn 0.3s ease;
}

.cookie-consent-modal {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    max-width: 600px;
    width: calc(100% - 40px);
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.98), rgba(30, 41, 59, 0.98));
    border: 1px solid rgba(249, 115, 22, 0.3);
    border-radius: 16px;
    box-shadow: 
        0 20px 60px rgba(0, 0, 0, 0.6),
        0 0 40px rgba(249, 115, 22, 0.15);
    z-index: 999999;
    padding: 24px;
    animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateX(-50%) translateY(60px);
    }
    to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
}

.cookie-consent-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.cookie-consent-header i {
    font-size: 28px;
    color: #f97316;
}

.cookie-consent-header h3 {
    margin: 0;
    font-family: var(--rr-font-display);
    font-size: 20px;
    font-weight: 700;
    color: #fff;
}

.cookie-consent-body {
    margin-bottom: 20px;
}

.cookie-consent-body p {
    margin: 0 0 12px 0;
    color: #cbd5e1;
    font-size: 14px;
    line-height: 1.6;
}

.cookie-consent-small {
    font-size: 12px !important;
    color: #94a3b8 !important;
}

.cookie-consent-small a {
    color: #f97316;
    text-decoration: underline;
}

.cookie-consent-small a:hover {
    color: #fb923c;
}

.cookie-consent-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.cookie-consent-btn {
    flex: 1;
    min-width: 180px;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.cookie-consent-btn-accept {
    background: linear-gradient(135deg, #f97316, #ea580c);
    color: #fff;
}

.cookie-consent-btn-accept:hover {
    background: linear-gradient(135deg, #fb923c, #f97316);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(249, 115, 22, 0.4);
}

.cookie-consent-btn-essentials {
    background: rgba(255, 255, 255, 0.1);
    color: #e2e8f0;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.cookie-consent-btn-essentials:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.3);
}

@media (max-width: 640px) {
    .cookie-consent-modal {
        bottom: 10px;
        width: calc(100% - 20px);
        padding: 20px;
    }
    
    .cookie-consent-actions {
        flex-direction: column;
    }
    
    .cookie-consent-btn {
        min-width: 100%;
    }
}

/* Hide when dismissed */
#cookieConsentBanner.dismissed {
    display: none !important;
}
</style>

<script>
(function() {
    'use strict';
    
    const COOKIE_CONSENT_KEY = 'cookie_consent_accepted';
    const COOKIE_CONSENT_EXPIRY_DAYS = 365; // 1 ano
    
    // Detectar se está rodando dentro do app (WebView Flutter)
    function isWebView() {
        var ua = navigator.userAgent || '';
        return ua.indexOf('ReiDoRodeioApp') !== -1;
    }

    // Verificar se já aceitou cookies
    function hasConsent() {
        return localStorage.getItem(COOKIE_CONSENT_KEY) === 'true';
    }
    
    // Mostrar banner se não tiver consentimento (apenas na versão web)
    function showBannerIfNeeded() {
        if (isWebView()) return; // Não exibir no app
        if (!hasConsent()) {
            const banner = document.getElementById('cookieConsentBanner');
            if (banner) {
                banner.style.display = 'block';
            }
        }
    }
    
    // Aceitar cookies
    window.acceptCookies = function() {
        localStorage.setItem(COOKIE_CONSENT_KEY, 'true');
        
        const banner = document.getElementById('cookieConsentBanner');
        if (banner) {
            banner.classList.add('dismissed');
        }
        
        console.log('✅ Cookies aceitos pelo usuário');
        
        // Aqui você pode ativar analytics, pixels, etc.
        // Ex: initGoogleAnalytics();
    };
    
    // Apenas cookies essenciais
    window.essentialCookiesOnly = function() {
        localStorage.setItem(COOKIE_CONSENT_KEY, 'essentials');
        
        const banner = document.getElementById('cookieConsentBanner');
        if (banner) {
            banner.classList.add('dismissed');
        }
        
        console.log('⚠️ Apenas cookies essenciais');
        
        // Não ativar analytics/pixels
    };
    
    // Mostrar banner quando página carregar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', showBannerIfNeeded);
    } else {
        showBannerIfNeeded();
    }
})();
</script>

/**
 * Cookie Consent Manager - GDPR Compliant
 * Studio Legale BCS - studiodigitale.eu
 */

(function () {
    'use strict';

    const COOKIE_CONSENT_KEY = 'bcs_cookie_consent';
    const CONSENT_EXPIRY_DAYS = 365;

    // Check if consent was already given
    function hasConsent() {
        return localStorage.getItem(COOKIE_CONSENT_KEY) === 'accepted';
    }

    function hasDeclined() {
        return localStorage.getItem(COOKIE_CONSENT_KEY) === 'declined';
    }

    function hasResponded() {
        return hasConsent() || hasDeclined();
    }

    // Save consent choice
    function saveConsent(accepted) {
        localStorage.setItem(COOKIE_CONSENT_KEY, accepted ? 'accepted' : 'declined');
    }

    // Load analytics scripts only after consent
    function loadAnalytics() {
        // Google Analytics (gtag.js)
        if (window.GA_MEASUREMENT_ID) {
            const script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.googletagmanager.com/gtag/js?id=' + window.GA_MEASUREMENT_ID;
            document.head.appendChild(script);

            window.dataLayer = window.dataLayer || [];
            function gtag() { dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', window.GA_MEASUREMENT_ID);
        }
    }

    // Create and show the banner
    function showBanner() {
        if (hasResponded()) return;

        const banner = document.createElement('div');
        banner.id = 'cookie-consent-banner';
        banner.innerHTML = `
            <div class="cookie-banner-content">
                <div class="cookie-text">
                    <p><strong>🍪 Questo sito utilizza i cookie</strong></p>
                    <p>Utilizziamo cookie tecnici e, previo tuo consenso, cookie di profilazione per analizzare il traffico e migliorare la tua esperienza. 
                    <a href="/cookie-policy.html" class="cookie-link">Cookie Policy</a> | 
                    <a href="/privacy-policy.html" class="cookie-link">Privacy Policy</a></p>
                </div>
                <div class="cookie-buttons">
                    <button id="cookie-accept" class="cookie-btn cookie-btn-accept">Accetta tutti</button>
                    <button id="cookie-decline" class="cookie-btn cookie-btn-decline">Solo necessari</button>
                </div>
            </div>
        `;

        // Inject styles
        const style = document.createElement('style');
        style.textContent = `
            #cookie-consent-banner {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: linear-gradient(135deg, #111714 0%, #1a2520 100%);
                border-top: 2px solid #13EC80;
                padding: 20px;
                z-index: 99999;
                box-shadow: 0 -4px 20px rgba(0,0,0,0.5);
                animation: slideUp 0.4s ease-out;
            }
            @keyframes slideUp {
                from { transform: translateY(100%); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            .cookie-banner-content {
                max-width: 1200px;
                margin: 0 auto;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
            }
            .cookie-text {
                flex: 1;
                min-width: 280px;
                color: #e5e7eb;
                font-family: 'Noto Sans', sans-serif;
                font-size: 14px;
                line-height: 1.5;
            }
            .cookie-text p { margin: 0 0 8px 0; }
            .cookie-text p:last-child { margin-bottom: 0; }
            .cookie-link {
                color: #13EC80;
                text-decoration: none;
                transition: color 0.2s;
            }
            .cookie-link:hover { color: #4fffac; text-decoration: underline; }
            .cookie-buttons {
                display: flex;
                gap: 12px;
                flex-shrink: 0;
            }
            .cookie-btn {
                padding: 12px 24px;
                border: none;
                border-radius: 8px;
                font-family: 'Space Grotesk', sans-serif;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            .cookie-btn-accept {
                background: #13EC80;
                color: #111714;
            }
            .cookie-btn-accept:hover {
                background: #4fffac;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(19, 236, 128, 0.4);
            }
            .cookie-btn-decline {
                background: transparent;
                color: #e5e7eb;
                border: 1px solid #3a6b54;
            }
            .cookie-btn-decline:hover {
                background: #3a6b54;
                color: white;
            }
            @media (max-width: 640px) {
                #cookie-consent-banner { padding: 16px; }
                .cookie-banner-content { flex-direction: column; text-align: center; }
                .cookie-buttons { width: 100%; justify-content: center; }
                .cookie-btn { flex: 1; max-width: 160px; }
            }
        `;
        document.head.appendChild(style);
        document.body.appendChild(banner);

        // Event listeners
        document.getElementById('cookie-accept').addEventListener('click', function () {
            saveConsent(true);
            hideBanner();
            loadAnalytics();
        });

        document.getElementById('cookie-decline').addEventListener('click', function () {
            saveConsent(false);
            hideBanner();
        });
    }

    function hideBanner() {
        const banner = document.getElementById('cookie-consent-banner');
        if (banner) {
            banner.style.animation = 'slideDown 0.3s ease-in forwards';
            banner.style.setProperty('--slide-down', 'translateY(100%)');
            const style = document.createElement('style');
            style.textContent = '@keyframes slideDown { to { transform: translateY(100%); opacity: 0; } }';
            document.head.appendChild(style);
            setTimeout(() => banner.remove(), 300);
        }
    }

    // Initialize on DOM ready
    function init() {
        if (hasConsent()) {
            // User previously accepted, load analytics
            loadAnalytics();
        } else if (!hasResponded()) {
            // Show banner after a short delay
            setTimeout(showBanner, 500);
        }
        // If declined, do nothing (only technical cookies)
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose API for manual control
    window.CookieConsent = {
        hasConsent: hasConsent,
        hasDeclined: hasDeclined,
        reset: function () {
            localStorage.removeItem(COOKIE_CONSENT_KEY);
            location.reload();
        }
    };
})();

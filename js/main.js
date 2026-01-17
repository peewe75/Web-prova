
const init = () => {
    setupSearch();
    setupBackToTop();
    setupMobileMenu();
    setupCookieConsent(); // Initialize Cookie Consent
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

/**
 * Mobile Menu Toggle
 */
function setupMobileMenu() {
    const menuBtn = document.querySelector('button.lg\\:hidden'); // Select the hamburger button
    const header = document.querySelector('header');

    if (!menuBtn || !header) return;

    // Create Mobile Menu Container
    const mobileMenu = document.createElement('div');
    mobileMenu.className = 'fixed inset-0 z-40 bg-background-dark/95 backdrop-blur-xl transform transition-transform duration-300 translate-x-full flex flex-col items-center justify-center gap-8 lg:hidden';
    mobileMenu.innerHTML = `
        <button class="absolute top-6 right-6 text-white p-2">
            <span class="material-symbols-outlined text-3xl">close</span>
        </button>
        <nav class="flex flex-col items-center gap-6 text-xl font-bold"></nav>
    `;
    document.body.appendChild(mobileMenu);

    const closeBtn = mobileMenu.querySelector('button');
    const mobileNav = mobileMenu.querySelector('nav');

    // Clone Links
    const desktopNav = header.querySelector('nav'); // Select the nav inside header (desktop)
    if (desktopNav) {
        const links = desktopNav.querySelectorAll('a');
        links.forEach(link => {
            const clone = link.cloneNode(true);
            clone.className = 'text-white hover:text-primary transition-colors';
            mobileNav.appendChild(clone);
        });
    }

    // Clone "Area Riservata" Button (it's usually outside the nav)
    // Login button removed per decommissioning request

    // Toggle Logic
    function toggleMenu() {
        const isOpen = !mobileMenu.classList.contains('translate-x-full');
        if (isOpen) {
            mobileMenu.classList.add('translate-x-full');
            document.body.style.overflow = '';
        } else {
            mobileMenu.classList.remove('translate-x-full');
            document.body.style.overflow = 'hidden';
        }
    }

    menuBtn.addEventListener('click', toggleMenu);
    closeBtn.addEventListener('click', toggleMenu);

    // Close on link click
    mobileNav.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', toggleMenu);
    });
}

/**
 * Internal Search for Blog Page
 */
function setupSearch() {
    const searchInput = document.getElementById('searchInput');
    const articles = document.querySelectorAll('.blog-article');
    const filterButtons = document.querySelectorAll('.filter-btn');

    if (!searchInput || !articles.length) return; // Exit if not on blog page or no articles

    let currentCategory = 'all';

    // Search Input Listener
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        filterArticles(searchTerm, currentCategory);
    });

    // Filter Buttons Listener
    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active class from all
            filterButtons.forEach(b => {
                b.classList.remove('bg-primary', 'text-background-dark');
                b.classList.add('bg-surface-dark', 'text-white', 'border-secondary');
            });
            // Add active class to clicked
            btn.classList.remove('bg-surface-dark', 'text-white', 'border-secondary');
            btn.classList.add('bg-primary', 'text-background-dark');

            currentCategory = btn.getAttribute('data-category');
            const searchTerm = searchInput.value.toLowerCase();
            filterArticles(searchTerm, currentCategory);
        });
    });

    function filterArticles(term, category) {
        articles.forEach(article => {
            const title = article.querySelector('h3')?.innerText.toLowerCase() || '';
            const content = article.querySelector('p')?.innerText.toLowerCase() || '';
            const articleCategory = article.getAttribute('data-category');

            const matchesSearch = title.includes(term) || content.includes(term);
            const matchesCategory = category === 'all' || articleCategory === category;

            if (matchesSearch && matchesCategory) {
                article.classList.remove('hidden');
                article.classList.add('flex'); // Restore flex display
            } else {
                article.classList.add('hidden');
                article.classList.remove('flex');
            }
        });
    }
}

/**
 * Back to Top Button
 */
function setupBackToTop() {
    const backToTopBtn = document.getElementById('backToTop');
    if (!backToTopBtn) return;

    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTopBtn.classList.remove('opacity-0', 'invisible', 'translate-y-10');
            backToTopBtn.classList.add('opacity-100', 'visible', 'translate-y-0');
        } else {
            backToTopBtn.classList.add('opacity-0', 'invisible', 'translate-y-10');
            backToTopBtn.classList.remove('opacity-100', 'visible', 'translate-y-0');
        }
    });

    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/**
 * Cookie Consent Popup
 */
function setupCookieConsent() {
    const consent = localStorage.getItem('cookieConsent');
    if (consent) return; // Already accepted or rejected

    // Create Popup Container
    const popup = document.createElement('div');
    popup.id = 'cookie-popup';
    popup.className = 'fixed bottom-0 left-0 right-0 z-[60] p-4 md:p-6 bg-surface-dark border-t border-secondary shadow-[0_-5px_20px_rgba(0,0,0,0.5)] transform transition-transform duration-500 translate-y-full';

    // Determine relative path to cookie-policy.html based on Logo location
    // This allows it to work in subdirectories (news/, etc.)
    const logo = document.querySelector('img[alt*="Logo"]');
    let policyPath = 'cookie-policy.html';

    if (logo) {
        // logo.getAttribute returns the raw string in the HTML (e.g. "../images/logo.png")
        // We replace 'images/logo.png' with '' to get the relative root (e.g. "../")
        const logoSrc = logo.getAttribute('src');
        if (logoSrc && logoSrc.includes('images/logo.png')) {
            policyPath = logoSrc.replace('images/logo.png', 'cookie-policy.html');
        }
    }

    popup.innerHTML = `
        <div class="layout-container max-w-[1200px] mx-auto flex flex-col md:flex-row items-center justify-between gap-4 md:gap-8">
            <div class="text-sm text-gray-300 text-center md:text-left">
                <p>
                    Utilizziamo cookie tecnici e di terze parti per migliorare la tua esperienza. 
                    Continuando a navigare, accetti la nostra 
                    <a href="${policyPath}" class="text-primary hover:underline font-bold">Cookie Policy</a>.
                </p>
                <div class="mt-1 text-xs text-gray-500">
                    Il rifiuto potrebbe limitare alcune funzionalità.
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <button id="btn-reject" class="px-4 py-2 rounded-lg border border-secondary text-gray-300 hover:text-white hover:border-white transition-colors text-sm font-bold">
                    Rifiuta
                </button>
                <button id="btn-accept" class="px-6 py-2 rounded-lg bg-primary hover:bg-primary-hover text-background-dark shadow-[0_0_15px_rgba(79,255,172,0.3)] transition-all text-sm font-bold">
                    Accetta
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(popup);

    // Animate In (small delay to ensure render)
    setTimeout(() => {
        popup.classList.remove('translate-y-full');
    }, 100);

    // Handlers
    const btnAccept = popup.querySelector('#btn-accept');
    const btnReject = popup.querySelector('#btn-reject');

    const closePopup = () => {
        popup.classList.add('translate-y-full');
        setTimeout(() => popup.remove(), 500);
    };

    btnAccept.addEventListener('click', () => {
        localStorage.setItem('cookieConsent', 'accepted');
        closePopup();
    });

    btnReject.addEventListener('click', () => {
        localStorage.setItem('cookieConsent', 'rejected');
        closePopup();
    });
}

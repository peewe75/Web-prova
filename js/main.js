
const init = () => {
    setupSearch();
    setupBackToTop();
    setupMobileMenu();
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
    const desktopNav = document.querySelector('nav'); // Select the first nav (desktop)
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

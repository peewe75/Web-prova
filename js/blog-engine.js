/**
 * Blog Engine - Studio Legale BCS
 * Handles fetching posts from API and rendering them on different pages.
 */

const API_URL = 'api/blog.php';

// Format date helper
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('it-IT', options);
}

// 1. Render "Latest News" Widget (Homepage & Sidebar)
async function initLatestNews(containerId, limit = 3, category = null) {
    const container = document.getElementById(containerId);
    if (!container) return;

    // Show loading skeleton or text
    container.innerHTML = '<div class="animate-pulse flex space-x-4"><div class="flex-1 space-y-4 py-1"><div class="h-4 bg-gray-700 rounded w-3/4"></div><div class="space-y-2"><div class="h-4 bg-gray-700 rounded"></div></div></div></div>';

    try {
        let url = `${API_URL}?limit=${limit}`;
        if (category) url += `&category=${category}`;

        const res = await fetch(url);
        const posts = await res.json();

        container.innerHTML = ''; // Clear loading

        if (posts.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-sm">Nessuna news disponibile al momento.</p>';
            return;
        }

        posts.forEach(post => {
            const article = document.createElement('a');
            article.href = post.custom_url ? post.custom_url : `article.html?id=${post.id}`;
            article.className = 'group block bg-surface-dark p-6 rounded-2xl border border-secondary hover:border-primary transition-all hover:scale-[1.02] shadow-lg mb-6 last:mb-0';

            // Image fallback
            // const bgImage = post.image ? post.image : 'images/default-blog.jpg'; 

            article.innerHTML = `
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-primary uppercase tracking-wider bg-primary/10 px-2 py-1 rounded-md border border-primary/20">${post.category}</span>
                        <span class="text-xs text-gray-500 font-medium">${formatDate(post.date)}</span>
                    </div>
                    <h3 class="text-lg font-bold text-white group-hover:text-primary transition-colors leading-tight">${post.title}</h3>
                    <p class="text-sm text-gray-400 line-clamp-2 leading-relaxed">${post.summary}</p>
                    <div class="flex items-center gap-2 mt-2 text-primary font-bold text-xs uppercase tracking-wide opacity-0 group-hover:opacity-100 transition-opacity transform translate-y-2 group-hover:translate-y-0">
                        Leggi tutto <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </div>
                </div>
            `;
            container.appendChild(article);
        });

    } catch (err) {
        console.error('Error fetching news:', err);
        container.innerHTML = '<p class="text-red-400 text-sm">Impossibile caricare le news.</p>';
    }
}


// 2. Render Full Blog Grid (Blog Page)
async function initBlogGrid(containerId, categoryFilter = 'all', dateFilter = 'all') {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = '<p class="text-center text-white col-span-full">Caricamento articoli...</p>';

    try {
        let url = `${API_URL}`;
        const res = await fetch(url);
        let posts = await res.json();

        // Client-side filtering logic to handle "all" button correctly without extra API calls if desired,
        // but here we align with the API's query param logic or filter client side for instant feel.
        if (categoryFilter !== 'all') {
            posts = posts.filter(p => p.category.toLowerCase() === categoryFilter.toLowerCase());
        }

        // Date filtering
        if (dateFilter !== 'all') {
            const now = new Date();
            const filterDate = new Date();

            switch (dateFilter) {
                case 'week':
                    filterDate.setDate(now.getDate() - 7);
                    break;
                case 'month':
                    filterDate.setMonth(now.getMonth() - 1);
                    break;
                case 'year':
                    filterDate.setFullYear(now.getFullYear() - 1);
                    break;
            }

            posts = posts.filter(p => {
                const postDate = new Date(p.date);
                return postDate >= filterDate;
            });
        }

        container.innerHTML = '';

        if (posts.length === 0) {
            container.innerHTML = '<div class="col-span-full text-center py-20"><p class="text-xl text-gray-400">Nessun articolo trovato in questa categoria.</p></div>';
            return;
        }

        posts.forEach(post => {
            const article = document.createElement('a');
            article.href = post.custom_url ? post.custom_url : `article.html?id=${post.id}`;
            article.className = 'blog-article bg-surface-dark rounded-3xl overflow-hidden border border-surface-border group hover:border-primary transition-colors flex flex-col h-full shadow-lg block';

            // Build image HTML
            const imageHtml = post.image
                ? `<div class="bg-surface-dark w-full h-full flex items-center justify-center"><img src="${post.image}" alt="${post.title}" class="w-full h-full object-contain" loading="lazy"></div>`
                : `<div class="w-full h-full" style="background: linear-gradient(to bottom right, #1a3326, #244233);"></div>`;

            article.innerHTML = `
                <div class="h-48 md:h-56 overflow-hidden relative bg-surface-dark">
                    ${imageHtml}
                    <div class="absolute inset-0 bg-gradient-to-t from-background-dark via-transparent to-transparent opacity-60"></div>
                    <div class="absolute top-4 left-4">
                        <span class="bg-background-dark/90 backdrop-blur-sm text-primary text-xs font-bold px-3 py-1 rounded-full border border-primary/20 uppercase tracking-wide shadow-lg">
                            ${post.category}
                        </span>
                    </div>
                </div>
                <div class="p-8 flex flex-col flex-grow">
                    <div class="flex items-center gap-2 text-gray-400 text-xs mb-3 font-medium">
                        <span class="material-symbols-outlined text-sm">calendar_today</span>
                        <span>${formatDate(post.date)}</span>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3 group-hover:text-primary transition-colors leading-tight">
                        ${post.title}
                    </h3>
                    <p class="text-gray-400 text-sm leading-relaxed mb-6 flex-grow line-clamp-3">
                        ${post.summary}
                    </p>

                    <div class="flex items-center justify-between mt-auto border-t border-secondary/30 pt-4">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500 bg-background-dark p-1 rounded-full text-lg">person</span>
                            <span class="text-xs text-gray-300 font-medium">${post.author}</span>
                        </div>
                        <span class="material-symbols-outlined text-primary group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </div>
                </div>
            `;
            container.appendChild(article);
        });

    } catch (err) {
        console.error('Error fetching blog grid:', err);
        container.innerHTML = '<p class="text-red-400 col-span-full text-center">Errore caricamento blog. Controlla la console.</p>';
        console.log("Failed to parse JSON. Check API output for warnings/errors.");
    }
}


// 3. Render Single Article (Article Page)
// 3. Render Single Article (Article Page)
async function initSingleArticle() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');

    if (!id) {
        window.location.href = 'blog.html';
        return;
    }

    try {
        const res = await fetch(`${API_URL}?id=${id}`);
        const post = await res.json();

        if (!post) {
            document.querySelector('main').innerHTML = '<div class="text-white text-center py-20 text-2xl">Articolo non trovato</div>';
            return;
        }

        // DOM Elements
        // Check for Custom URL (AI Page)
        if (post.custom_url) {
            window.location.replace(post.custom_url);
            return;
        }

        // Standard Rendering
        document.title = `${post.title} - Studio Legale BCS`;
        document.getElementById('article-title').textContent = post.title;
        document.getElementById('article-category').textContent = post.category;
        document.getElementById('article-date').textContent = formatDate(post.date);
        document.getElementById('article-author').textContent = post.author;
        document.getElementById('article-content').innerHTML = post.content;

        // Append Feedback UI for Standard Article
        appendFeedbackUI(document.getElementById('article-content'), post.id);

        // Image
        const heroContainer = document.getElementById('article-hero');
        if (post.image) {
            heroContainer.style.backgroundImage = `url('${post.image}')`;
        } else {
            // Default gradient
            heroContainer.style.background = `linear-gradient(to right, #1a3326, #244233)`;
        }

    } catch (err) {
        console.error(err);
    }
}

/**
 * Append Feedback UI to container
 */
function appendFeedbackUI(container, postId) {
    const feedbackDiv = document.createElement('div');
    feedbackDiv.className = 'mt-12 pt-8 flex flex-col items-center gap-3 text-center relative z-10';
    feedbackDiv.innerHTML = `
        <h4 style="color: #1a3326; font-weight: bold; font-size: 1rem; margin-bottom: 0.5rem;">Questo articolo ti è stato utile?</h4>
        <div class="flex gap-3" id="feedback-buttons-${postId}">
            <button onclick="submitFeedback('${postId}', 'up')" 
                style="background-color: #244233; color: white; border: 1px solid #3a6b54; padding: 0.5rem 1.25rem; border-radius: 9999px; font-size: 0.875rem;"
                class="flex items-center gap-2 hover:bg-opacity-80 transition-all group cursor-pointer shadow-md">
                <span class="material-symbols-outlined" style="font-size: 1.125rem;">thumb_up</span>
                <span>Sì</span>
            </button>
            <button onclick="submitFeedback('${postId}', 'down')" 
                style="background-color: #244233; color: white; border: 1px solid #3a6b54; padding: 0.5rem 1.25rem; border-radius: 9999px; font-size: 0.875rem;"
                class="flex items-center gap-2 hover:bg-opacity-80 transition-all group cursor-pointer shadow-md">
                <span class="material-symbols-outlined" style="font-size: 1.125rem;">thumb_down</span>
                <span>No</span>
            </button>
        </div>
        <div id="feedback-message-${postId}" class="hidden text-primary font-medium bg-primary/10 px-4 py-2 rounded-lg border border-primary/20">
            Grazie per il tuo feedback!
        </div>
    `;

    container.appendChild(feedbackDiv);
}

/**
 * Handle Feedback Submission
 */
async function submitFeedback(postId, vote) {
    const btns = document.getElementById(`feedback-buttons-${postId}`);
    const msg = document.getElementById(`feedback-message-${postId}`);

    // Immediate visual feedback
    if (btns) btns.style.opacity = '0.5';

    try {
        const res = await fetch('api/submit_feedback.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ post_id: postId, vote: vote })
        });

        if (res.ok) {
            if (btns) btns.classList.add('hidden');
            if (msg) {
                msg.classList.remove('hidden');
                msg.innerHTML = `Grazie! <span class="text-xs text-gray-400 ml-2">(Feedback registrato)</span>`;
            }
        } else {
            alert('Errore nell\'invio del feedback.');
            if (btns) btns.style.opacity = '1';
        }
    } catch (e) {
        console.warn("Feedback API not reachable, showing UI feedback anyway", e);
        // Fallback for demo/offline mode
        if (btns) btns.classList.add('hidden');
        if (msg) {
            msg.classList.remove('hidden');
            msg.textContent = "Grazie per il tuo feedback!";
        }
    }
}

// Ensure global access
window.submitFeedback = submitFeedback;

---
description: Generate a new standalone AI News Page from JSON data
---

# Master Template News - studiodigitale.eu

## ⚠️ CRITICAL RULES

1. **ABSOLUTE PATHS ONLY** - All links MUST start with `/` (e.g., `/images/logo.png`, `/index.html`)
2. **NO AI IMAGE GENERATION** - Use thumbnail manually uploaded by user
3. **ASK FOR THUMBNAIL URL** - Before creating HTML, ask user for the blog thumbnail filename

---

## Protocol for News Generation

### Step 1: Read PDF
Extract ALL text content visually via browser agent.

### Step 2: Ask User for Thumbnail
**BEFORE creating HTML, ask the user:**
> "Qual è il nome del file della miniatura che hai caricato nel blog? (es: `Gemini_Generated_Image_xyz.png`)"

The full path will be: `/images/blog/[FILENAME]`

### Step 3: Create HTML File
- Use the Master Template below
- **ALL paths must be absolute** (start with `/`)
- Replace `[BLOG_THUMBNAIL_URL]` with `/images/blog/[user_filename]`
- Generate Table of Contents with anchor links
- Save to: `news/[slug].html`

### Step 4: Notify User
Tell user the file location: `studiodigitale/news/[slug].html`

---

## Path Reference (ABSOLUTE PATHS)

| Resource | Path |
|----------|------|
| Site Logo | `/images/logo.png` |
| Header Image | `/images/blog/[FILENAME_FROM_USER]` |
| Home | `/index.html` |
| Chi Siamo | `/chi-siamo.html` |
| Servizi | `/servizi.html` |
| Blog | `/blog.html` |
| Consulenze Tech | `/consulenze-tech.html` |
| Contatti | `/contatti.html` |
| Diritto Penale | `/approfondimento-penale.html` |
| Diritto del Gioco | `/approfondimento-gioco.html` |
| Diritto Civile | `/approfondimento-civile.html` |
| Bancario | `/approfondimento-bancario.html` |
| Crisi d'Impresa | `/approfondimento-crisi.html` |

---

## Master Template Structure

```html
<!DOCTYPE html>
<html class="dark" lang="it">
<head>
    <!-- Meta, Fonts (Space Grotesk, Noto Sans), Material Symbols -->
    <!-- Tailwind CDN with site config (colors: primary #4fffac, secondary #3a6b54, backgrounds) -->
    <!-- Embedded <style> for scrollbar, smooth scroll -->
</head>
<body class="bg-background-dark font-display text-white">

    <!-- HEADER -->
    <header class="sticky top-0 z-50 ...">
        <a href="/index.html">
            <img src="/images/logo.png" alt="Logo Studio Legale BCS">
        </a>
        <nav>
            <a href="/index.html">Home</a>
            <a href="/chi-siamo.html">Il Team</a>
            <a href="/servizi.html">Aree di Competenza</a>
            <a href="/blog.html">Blog & News</a>
            <a href="/consulenze-tech.html">Consulenze Tech</a>
            <a href="/contatti.html">Contatti</a>
        </nav>
    </header>

    <!-- HEADER IMAGE: Dynamically loaded from dashboard -->
    <div id="header-image" class="w-full relative">
        <img id="dynamic-header-img" src="" alt="[TITLE]"
            class="w-full h-auto object-contain max-h-[400px] mx-auto" style="display: none;">
        <div class="absolute inset-0 bg-gradient-to-t from-background-dark/80 to-transparent pointer-events-none"></div>
    </div>

    <script>
        // Dynamic Header Image Loader - Fetches image from dashboard data
        (async function loadHeaderImage() {
            try {
                const currentPath = window.location.pathname;
                const res = await fetch('/api/blog.php');
                const posts = await res.json();
                
                // Find the post that links to this page
                const matchingPost = posts.find(post => {
                    if (!post.custom_url) return false;
                    // Check if custom_url matches current page
                    return currentPath.includes(post.custom_url) || 
                           post.custom_url.includes(currentPath.split('/').pop()) ||
                           currentPath.endsWith(post.custom_url.split('/').pop());
                });
                
                const img = document.getElementById('dynamic-header-img');
                if (matchingPost && matchingPost.image) {
                    img.src = '/' + matchingPost.image;
                    img.style.display = 'block';
                } else {
                    // Fallback: show a gradient background
                    document.getElementById('header-image').style.background = 'linear-gradient(to right, #1a3326, #244233)';
                    document.getElementById('header-image').style.minHeight = '200px';
                }
            } catch (e) {
                console.log('Could not load dynamic header image:', e);
                document.getElementById('header-image').style.background = 'linear-gradient(to right, #1a3326, #244233)';
                document.getElementById('header-image').style.minHeight = '200px';
            }
        })();
    </script>

    <!-- MAIN CONTENT -->
    <main class="flex-grow layout-container px-4 md:px-10 lg:px-40 flex justify-center py-12 md:py-20">
        <div class="w-full max-w-[1000px] flex flex-col lg:flex-row gap-12">
            
            <!-- SIDEBAR: Table of Contents -->
            <aside class="lg:w-1/3 order-2 lg:order-1">
                <div class="sticky top-32 bg-surface-dark border border-secondary rounded-2xl p-6 shadow-xl">
                    <h3 class="text-primary font-bold uppercase tracking-widest text-sm mb-6">Indice Contenuti</h3>
                    <nav class="flex flex-col gap-4">
                        <!-- [GENERATED ANCHOR LINKS] -->
                    </nav>
                    <div class="mt-8 pt-8 border-t border-secondary/50">
                        <a href="/index.html" class="flex items-center gap-2 text-primary text-sm font-bold">
                            ← Torna alla Home
                        </a>
                    </div>
                </div>
            </aside>

            <!-- ARTICLE CONTENT -->
            <article class="lg:w-2/3 order-1 lg:order-2 font-body text-gray-200 leading-relaxed space-y-8">
                <header class="mb-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 border border-primary/20 mb-6">
                        <span class="text-primary text-xs font-bold uppercase tracking-wider">News Legali</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-display font-bold text-white mb-6">[TITLE]</h1>
                    <p class="text-xl text-gray-400 font-light">[SUMMARY]</p>
                </header>

                <!-- [SEMANTIC HTML CONTENT] -->
                
            </article>
        </div>
    </main>

    <!-- FOOTER: Exact copy from index.html (4-column layout) -->
    <footer class="border-t border-secondary bg-background-dark pt-16 pb-8 mt-auto">
        <div class="layout-container px-4 md:px-10 lg:px-40 flex justify-center">
            <div class="w-full max-w-[1200px] flex flex-col gap-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center gap-2 text-white">
                            <img src="/images/logo.png" alt="Logo Studio Legale BCS"
                                class="h-24 w-auto object-contain">
                        </div>
                        <p class="text-gray-400 text-sm">
                            Innovativi, competenti, affidabili.<br>L’alternativa etica e moderna.
                        </p>
                    </div>
                    <div class="flex flex-col gap-4">
                        <h4 class="text-white font-bold text-sm uppercase tracking-wider">Servizi</h4>
                        <a class="text-gray-400 hover:text-primary text-sm transition-colors"
                            href="/approfondimento-penale.html">Diritto Penale</a>
                        <a class="text-gray-400 hover:text-primary text-sm transition-colors"
                            href="/approfondimento-gioco.html">Diritto del Gioco</a>
                        <a class="text-gray-400 hover:text-primary text-sm transition-colors"
                            href="/approfondimento-civile.html">Diritto Civile e Famiglia</a>
                        <a class="text-gray-400 hover:text-primary text-sm transition-colors"
                            href="/approfondimento-bancario.html">Bancario e Assicurativo</a>
                        <a class="text-gray-400 hover:text-primary text-sm transition-colors"
                            href="/approfondimento-crisi.html">Crisi d'Impresa</a>
                        <a class="text-gray-400 hover:text-primary text-sm transition-colors"
                            href="/consulenze-tech.html">Consulenze Tech</a>
                    </div>
                    <div class="flex flex-col gap-4">
                        <h4 class="text-white font-bold text-sm uppercase tracking-wider">Studio</h4>
                        <a class="text-gray-400 hover:text-primary text-sm transition-colors"
                            href="/chi-siamo.html">Il
                            Team</a>
                        <a class="text-gray-400 hover:text-primary text-sm transition-colors"
                            href="/contatti.html">Contatti</a>
                    </div>
                    <div class="flex flex-col gap-4">
                        <h4 class="text-white font-bold text-sm uppercase tracking-wider">Contatti</h4>
                        <a href="mailto:info@studiodigitale.eu"
                            class="text-gray-400 hover:text-primary text-sm transition-colors">info@studiodigitale.eu</a>
                        <a href="tel:+390313515213"
                            class="text-gray-400 hover:text-primary text-sm transition-colors">031 3515213</a>
                        <a href="https://www.google.com/maps/search/?api=1&query=Via+Matteotti+33,+22063+Cantù+(CO)"
                            target="_blank" class="text-gray-400 hover:text-primary text-sm transition-colors">Via
                            Matteotti 33, 22063 Cantù (CO)</a>
                    </div>
                </div>
                <div class="border-t border-secondary pt-8">
                    <p class="text-gray-500 text-sm text-center">
                        © 2026 Studio Legale Sapone. Tutti i diritti riservati.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- BACK TO TOP BUTTON -->
    <button id="backToTop">...</button>
    <script>// Back to Top script</script>
</body>
</html>
```

---

## Checklist

- [ ] PDF text extracted completely
- [ ] Asked user for thumbnail filename
- [ ] Checklist updated
- [ ] HTML created with **ABSOLUTE PATHS** (`/images/`, `/index.html`)
- [ ] Header image uses **Dynamic Image Loader script**
- [ ] Table of Contents generated with anchor links
- [ ] All navigation links use absolute paths
- [ ] File saved to `news/[slug].html`
- [ ] User notified of file location

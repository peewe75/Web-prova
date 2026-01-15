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

    <!-- HEADER IMAGE: Use blog thumbnail -->
    <div id="header-image" class="w-full relative">
        <img src="/images/blog/[THUMBNAIL_FILENAME]" 
             alt="[TITLE]" 
             class="w-full h-auto object-contain max-h-[400px] mx-auto">
    </div>

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

    <!-- FOOTER -->
    <footer class="border-t border-secondary bg-background-dark pt-16 pb-8 mt-auto">
        <img src="/images/logo.png" alt="Logo">
        <!-- All footer links use absolute paths: /chi-siamo.html, /contatti.html, etc. -->
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
- [ ] HTML created with **ABSOLUTE PATHS** (`/images/`, `/index.html`)
- [ ] Header image set to `/images/blog/[user_filename]`
- [ ] Table of Contents generated with anchor links
- [ ] All navigation links use absolute paths
- [ ] File saved to `news/[slug].html`
- [ ] User notified of file location

---
description: Generate a new standalone AI News Page from JSON data
---

# Master Template News - studiodigitale.eu

## Folder Structure
```
studiodigitale/
├── news/                      ← All news pages go here
│   ├── images/                ← All cover images go here
│   │   └── copertina_[slug].png
│   └── [slug].html            ← News page HTML files
├── images/                    ← Site-wide images (logo, etc.)
├── index.html                 ← Main site
└── ...
```

## Protocol for News Generation

### Input Required
- PDF file in the `studiodigitale` folder

### Automatic Steps (Full Automation Mode)

1. **Read PDF**: Extract ALL text content visually via browser agent.

2. **Generate Cover Image**:
   - Use `generate_image` tool.
   - Prompt based on Title + Summary.
   - Style: "Modern legal aesthetics, neon green #4fffac and dark green #1a3326, professional, minimalistic, 16:9, no text."
   - Save to: `news/images/copertina_[slug].png`

3. **Create HTML File**:
   - Use Master Template (below).
   - Replace content placeholders.
   - Generate Table of Contents with anchor links.
   - Save to: `news/[slug].html`

4. **Notify User**: Provide paths for upload.

---

## Path Reference (from `news/[file].html`)

| Resource | Path |
|----------|------|
| Site Logo | `../images/logo.png` |
| Cover Image | `images/copertina_[slug].png` |
| Home | `../index.html` |
| Chi Siamo | `../chi-siamo.html` |
| Servizi | `../servizi.html` |
| Blog | `../blog.html` |
| Consulenze Tech | `../consulenze-tech.html` |
| Contatti | `../contatti.html` |
| Approfondimenti | `../approfondimento-*.html` |

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

    <!-- HEADER: Exact copy from index.html (Navigation bar) -->
    <header class="sticky top-0 z-50 ...">
        <!-- Logo (../images/logo.png), Nav links (../*.html) -->
    </header>

    <!-- COVER IMAGE -->
    <div id="header-image" class="w-full relative">
        <img src="images/copertina_[SLUG].png" 
             alt="[TITLE]" 
             class="w-full h-auto object-contain max-h-[400px] mx-auto">
        <div class="absolute inset-0 bg-gradient-to-t from-background-dark/80 to-transparent"></div>
    </div>

    <!-- MAIN CONTENT -->
    <main class="flex-grow layout-container px-4 md:px-10 lg:px-40 flex justify-center py-12 md:py-20">
        <div class="w-full max-w-[1000px] flex flex-col lg:flex-row gap-12">
            
            <!-- SIDEBAR: Table of Contents (sticky, auto-generated) -->
            <aside class="lg:w-1/3 order-2 lg:order-1">
                <div class="sticky top-32 bg-surface-dark border border-secondary rounded-2xl p-6 shadow-xl">
                    <h3 class="text-primary font-bold uppercase tracking-widest text-sm mb-6">Indice Contenuti</h3>
                    <nav class="flex flex-col gap-4">
                        <!-- [GENERATED ANCHOR LINKS] -->
                    </nav>
                    <div class="mt-8 pt-8 border-t border-secondary/50">
                        <a href="../index.html" class="flex items-center gap-2 text-primary text-sm font-bold">
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
                <!-- Use <section id="anchorN">, <h2>, <h3>, <p>, <ul>, <table>, etc. -->
                
            </article>
        </div>
    </main>

    <!-- FOOTER: Exact copy from index.html (4-column layout) -->
    <footer class="border-t border-secondary bg-background-dark pt-16 pb-8 mt-auto">
        <!-- Logo (../images/logo.png), Servizi links (../*.html), Contatti -->
    </footer>

    <!-- BACK TO TOP BUTTON -->
    <button id="backToTop" class="fixed bottom-8 right-8 bg-primary text-background-dark p-3 rounded-full shadow-lg opacity-0 invisible transition-all z-50">
        <span class="material-symbols-outlined">arrow_upward</span>
    </button>

    <script>
        // Back to Top + Smooth Scroll script (embedded)
    </script>
</body>
</html>
```

---

## Checklist for Each News

- [ ] PDF text extracted completely
- [ ] Cover image generated and saved to `news/images/`
- [ ] HTML created with correct paths (`../` for site resources)
- [ ] Table of Contents generated with anchor links
- [ ] All navigation links functional
- [ ] Self-contained code (CSS/JS embedded)
- [ ] File saved to `news/[slug].html`

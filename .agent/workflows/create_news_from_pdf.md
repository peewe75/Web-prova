---
description: Create a new News/Article page from a PDF document
---

# Workflow: Create News Page from PDF

This workflow describes the process of converting a PDF document (e.g., a newsletter or legal guide) into a fully styled HTML page for `studiodigitale.eu`, including automatic cover image generation.

## Prerequisites
- The PDF file must be available locally.
- Access to `index.html` to copy the latest Header/Footer.

## Steps

### 1. Content Extraction
- Use the browser agent to open the PDF.
- Visually transcribe or copy the text content.
- Identify the Main Title, Sections (H1, H2, H3), and specific blocks (Info, Warnings).
- **Clean up**: Remove AI artifacts, fix placeholders (e.g., change `[Nome Città]` to `Cantù`).

### 2. Cover Image Generation
- **Strategy**: Do NOT generate images with AI.
- **Action**: Ask the User to upload a cover image via the Dashboard during the article creation process.
- **Code**: Use the "Dynamic Image Loader" script in the HTML (see below) to automatically fetch the image uploaded to the dashboard.

### 3. HTML Generation
- Create a new file (e.g., `news/nomenews.html`).
- **Structure**:
    - Use the standard HTML5 boilerplate.
    - Include Tailwind CSS CDN and Config.
    - Include Fonts: `Space Grotesk` (Display) and `Noto Sans` (Body).
- **Layout**:
    - **Header**: Copy the *exact* `<header>` block from `index.html`.
    - **Footer**: Copy the *exact* `<footer>` block from `index.html`.
- **Critical - Paths**:
    - **MUST USE ABSOLUTE PATHS** (e.g., `/index.html`, `/images/logo.png`).
    - Do NOT use `../../` anymore. This ensures compatibility when served from root or subdirectories managed by the dashboard.

- **Header Image Implementation**:
Instead of a static `<img>` tag, use this script to load the image dynamically from the dashboard:

```html
<!-- Header Image - Dynamically loaded from dashboard -->
<div id="header-image" class="w-full relative">
    <img id="dynamic-header-img" src="" alt="[News Title]"
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
```

### 4. Upload via Dashboard
1. Go to `/admin/dashboard.php`.
2. Create **New Article**.
3. Fill in Title, Summary, Category.
4. **Upload Cover Image**: Select the image file.
5. **Upload AI Page**: Select the generated `.html` file.
6. Click **Save**.

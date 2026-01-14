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

### 2. Cover Image Generation (NEW)
- Use the `generate_image` tool to create a cover image.
- **Prompt**: Base it on the article's **Title** and **Summary**.
- **Style**: Include site branding: "Modern legal aesthetics, neon green #4fffac and dark green #1a3326 accents, professional, minimalistic, 16:9 aspect ratio, no text."
- **File Location**: Save directly to `images/copertina_[newsname].png`.
- **Note**: The image will be auto-generated and embedded in the HTML.

### 3. HTML Generation
- Create a new file (e.g., `nomenews.html`).
- **Structure**:
    - Use the standard HTML5 boilerplate.
    - Include Tailwind CSS CDN and Config (see `index.html`).
    - Include Fonts: `Space Grotesk` (Display) and `Noto Sans` (Body).
- **Layout**:
    - **Header**: Copy the *exact* `<header>` block from `index.html` (Navigation, Logo, Links).
    - **Cover Image**: Use `<img src="../../images/copertina_[newsname].png" class="w-full h-auto object-contain max-h-[400px] mx-auto">`.
    - **Sidebar**: Create a sticky `<aside>` with a Table of Contents linking to sections.
    - **Content**: Wrap the extracted text in `<article>`. Use semantic tags (`<section>`, `<h2>`, `<p>`).
    - **Footer**: Copy the *exact* `<footer>` block from `index.html` (4 columns: Logo, Servizi, Studio, Contatti).
- **Critical - Paths**:
    - All paths MUST use `../../` prefix (e.g., `../../index.html`, `../../images/logo.png`) because news pages are served from `news_pages/[folder]/`.

### 4. Feature Implementation
- **Dark Mode**: Ensure `class="dark"` is on `<html>` and colors use `bg-background-dark`, `text-gray-N`.
- **Back to Top**: Add the floating button script.
- **Smooth Scroll**: Ensure `scroll-behavior: smooth;` is active.

### 5. Verification
- Check navigation links (Home, Back to Top).
- Check visual consistency with `index.html`.
- Verify cover image displays correctly.

### 6. Delivery
- The HTML file is ready for direct upload via dashboard (supports .html files).
- The cover image is already in `images/` folder and will work once synced/deployed.

## Example Prompt for Image Generation
```
Create a professional blog cover image for a legal article titled "[TITLE]". 
Context: [SUMMARY]. 
Style: Modern corporate legal aesthetics, primary color #4fffac (neon green) and dark green #1a3326 accents, professional, minimalistic, abstract or symbolic, high resolution, 16:9 aspect ratio. No text on image.
```

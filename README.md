# Cofifi — WordPress theme

Custom WooCommerce theme for **Cofifi, Afro Coffee & Treats Co.** Classic PHP theme, no page builder, no build step.

This repository is the **theme only**. WordPress core, `wp-config.php`, uploads and the database are never committed.

---

## Install

Clone into a WordPress install so the theme folder is named `cofifi`:

```bash
cd /path/to/wordpress/wp-content/themes
git clone https://github.com/gunn3rfourlif3/cofifi.git cofifi
```

Locally that is:

```
C:\xampp\htdocs\development\cofifi\wp-content\themes\cofifi\
```

Then:

1. **Plugins → Add New → WooCommerce**, install and activate.
2. **Appearance → Themes → Cofifi → Activate.**
3. **Settings → Permalinks → Post name.** For `/shop/<slug>` product URLs, set the product permalink base to `shop`.
4. **Appearance → Customize → Cofifi details** — replace every value still in square brackets.
5. **Appearance → Menus** — create *Primary* and the four footer menus. Without them the theme falls back to a sensible default list, so nothing looks broken on a fresh install.

## What's here

```
cofifi/
├── style.css                  Theme header only — the real CSS is in assets/
├── functions.php              Bootstrap
├── inc/
│   ├── setup.php              Supports, menus, image sizes, Customizer
│   ├── enqueue.php            Styles, scripts, font preload
│   ├── template-tags.php      Inline SVG icons and helpers
│   └── woocommerce.php        Woo supports and hooks
├── header.php  footer.php
├── front-page.php             Homepage
├── index.php  page.php  single.php  404.php  searchform.php
├── template-parts/
│   ├── home/                  One file per homepage section
│   └── components/            product-card
├── woocommerce/               Only the templates that actually needed changing
└── assets/
    ├── css/theme.css          The stylesheet
    ├── js/theme.js            Nav drawer, accordions, gallery, quantity
    ├── fonts/                 Amulya (self-hosted brand font)
    └── img/                   Brand photography and the cut-out packshot
```

## Design system

Tokens live at the top of `assets/css/theme.css`. Don't hardcode hex values in templates.

- **Colour rule:** coffee is black-and-bone, CBD carries the teal (`#4CAA9C`, taken from the product label). Teal is the only accent — it marks the CBD line and primary actions, and never decorates a coffee card.
- **Display face:** Cormorant Garamond, 300/400, mixed case, loaded from Google Fonts.
- **Body and UI:** Amulya, self-hosted from `assets/fonts/`. Buttons and small labels are Amulya 500, uppercase, tracked — never the serif.
- **Layout:** 1440 px max, gutters and section padding scale with `clamp()`. Breakpoints at 1200 / 1080 / 1024 / 980 / 860 / 700 / 480.

The approved design lives here: https://claude.ai/code/artifact/75a35245-0bb4-4ba5-87eb-b6e425b14034

## Homepage

`front-page.php` calls one template part per section, in this order:

hero → two category doors → the range → story band → CBD spotlight → why Cofifi → subscription → newsletter

The order is the argument the page makes. Don't reorder it without a reason.

**The range** section uses real WooCommerce products (featured first, then most recent). With no products published it falls back to three designed cards so the homepage is never empty — **those fallback prices are sample values.**

## Product page

`woocommerce/content-single-product.php` supplies the two-column shell, the assurances list, the accordions and a spec table built from product attributes. Add attributes named Origin, Roast, Weight, CBD, THC and Batch and they appear automatically.

The add-to-cart form is deliberately left to WooCommerce's own templates. The designed grind and size pills are variation selectors; hand-rolling them here would break variable products and stock handling. Woo's selects are styled to match — swapping them for pills is a progressive enhancement, not a template rewrite.

## CBD compliance

Not optional.

- **No medical or therapeutic claims anywhere.** Describe carrier oil, spectrum, terpenes, dose and format. A product named "Focus" is fine; a claim about what it does is not.
- The disclaimer in `cofifi_compliance_line()` renders in the footer on every page, and again under the add-to-cart form on any product in the `cbd`, `cbd-oil` or `cbd-plus` category, or tagged `cbd`. **Keep those taxonomy terms in place.**
- Publish the certificate of analysis against the batch number.
- Have a person with legal responsibility read the CBD copy before launch. Nothing here has been legally reviewed.

## Placeholders and sample data

Values in **[square brackets]** are placeholders for facts nobody has confirmed yet — free-delivery threshold, address, contact email. They are editable in **Customize → Cofifi details**. Prices, batch numbers and the "Ceremony Set" bundle in the design are sample content. Replace before launch; don't invent figures that look real.

## Image notes

`assets/img/pack-hero.webp` is the hero packshot, cut out from the studio shot and relit on black.

Some of the supplied product renders carry AI-garbled label text ("AFIO COFFEE & TIEATE CO.", "100% Ethiopien") — `hero-4.png`, `hero-2.png`, `hero-3.png`, `qwen-1.png` and the two-bag shot. They are used small or cropped only. Reshoot before those appear at any size where the label is readable.

## Deployment

The site runs in its own container on the VPS. This repo is pulled into `wp-content/themes/cofifi` on the server; core, plugins, uploads and the database are managed there, not here.

## Conventions

- Enqueue assets in `inc/enqueue.php` with `filemtime()` versioning. No `@import`, no `<link>` in templates.
- Escape on output (`esc_html`, `esc_url`, `esc_attr`, `wp_kses_post`) and wrap user-facing strings in `__()` with the `cofifi` text domain.
- Copy a WooCommerce template into `woocommerce/` only when it genuinely needs to change; prefer hooks.
- Flex and grid with `gap` — not margins between siblings.
- Check every change at phone width before calling it done.

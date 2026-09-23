# Cofifi — WordPress theme

Custom WooCommerce theme for **Cofifi, Afro Coffee & Treats Co.** Classic PHP theme, no page builder, no build step.

This repository is the **theme only**. WordPress core, `wp-config.php`, uploads and the database are never committed.

---

## Install

**Automated:** with Apache and MySQL running, from the theme folder —

```powershell
powershell -ExecutionPolicy Bypass -File setup\install.ps1   # Windows / XAMPP
```
```bash
./setup/install.sh                                           # Linux / container
```

That downloads WordPress and WooCommerce, creates the database, activates the theme and provisions pages, menus, categories and sample products. Idempotent — safe to re-run. See [setup/README.md](setup/README.md).

**Manual**, if you would rather. Clone into a WordPress install so the theme folder is named `cofifi`:

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
│   ├── gallery.php            Photo manifest, tile + lightbox markup
│   ├── bootstrap.php          One-shot content bootstrap (see Gallery)
│   └── woocommerce.php        Woo supports and hooks
├── header.php  footer.php
├── front-page.php             Homepage
├── index.php  page.php  single.php  404.php  searchform.php
├── page-gallery.php           Template Name: Gallery
├── template-parts/
│   ├── home/                  One file per homepage section
│   └── components/            product-card
├── woocommerce/               Only the templates that actually needed changing
└── assets/
    ├── css/theme.css          The stylesheet
    ├── js/theme.js            Nav drawer, accordions, quantity, lightbox
    ├── fonts/                 Amulya (self-hosted brand font)
    └── img/                   Brand photography, packshot, gallery/
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

## Gallery

`inc/gallery.php` is the manifest — one row per photograph, with its alt text and caption.
`cofifi_gallery_tile()` renders a tile, `cofifi_lightbox()` prints the overlay. The homepage
strip (`template-parts/home/gallery.php`) and the Gallery page (`page-gallery.php`) both use
them, so the lightbox needs no per-page wiring: it reads the full-size source and caption off
the tiles themselves.

To add a photograph, drop `gNN.webp` (1000×667) and `gNN-full.webp` (1920×1280) into
`assets/img/gallery/` and add a row to `cofifi_gallery_items()` — or filter
`cofifi_gallery_items` from a child theme.

The **Gallery page** is created by `setup/provision.php` on a new install, and by
`inc/bootstrap.php` on one that is already running. `bootstrap.php` runs once, records the
version it reached in the autoloaded `cofifi_bootstrap` option, and afterwards costs one array
lookup per request. Bump `COFIFI_BOOTSTRAP` to make it run again.

## CBD compliance

Not optional.

- **No medical or therapeutic claims anywhere.** Describe carrier oil, spectrum, terpenes, dose and format. A product named "Focus" is fine; a claim about what it does is not.
- The disclaimer in `cofifi_compliance_line()` renders in the footer on every page, and again under the add-to-cart form on any product in the `cbd`, `cbd-oil` or `cbd-plus` category, or tagged `cbd`. **Keep those taxonomy terms in place.**
- Publish the certificate of analysis against the batch number.
- Have a person with legal responsibility read the CBD copy before launch. Nothing here has been legally reviewed.

### Unresolved: the labels and the copy disagree

The product photography shows labels reading **THC 750mg** and **THC 150mg**, and a
**Rasta Roast / Mada Kush** sub-brand that is not in the catalogue. The site states
**“Contains less than 0.3% THC”** in the utility bar, the hero trust row, the CBD band and two
product descriptions. Both cannot be true of the same product.

Nothing here resolves it. The photographs are used as shot and the copy is unchanged, so the
Coffee CBD+ product page currently shows a bag labelled 750 mg THC beside a claim of under
0.3%. Somebody with legal responsibility has to decide which is correct; the other one has to
change. Treat this as blocking for launch, not as a design note.

## Placeholders and sample data

Values in **[square brackets]** are placeholders for facts nobody has confirmed yet — free-delivery threshold, address, contact email. They are editable in **Customize → Cofifi details**. Prices, batch numbers and the "Ceremony Set" bundle in the design are sample content. Replace before launch; don't invent figures that look real.

## Image notes

`assets/img/` carries the product shoot. Every derivative is WebP, generated from the 6036 px
originals; the originals are not in the repo.

| File | What it is |
|---|---|
| `pack-hero.webp` | The hero packshot — cut out of the studio frame and relit on black |
| `door-coffee.webp` | The Roastery door |
| `roastery.webp` | The story band |
| `ritual.webp` | The standing-order band |
| `prod-coffee-sq.webp`, `prod-cbd-sq.webp` | Seeded product images, sideloaded into the media library |
| `gallery/gNN.webp` + `gNN-full.webp` | The gallery: a 1000×667 tile and a 1920×1280 frame each |
| `oil-*.jpg` | The CBD oil. The shoot is coffee only, so the oil keeps its own photography |

The AI-garbled renders are gone — labels reading “AFIO COFFEE & TIEATE CO.” or “100% Ethiopien”
are not in the theme any more. Don't bring them back; the real shoot covers every slot that used
one.

Served at `http://localhost/cofifi` via an Apache alias — see [setup/README.md](setup/README.md).

## Deployment

The site runs in its own container on the VPS. This repo is pulled into `wp-content/themes/cofifi` on the server; core, plugins, uploads and the database are managed there, not here.

## Conventions

- Enqueue assets in `inc/enqueue.php` with `filemtime()` versioning. No `@import`, no `<link>` in templates.
- Escape on output (`esc_html`, `esc_url`, `esc_attr`, `wp_kses_post`) and wrap user-facing strings in `__()` with the `cofifi` text domain.
- Copy a WooCommerce template into `woocommerce/` only when it genuinely needs to change; prefer hooks.
- Flex and grid with `gap` — not margins between siblings.
- Check every change at phone width before calling it done.

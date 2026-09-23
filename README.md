# Cofifi — WordPress theme

Custom WooCommerce theme for **Cofifi, Afro Coffee & Treats Co.** Classic PHP theme, no page builder, no build step.

This repository is the **theme only**. WordPress core, `wp-config.php`, uploads and the database are never committed.

## Names and where they live

| | Value | Why |
|---|---|---|
| Domain | **cofifi.com** | Registered through Google Workspace; DNS is managed there |
| Brand, as written | **COFiFi** | Matches the logo and the bags. Used in every heading, product name and paragraph |
| Registered name | **CoFiFi Roastery** | As it reads on the Workspace billing record. The footer copyright uses this, **not** the brand styling — it is a legal name, transcribed, not set |
| Contact | **hello@cofifi.com** | Customizer → COFiFi details |
| Local dev | `http://localhost/cofifi` | Files stay in `htdocs\development\cofifi`; an Apache alias joins the two |

`CoFiFi Roastery` in the footer is not a typo. If the registration actually reads something else,
change `legal_name` in `inc/setup.php` (or the Customizer) — don't reach for find-and-replace.

**`cofifi` in lower case is an identifier, never a display name.** It is the text domain, the
function prefix, the `COFIFI_` constants, the theme folder and the `@package` tag. Renaming the
brand must never touch any of them.

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
│   ├── catalogue.php          Categories and seed products — the single source
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
- `cofifi_compliance_line()` renders in the footer on every page, and again under the add-to-cart form on any product in `cbd`, `cbd-oil`, `cbd-plus`, `thc` or `rasta-roast`. **Keep those taxonomy terms in place** — see the table below.
- Publish the certificate of analysis against the batch number.
- Have a person with legal responsibility read the CBD copy before launch. Nothing here has been legally reviewed.

### Two notices, not one

`cofifi_compliance_line( $kind )` returns one of three lines:

| Kind | Where | What it says |
|---|---|---|
| `general` | The footer, every page | No THC figure at all — the range runs from none to 750 mg, so no single number is true sitewide |
| `cbd` | Products in `cbd`, `cbd-oil`, `cbd-plus` | Food supplement, less than 0.3% THC, 18+ |
| `thc` | Products in `thc` or `rasta-roast` | Contains THC, strictly 18+, do not drive, keep from children, edibles take up to two hours |

THC outranks CBD: a bag in both gets the stronger notice. The gate is in
`cofifi_product_compliance_note()` and keys off the **category slugs** — rename `thc` or
`rasta-roast` and a legal notice disappears from a product page with no other symptom.

The old sitewide claim “Contains less than 0.3% THC” was seeded into the Customizer as a theme
mod. `inc/bootstrap.php` retires it, replacing only that exact string. It is false of the
infused coffees and must not come back above every page. It is still correct on the **CBD oil**,
where it is stated product by product.

### Still to do before launch

- **There is no age gate.** Every THC product page says 18+, and nothing enforces it.
- Nothing here has been checked against South African law on the sale of THC-containing food.
  Names, weights and strengths are transcribed from the packaging; whether they may be sold and
  shipped this way is not a question this theme answers.

## The catalogue

`inc/catalogue.php` is the single source for product categories and seed products.
`setup/provision.php` seeds a new install from it; `inc/bootstrap.php` brings a running install
up to date from the same data, so the two cannot drift.

**Every figure in it is transcribed from the packaging**, not invented:

| SKU | Name | Weight | Cannabinoids |
|---|---|---|---|
| `COF-COFFEE-250` | Cofifi Coffee | 250 g | none |
| `COF-CBD-THC150-250` | Cofifi CBD + Coffee — THC 150 mg | 250 g | CBD + 150 mg THC |
| `COF-CBD-THC750-250` | Cofifi CBD + Coffee — THC 750 mg | 250 g | CBD + 750 mg THC |
| `RR-MK-THC500-150` | Rasta Roast Mada Kush Coffee — THC 500 mg | 150 g | 500 mg THC |
| `COF-OIL-30` | Cofifi CBD Oil — Focus | 30 ml | 150 mg CBD, under 0.3% THC |

The CBD + Coffee bags also carry “Handroasted in SA · 100% Ethiopian · creamy, smooth, no
acidity” and “Manufactured according to SAHPRA & MCC standards”. Rasta Roast carries “hand
roasted & infused with love by black families”, “lab tested” and an 18+ mark. All of that is in
the product copy and attributes.

`COF-CBD-250` (the old “Cofifi Coffee CBD+”) became `COF-CBD-THC150-250`. The seeder's
`renames` key carries the existing product across rather than leaving a duplicate, so the post,
its URL and any orders against it survive.

Seeding only ever touches products carrying `_cofifi_seeded`, and never deletes. Price and stock
are set on creation only — once the shop is live, those are the shop's business.

**The label small print is AI-garbled.** The headline elements are clean, but the body copy on
the bags reads “AFIO COFFEE & TIEATE CO.”, “HANDRRSTED IH SK 10D% ETHI0PIAH”, “SAHPSA & MCL
standards”. Reshoot or re-artwork before any of it is printed for real. The site's own copy uses
the corrected wording.

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

**cofifi.com is registered through Google Workspace**, so DNS lives in the Google admin console,
not at the host. Going live means pointing the A record (and `www`) at the VPS there — and
leaving the MX records alone, or Workspace mail stops. Set `SITE_URL=https://cofifi.com` when
running the installer on the server.

## Conventions

- Enqueue assets in `inc/enqueue.php` with `filemtime()` versioning. No `@import`, no `<link>` in templates.
- Escape on output (`esc_html`, `esc_url`, `esc_attr`, `wp_kses_post`) and wrap user-facing strings in `__()` with the `cofifi` text domain.
- Copy a WooCommerce template into `woocommerce/` only when it genuinely needs to change; prefer hooks.
- Flex and grid with `gap` — not margins between siblings.
- Check every change at phone width before calling it done.

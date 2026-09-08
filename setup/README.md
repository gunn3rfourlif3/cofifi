# Automated install

One command takes an empty folder to a working Cofifi shop with WooCommerce, the theme, pages, menus, categories and three sample products.

## What has to be there first

| Requirement | Why | Check |
|---|---|---|
| **PHP 8.0+** | WP-CLI runs on it | XAMPP ships it at `C:\xampp\php\php.exe` |
| **MySQL running** | the script creates the database | start MySQL in the XAMPP Control Panel |
| **Apache running** | to open the site afterwards | start Apache in the XAMPP Control Panel |
| **Internet** | fetches WP-CLI, WordPress core, WooCommerce | — |
| **This theme in place** | the script locates everything relative to itself | `wp-content/themes/cofifi/` |

Put the WordPress install at **`C:\xampp\htdocs\<brand>`**, not nested inside a `development/` folder — the site URL is then `http://localhost/<brand>`, which is shorter, matches the container path in production, and keeps `RewriteBase` simple. Keep brand source assets (logos, renders, font packs) *outside* the web root.

Nothing else. WP-CLI downloads itself into `setup/bin/` on first run and is gitignored.

The one thing the scripts can't do is start XAMPP's services — Apache and MySQL have to be running before you begin.

## Run it

**Windows / XAMPP**, from `wp-content\themes\cofifi`:

```powershell
powershell -ExecutionPolicy Bypass -File setup\install.ps1
```

**Linux / the VPS container**, from the theme folder:

```bash
./setup/install.sh
```

Both are **idempotent** — every step checks first and skips what is already done, so re-running after a failure picks up where it stopped. Neither ever drops a database or deletes a file.

## Options

```powershell
setup\install.ps1 -SiteUrl "http://localhost/cofifi" `
                  -DbName cofifi -DbUser root -DbPass "" `
                  -AdminUser cofifi -AdminEmail you@example.com `
                  -SkipProducts
```

```bash
SITE_URL=https://cofifi.co.za DB_NAME=cofifi DB_USER=cofifi DB_PASS=… \
ADMIN_USER=cofifi ADMIN_EMAIL=you@example.com ./setup/install.sh
```

On Linux the script also reads `setup/.env` if present. **Never commit a `.env` with real credentials** — it is gitignored.

If you don't pass an admin password one is generated and printed **once**. Save it there and then.

## What the scripts do

1. Locate PHP and confirm MySQL is running
2. Fetch WP-CLI into `setup/bin/`
3. Download WordPress core
4. Write `wp-config.php` (`cof_` table prefix, debug logging on, environment `local`)
5. Create the database
6. Install WordPress
7. Install and activate WooCommerce
8. Activate the Cofifi theme
9. Run `provision.php`
10. Flush rewrite rules

## What provision.php sets up

Run it on its own any time — it is idempotent and never deletes:

```bash
wp eval-file setup/provision.php
wp eval-file setup/provision.php --skip-products --skip-menus
```

- **Site:** title, tagline, `Africa/Johannesburg`, `/%postname%/` permalinks. Search-engine indexing is switched **off** automatically when the host is `localhost`, `.local` or `.test`.
- **WooCommerce:** ZAR with a space thousands separator, South Africa, kg/cm, stock management on, `/shop/<slug>` product URLs, onboarding wizard suppressed.
- **Categories:** `coffee`, `cbd`, `cbd-oil`, `cbd-plus`.
- **Pages:** Home, Our story, The ceremony, Lab certificates, Brewing, Delivery, Returns, Contact, Wholesale, Journal — with Home as the front page and Journal as the posts page.
- **Menus:** Primary plus the four footer menus, created and assigned to their theme locations.
- **Placeholders:** the bracketed values seeded into the Customizer.
- **Products:** the three SKUs with images pulled from the theme's own assets, categories, attributes and stock.

## Two things to know

**The category slugs are load-bearing.** The CBD disclaimer renders under the add-to-cart form for products in `cbd`, `cbd-oil` or `cbd-plus`. Rename or delete those terms and a legal notice silently disappears from the page.

**The seeded data is sample data.** Prices R265 / R395 / R620 are invented, and batch numbers are `[batch number]`. Products carry a `_cofifi_seeded` meta flag so you can find them later:

```bash
wp post list --post_type=product --meta_key=_cofifi_seeded --fields=ID,post_title
```

## Starting over

```powershell
# from the document root
php setup\bin\wp-cli.phar db reset --yes
php setup\bin\wp-cli.phar core install …   # or just re-run install.ps1
```

`db reset` drops everything. The theme and uploads are untouched.

## Doing this for the next brand

The pattern is portable — see the `woo-brand-storefront` skill. Per brand you change: the theme slug and folder, the `cofifi_*` theme mods, the category slugs the compliance notice keys off, the seed products, and the currency and country. The install scripts themselves need no changes beyond their defaults.

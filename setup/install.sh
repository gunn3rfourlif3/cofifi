#!/usr/bin/env bash
#
# Bootstrap a Cofifi WordPress install. Linux / container counterpart to
# install.ps1 — same steps, same provisioning script.
#
# Idempotent: safe to re-run. Each step is skipped when already done.
#
#   ./setup/install.sh
#
# Configuration comes from the environment (or a .env beside this script):
#
#   SITE_URL     http://localhost:8080
#   SITE_TITLE   Cofifi
#   DB_NAME DB_USER DB_PASS DB_HOST DB_PREFIX
#   ADMIN_USER ADMIN_PASS ADMIN_EMAIL
#   WP_ROOT      document root (default: three levels above this script)
#
# Never commit a .env with real credentials.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_DIR="$(dirname "$SCRIPT_DIR")"

# shellcheck disable=SC1091
[ -f "$SCRIPT_DIR/.env" ] && . "$SCRIPT_DIR/.env"

WP_ROOT="${WP_ROOT:-$(cd "$THEME_DIR/../../.." && pwd)}"
SITE_URL="${SITE_URL:-http://localhost:8080}"
SITE_TITLE="${SITE_TITLE:-Cofifi}"

DB_NAME="${DB_NAME:-cofifi}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_HOST="${DB_HOST:-localhost}"
DB_PREFIX="${DB_PREFIX:-cof_}"

ADMIN_USER="${ADMIN_USER:-cofifi}"
ADMIN_PASS="${ADMIN_PASS:-}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@example.com}"

SKIP_PRODUCTS="${SKIP_PRODUCTS:-0}"

step() { printf '  \033[36m→ %s\033[0m\n' "$1"; }
ok()   { printf '  \033[32m✓ %s\033[0m\n' "$1"; }
skip() { printf '  \033[90m· %s\033[0m\n' "$1"; }
warn() { printf '  \033[33m! %s\033[0m\n' "$1"; }
fail() { printf '  \033[31m✗ %s\033[0m\n' "$1"; exit 1; }

echo
echo "  Cofifi — install"
echo "  ----------------"

command -v php >/dev/null 2>&1 || fail "PHP not found on PATH."
[ -f "$THEME_DIR/style.css" ] || fail "Theme not found at $THEME_DIR"

ok "PHP     $(php -r 'echo PHP_VERSION;')"
ok "Root    $WP_ROOT"
ok "Theme   $THEME_DIR"

# --- WP-CLI ----------------------------------------------------------------

if command -v wp >/dev/null 2>&1; then
	WP_BIN=(wp)
	skip "WP-CLI already installed"
else
	mkdir -p "$SCRIPT_DIR/bin"
	if [ ! -f "$SCRIPT_DIR/bin/wp-cli.phar" ]; then
		step "Downloading WP-CLI"
		curl -fsSL -o "$SCRIPT_DIR/bin/wp-cli.phar" \
			https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
			|| fail "Could not download WP-CLI"
		chmod +x "$SCRIPT_DIR/bin/wp-cli.phar"
		ok "WP-CLI downloaded"
	fi
	WP_BIN=(php "$SCRIPT_DIR/bin/wp-cli.phar")
fi

# Containers commonly run as root; WP-CLI needs telling that is intended.
WP_FLAGS=(--path="$WP_ROOT")
[ "$(id -u)" = "0" ] && WP_FLAGS+=(--allow-root)

wpc()  { "${WP_BIN[@]}" "${WP_FLAGS[@]}" "$@"; }
wpq()  { "${WP_BIN[@]}" "${WP_FLAGS[@]}" "$@" >/dev/null 2>&1; }

# --- Core ------------------------------------------------------------------

mkdir -p "$WP_ROOT"

if [ -f "$WP_ROOT/wp-settings.php" ]; then
	skip "WordPress core already downloaded"
else
	step "Downloading WordPress"
	wpc core download --locale=en_GB
	ok "WordPress downloaded"
fi

# --- wp-config -------------------------------------------------------------

if [ -f "$WP_ROOT/wp-config.php" ]; then
	skip "wp-config.php already exists"
else
	step "Writing wp-config.php"
	wpc config create \
		--dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS" \
		--dbhost="$DB_HOST" --dbprefix="$DB_PREFIX" --locale=en_GB --skip-check
	ok "wp-config.php written"
fi

# --- Database --------------------------------------------------------------

if wpq db check; then
	skip "Database '$DB_NAME' already reachable"
else
	step "Creating database '$DB_NAME'"
	wpc db create
	ok "Database created"
fi

# --- Install ---------------------------------------------------------------

if wpq core is-installed; then
	skip "WordPress already installed"
else
	GENERATED=0
	if [ -z "$ADMIN_PASS" ]; then
		ADMIN_PASS="$(head -c 24 /dev/urandom | base64 | tr -dc 'A-Za-z0-9' | head -c 18)"
		GENERATED=1
	fi
	step "Installing WordPress"
	wpc core install --url="$SITE_URL" --title="$SITE_TITLE" \
		--admin_user="$ADMIN_USER" --admin_password="$ADMIN_PASS" \
		--admin_email="$ADMIN_EMAIL" --skip-email
	ok "WordPress installed"
	if [ "$GENERATED" = "1" ]; then
		echo
		warn "ADMIN PASSWORD (shown once — save it now)"
		echo "      user: $ADMIN_USER"
		echo "      pass: $ADMIN_PASS"
		echo
	fi
fi

# --- WooCommerce -----------------------------------------------------------

if wpq plugin is-active woocommerce; then
	skip "WooCommerce already active"
else
	step "Installing WooCommerce"
	wpc plugin install woocommerce --activate
	ok "WooCommerce active"
fi

# --- Theme and provisioning ------------------------------------------------

step "Activating the Cofifi theme"
wpc theme activate cofifi
ok "Theme active"

step "Provisioning content"
if [ "$SKIP_PRODUCTS" = "1" ]; then
	wpc eval-file "$SCRIPT_DIR/provision.php" --skip-products
else
	wpc eval-file "$SCRIPT_DIR/provision.php"
fi

wpc rewrite flush --hard

echo
ok "Done. Open $SITE_URL"
ok "Admin:  $SITE_URL/wp-admin"
echo

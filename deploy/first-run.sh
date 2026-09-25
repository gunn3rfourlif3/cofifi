#!/usr/bin/env bash
# First run only. Installs WordPress and WooCommerce into the volume and
# provisions the shop. Idempotent — every step checks before it acts, so a
# re-run after a failure picks up where it stopped.
#
# Refuses to start until preflight.sh is happy, because this box is not empty.
set -euo pipefail

cd "$(dirname "$0")"
[ -f .env ] || { echo "No .env — copy .env.example and fill it in."; exit 1; }

if [ "${1:-}" = "--skip-preflight" ]; then
  echo "!! Skipping the preflight check at your own risk."
  shift
else
  echo "==> preflight (read-only)"
  ./preflight.sh || {
    echo
    echo "Preflight found a blocker. Fix it, or re-run with --skip-preflight if"
    echo "you have read it and disagree. Nothing has been created."
    exit 1
  }
fi

set -a; . ./.env; set +a
: "${SITE_URL:?}" "${ADMIN_USER:?}" "${ADMIN_EMAIL:?}"

wp() { docker compose run --rm -T cli wp --path=/var/www/html "$@"; }

echo "==> bringing the stack up"
docker compose up -d db wordpress

echo "==> waiting for WordPress to write its files"
for _ in $(seq 1 60); do
  docker compose run --rm -T cli test -f /var/www/html/wp-settings.php && break
  sleep 2
done

if wp core is-installed 2>/dev/null; then
  echo "..  WordPress already installed"
else
  echo "==> installing WordPress"
  PASS="${ADMIN_PASSWORD:-}"
  GENERATED=0
  if [ -z "$PASS" ]; then PASS="$(openssl rand -base64 18)"; GENERATED=1; fi

  wp core install \
    --url="$SITE_URL" \
    --title="COFiFi" \
    --admin_user="$ADMIN_USER" \
    --admin_email="$ADMIN_EMAIL" \
    --admin_password="$PASS" \
    --skip-email

  if [ "$GENERATED" = "1" ]; then
    echo
    echo "    ADMIN PASSWORD (shown once — save it now):"
    echo "    $PASS"
    echo
  fi
fi

echo "==> WooCommerce"
wp plugin is-installed woocommerce 2>/dev/null || wp plugin install woocommerce
wp plugin is-active woocommerce 2>/dev/null || wp plugin activate woocommerce

echo "==> theme"
wp theme activate cofifi

echo "==> provisioning"
wp eval-file wp-content/themes/cofifi/setup/provision.php

echo "==> permalinks"
wp rewrite structure '/%postname%/' --hard || true
wp rewrite flush --hard || true

echo "==> checking WordPress answers inside the container"
CODE="$(docker compose exec -T wordpress php -r \
  'echo @get_headers("http://localhost/")[0] ?? "no answer";' 2>/dev/null || echo "could not check")"
echo "    $CODE"
case "$CODE" in
  *503*) echo "    503 is correct — the shop is closed." ;;
  *200*) echo "    !! 200, not 503. MAINTENANCE is not taking effect. Check .env." ;;
esac

echo
echo "Done. Nothing outside the 'cofifi' compose project was touched."
echo "  Closed:  MAINTENANCE=${MAINTENANCE:-true}"
echo "  Preview: ./preview-link.sh"
echo
echo "Next, and NOT done by this script:"
echo "  1. Add caddy-cofifi.snippet to the Caddyfile IN THE PMS03 REPO, commit,"
echo "     pull on this box, then validate and reload Caddy. See README.md."
echo "  2. Point the cofifi.com A records at this VPS. Leave MX alone."
echo "  Until both are done, $SITE_URL does not reach this stack."

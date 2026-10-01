#!/usr/bin/env bash
# Deploy a new version of the theme. Pull, re-provision, flush.
set -euo pipefail

cd "$(dirname "$0")"
[ -f .env ] || { echo "No .env here."; exit 1; }
set -a; . ./.env; set +a

wp() { docker compose run --rm -T cli wp --path=/var/www/html "$@"; }

echo "==> pulling the theme"
git -C .. pull --ff-only

# Schema only. Deliberately NOT `wp core update` — this is the routine deploy
# script, and silently pulling a new major WordPress into a live shop because
# wordpress.org released one is not a thing a theme deploy should do. Upgrade
# core on purpose, with first-run.sh or by hand.
echo "==> database schema (no-op unless core was upgraded)"
wp core update-db 2>/dev/null || true

echo "==> provisioning (idempotent — creates what is missing, changes nothing else)"
wp eval-file wp-content/themes/cofifi/setup/provision.php

echo "==> flushing"
wp rewrite flush --hard || true
wp cache flush || true

echo "==> restarting php so opcache picks up the new files"
docker compose restart wordpress

echo "Deployed: $(git -C .. rev-parse --short HEAD) — $(git -C .. log -1 --format=%s)"

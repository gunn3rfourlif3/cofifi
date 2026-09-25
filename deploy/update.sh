#!/usr/bin/env bash
# Deploy a new version of the theme. Pull, re-provision, flush.
set -euo pipefail

cd "$(dirname "$0")"
[ -f .env ] || { echo "No .env here."; exit 1; }
set -a; . ./.env; set +a

wp() { docker compose run --rm -T cli wp --path=/var/www/html "$@"; }

echo "==> pulling the theme"
git -C .. pull --ff-only

echo "==> provisioning (idempotent — creates what is missing, changes nothing else)"
wp eval-file wp-content/themes/cofifi/setup/provision.php

echo "==> flushing"
wp rewrite flush --hard || true
wp cache flush || true

echo "==> restarting php so opcache picks up the new files"
docker compose restart wordpress

echo "Deployed: $(git -C .. rev-parse --short HEAD) — $(git -C .. log -1 --format=%s)"

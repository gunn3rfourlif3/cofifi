#!/usr/bin/env bash
# Open the shop to the public: flip MAINTENANCE to false and restart.
# Reverse it with:  ./close-shop.sh
set -euo pipefail
cd "$(dirname "$0")"
[ -f .env ] || { echo "No .env here."; exit 1; }

want="${1:-false}"
sed -i.bak -E "s/^MAINTENANCE=.*/MAINTENANCE=${want}/" .env
docker compose up -d --force-recreate wordpress

if [ "$want" = "false" ]; then
  echo "Shop is OPEN. Check it in a private window."
else
  echo "Shop is CLOSED. The holding page is showing."
fi

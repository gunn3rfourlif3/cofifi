#!/usr/bin/env bash
#
# Remove COFiFi from this VPS completely, and touch nothing else.
#
# Everything this stack created is namespaced under the compose project
# "cofifi", and every command below is scoped to that project by name. No
# `docker system prune`, no `docker volume prune`, nothing that reaches past
# our own containers — those commands are how people delete other people's
# projects by accident.
set -uo pipefail
cd "$(dirname "$0")"

KEEP_DATA=1
[ "${1:-}" = "--with-data" ] && KEEP_DATA=0

echo "This will stop and delete:"
docker compose ps -a --format '  container  {{.Name}}' 2>/dev/null || true
echo "  network    cofifi_default"
if [ "$KEEP_DATA" = "0" ]; then
  echo "  volume     cofifi_db   (THE DATABASE — orders, products, the waiting list)"
  echo "  volume     cofifi_wp   (uploads and plugins)"
  echo
  echo "  *** --with-data was passed. This is not recoverable. ***"
else
  echo
  echo "  Volumes cofifi_db and cofifi_wp are KEPT. Re-run with --with-data to delete them too."
fi
echo
echo "Not touched: the host nginx, its vhosts, certificates, and every other"
echo "container, volume and network on this box."
echo
read -r -p "Type 'remove' to continue: " answer
[ "$answer" = "remove" ] || { echo "Nothing done."; exit 1; }

if [ "$KEEP_DATA" = "0" ]; then
  docker compose --profile tools down --volumes --remove-orphans
else
  docker compose --profile tools down --remove-orphans
fi

echo
echo "Done. Still on disk, remove by hand if you want them gone:"
echo "  this folder and the repo above it"
echo "  /etc/nginx/sites-{available,enabled}/cofifi.com   (then: nginx -t && systemctl reload nginx)"
echo "  /etc/letsencrypt/live/cofifi.com                  (certbot delete --cert-name cofifi.com)"
echo "  the A records, if you are giving the domain up"

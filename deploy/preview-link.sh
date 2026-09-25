#!/usr/bin/env bash
# Print the shareable preview link — opens the real shop for anyone holding it,
# for a week, without a login. Treat it like a password.
set -euo pipefail
cd "$(dirname "$0")"
docker compose run --rm -T cli wp --path=/var/www/html eval 'echo cofifi_preview_url() . PHP_EOL;'

#!/usr/bin/env bash
#
# Read-only audit of this VPS before COFiFi is installed on it.
#
# CREATES NOTHING. CHANGES NOTHING. STARTS NOTHING. Every command below either
# reads a file or asks a daemon a question. Run it as many times as you like.
#
# It answers one question: will putting this stack here disturb anything that
# is already running?

set -uo pipefail
cd "$(dirname "$0")"

FAIL=0
WARN=0

ok()   { printf '  \033[32mok\033[0m    %s\n' "$*"; }
warn() { printf '  \033[33mcheck\033[0m %s\n' "$*"; WARN=$((WARN+1)); }
bad()  { printf '  \033[31mSTOP\033[0m  %s\n' "$*"; FAIL=$((FAIL+1)); }
head_() { printf '\n\033[1m%s\033[0m\n' "$*"; }

NAME="cofifi"; ALIAS="cofifi-wp"; PROXY_NET="deploy_default"
DIR="$(cd .. && pwd)"; DOMAIN="cofifi.com"
if [ -f .env ]; then
  # shellcheck disable=SC1091
  set -a; . ./.env; set +a
  PROXY_NET="${PROXY_NETWORK:-$PROXY_NET}"
  DOMAIN="$(printf '%s' "${SITE_URL:-https://cofifi.com}" | sed -E 's#^https?://##; s#/.*##')"
fi

printf '\033[1mCOFiFi preflight\033[0m — nothing here writes anything.\n'
printf 'Checking: project "%s", alias %s on %s, %s\n' "$NAME" "$ALIAS" "$PROXY_NET" "$DOMAIN"

# ---------------------------------------------------------------- tooling
head_ "Tooling"
if command -v docker >/dev/null; then ok "docker $(docker --version | awk '{print $3}' | tr -d ,)"
else bad "docker is not installed"; fi

if docker compose version >/dev/null 2>&1; then ok "compose v2 ($(docker compose version --short 2>/dev/null))"
else bad "Compose v2 missing. This stack uses the top-level 'name:' key, which compose v1 ignores — without it every command would act on the wrong project."; fi

# -------------------------------------------------- the proxy we plug into
head_ "Reverse proxy network ($PROXY_NET)"
if command -v docker >/dev/null && docker info >/dev/null 2>&1; then
  if docker network inspect "$PROXY_NET" >/dev/null 2>&1; then
    ok "$PROXY_NET exists — we join it, we never create or remove it"
    ON_NET="$(docker network inspect "$PROXY_NET" --format '{{range .Containers}}{{.Name}} {{end}}' 2>/dev/null)"
    echo "        already on it: $ON_NET"
    case "$ON_NET" in
      *caddy*|*traefik*|*nginx*) ok "a proxy container is on this network" ;;
      *) warn "no obvious proxy container on $PROXY_NET — check PROXY_NETWORK in .env" ;;
    esac
    # An alias clash is silent and vicious: two containers answering to one
    # name means the proxy reaches whichever DNS feels like answering.
    if docker ps --format '{{.Names}}' | grep -q "^cofifi-wordpress"; then
      warn "a cofifi-wordpress container is already running"
    fi
    for c in $(docker network inspect "$PROXY_NET" --format '{{range .Containers}}{{.Name}} {{end}}' 2>/dev/null); do
      if docker inspect "$c" --format "{{range .NetworkSettings.Networks}}{{range .Aliases}}{{.}} {{end}}{{end}}" 2>/dev/null | grep -qw "$ALIAS"; then
        bad "the alias '$ALIAS' is already taken on $PROXY_NET by $c"
      fi
    done
    [ "$FAIL" -eq 0 ] && ok "alias '$ALIAS' is free"
  else
    bad "network $PROXY_NET does not exist. Find the proxy's network:
          docker inspect <proxy-container> --format '{{range \$k,\$v := .NetworkSettings.Networks}}{{\$k}} {{end}}'
        then set PROXY_NETWORK in .env."
  fi
else
  warn "cannot reach the docker daemon — is this user in the docker group?"
fi

# ------------------------------------------------------------ name clashes
head_ "Name clashes"
if command -v docker >/dev/null && docker info >/dev/null 2>&1; then
  if docker compose ls -a --format json 2>/dev/null | grep -q "\"Name\":\"$NAME\""; then
    warn "a compose project called '$NAME' already exists — this would adopt it, not create a new one"
  else ok "no compose project called '$NAME'"; fi

  CLASH="$(docker ps -a --format '{{.Names}}' | grep -E "^${NAME}[-_]" || true)"
  if [ -n "$CLASH" ]; then warn "containers already named like ours: $(echo "$CLASH" | tr '\n' ' ')"
  else ok "no container name clashes"; fi

  VOL="$(docker volume ls --format '{{.Name}}' | grep -E "^${NAME}_(db|wp)$" || true)"
  if [ -n "$VOL" ]; then
    warn "volumes already exist: $(echo "$VOL" | tr '\n' ' ') — first-run would reuse their data, not start clean"
  else ok "no volume clashes (will create ${NAME}_db, ${NAME}_wp)"; fi

  NET="$(docker network ls --format '{{.Name}}' | grep -E "^${NAME}_default$" || true)"
  [ -n "$NET" ] && warn "network $NET already exists" || ok "no network clash"
else
  warn "cannot talk to the docker daemon — run this with the same user that runs docker"
fi

# --------------------------------------------------------- who owns 80/443
head_ "What is already serving the internet"
FRONT="$( (ss -ltnp 2>/dev/null || netstat -ltnp 2>/dev/null) | grep -E '[:.](80|443)\b' )"
if [ -n "$FRONT" ]; then
  printf '        %s\n' "$FRONT"
  case "$FRONT" in
    *nginx*)    ok "host nginx is the front door — use nginx-cofifi.conf.example" ;;
    *apache*|*httpd*) warn "host Apache is the front door — the sample vhost is nginx. Translate it, or put COFiFi behind Apache with mod_proxy." ;;
    *caddy*)    warn "Caddy is the front door — add a block to its Caddyfile instead of the nginx sample (README has one)" ;;
    *docker*)   warn "a container owns 80/443 — see the proxy line below" ;;
    *)          warn "the owner of 80/443 is inside a container namespace, so ss cannot name it — see the proxy line below" ;;
  esac
else
  warn "nothing is listening on 80/443. If other sites are live here, they are being served some other way — find out how before you add anything."
fi

if command -v docker >/dev/null && docker info >/dev/null 2>&1; then
  PROXY="$(docker ps --format '{{.Names}} {{.Image}}' | grep -Ei 'traefik|nginx-proxy|caddy|jwilder|acme' || true)"
  if [ -n "$PROXY" ]; then
    ok "the front door is a container: $(echo "$PROXY" | tr '\n' '; ')"
    case "$PROXY" in
      *caddy*) echo "        Caddy issues and renews its own certificates."
               echo "        DO NOT run certbot, and do not add an nginx vhost — neither is read here."
               echo "        Add caddy-cofifi.snippet to the Caddyfile instead (see README)." ;;
    esac
  fi
fi

# ------------------------------------------------------- domain already used
head_ "Is $DOMAIN already configured here"
HITS="$(grep -rl "$DOMAIN" /etc/nginx /etc/apache2 /etc/caddy $HOME/*/Caddyfile $HOME/*/*/Caddyfile 2>/dev/null || true)"
if [ -n "$HITS" ]; then warn "$DOMAIN already appears in: $(echo "$HITS" | tr '\n' ' ')"
else ok "no existing vhost or Caddyfile mentions $DOMAIN"; fi

if [ -d /etc/letsencrypt/live ]; then
  ls /etc/letsencrypt/live 2>/dev/null | grep -q "$DOMAIN" \
    && warn "a Let's Encrypt cert for $DOMAIN already exists" \
    || ok "no existing cert for $DOMAIN"
fi

# ------------------------------------------------------------------ headroom
head_ "Headroom"
MEM_AVAIL=$(awk '/MemAvailable/{print int($2/1024)}' /proc/meminfo 2>/dev/null || echo 0)
if   [ "$MEM_AVAIL" -lt 700 ]; then bad "only ${MEM_AVAIL}MB RAM available. This stack is capped at 768MB; starting it here risks the kernel killing something else."
elif [ "$MEM_AVAIL" -lt 1200 ]; then warn "${MEM_AVAIL}MB RAM available — tight. The stack is capped at 768MB, so it cannot run away, but there is little slack."
else ok "${MEM_AVAIL}MB RAM available (stack is capped at 768MB)"; fi

if [ -z "$(swapon --show 2>/dev/null)" ]; then
  warn "no swap. On a small box that turns a memory spike into the OOM killer picking a victim — which may be one of your other sites."
else ok "swap is on"; fi

DISK=$(df -Pm / | awk 'NR==2{print $4}')
if   [ "$DISK" -lt 3000 ]; then bad "only ${DISK}MB free on / — images and volumes need roughly 2GB"
elif [ "$DISK" -lt 8000 ]; then warn "${DISK}MB free on /"
else ok "${DISK}MB free on /"; fi

if command -v docker >/dev/null && docker info >/dev/null 2>&1; then
  BIG="$(docker ps -q | head -1)"
  if [ -n "$BIG" ] && ! docker inspect "$BIG" --format '{{.HostConfig.LogConfig.Config}}' 2>/dev/null | grep -q max-size; then
    warn "your other containers have no log rotation — unbounded JSON logs are a classic way to fill a VPS disk. Ours are capped; theirs are not."
  fi
fi

# ------------------------------------------------------------------- verdict
head_ "Verdict"
if [ "$FAIL" -gt 0 ]; then
  printf '  \033[31m%s blocker(s) and %s thing(s) to check. Do not run first-run.sh yet.\033[0m\n\n' "$FAIL" "$WARN"
  exit 1
elif [ "$WARN" -gt 0 ]; then
  printf '  \033[33mNo blockers, %s thing(s) to look at above.\033[0m\n\n' "$WARN"
  exit 0
else
  printf '  \033[32mClear. Nothing here collides with what is already running.\033[0m\n\n'
  exit 0
fi

# Going live, closed

Two things happen here and they are worth keeping apart:

1. **Live** — the stack runs on the VPS and cofifi.com resolves to it.
2. **Closed** — every visitor gets a 503 and the holding page. Staff and anyone
   holding the preview link get the real shop.

The site is closed by default. `MAINTENANCE=true` in `.env` becomes a
`COFIFI_MAINTENANCE` constant in `wp-config.php`, and a constant beats the
Customizer setting — so a database restore, a half-finished import or someone
clicking the wrong checkbox cannot open the shop by accident. Opening it is a
deliberate, separate act: `./open-shop.sh`.

## This box is not empty

Five projects share it. Before anything is created:

```bash
./preflight.sh
```

Read-only. Starts nothing, writes nothing, safe to run repeatedly long before
you intend to install. `first-run.sh` refuses to start until it passes.

**[BLAST-RADIUS.md](BLAST-RADIUS.md)** is the companion: what this creates, what
it never goes near, what it is capped at, and how to remove it. Short, and the
honest version.

## How it attaches

One Caddy fronts the whole VPS — container `deploy-caddy-1`, part of the PMS03
compose project, on the docker network `deploy_default`.

COFiFi **joins that network** under the alias `cofifi-wp`, and Caddy proxies to
it by name. This is exactly how `buddhapets-wp` is already wired, and it means
**COFiFi binds no host port at all** — not 80, not 443, not even a loopback
port. The only route in is through Caddy.

Two consequences worth stating plainly:

- **Do not run certbot.** Caddy issues and renews its own certificates. Adding
  certbot to a box whose certs are container-managed is how you break renewals
  for every site on it. `nginx-cofifi.conf.example` in this folder is dead
  weight for this VPS — it is kept only in case a future box is fronted by host
  nginx instead.
- `deploy_default` is shared, so COFiFi's container can reach Locare's postgres
  and redis, and theirs can reach ours. That is already true of BuddhaPets; a
  docker network is a routing domain, not a security boundary. The database
  password is the thing keeping them apart, which is why `.env` is `chmod 600`
  and why `db` stays off the shared network entirely.

## Install

```bash
ssh deploy@your-vps
git clone https://github.com/gunn3rfourlif3/cofifi.git ~/cofifi
cd ~/cofifi/deploy

cp .env.example .env
# DB_PASSWORD, DB_ROOT_PASSWORD, ADMIN_EMAIL.
# Generate, don't invent:  openssl rand -base64 24
# Leave MAINTENANCE=true.
nano .env
chmod 600 .env

./preflight.sh      # read-only; read what it says
./first-run.sh      # refuses to run until preflight passes
```

`first-run.sh` brings the stack up, installs WordPress and WooCommerce,
activates the theme, provisions, and prints the admin password **once** if you
did not set one. Save it there and then. Idempotent — if it fails halfway, fix
the cause and run it again.

At the end it should report `HTTP/1.1 503` from inside the container. 503 is
correct: the shop is closed.

## The Caddyfile block

`/home/deploy/PMS03/deploy/Caddyfile` opens with:

> ⚠ THIS FILE IS THE SOURCE OF TRUTH. Edit it here and deploy with `git pull`,
> never directly on the VPS.

**Follow that.** Copy [`caddy-cofifi.snippet`](caddy-cofifi.snippet) into the
Caddyfile **in the PMS03 repo**, next to the BuddhaPets block, commit it, and
pull on the box. Pasting it in on the server is how the BuddhaPets blocks ended
up invisible to the repo for weeks, one `git checkout` away from taking a live
site down.

Then, on the box:

```bash
cd /home/deploy/PMS03/deploy
git pull

# Validate BEFORE reloading. A bad Caddyfile that gets loaded takes every site
# on this box down, not just COFiFi.
docker compose exec -w /etc/caddy caddy caddy validate --config Caddyfile

# Reload, not restart. Reload is graceful — no dropped connections, no gap for
# the other four projects.
docker compose exec -w /etc/caddy caddy caddy reload --config Caddyfile
```

If `validate` complains, stop. Nothing has changed yet at that point.

Caddy will now try to get a certificate for cofifi.com and fail, because DNS
still points at Squarespace. That is expected and harmless — it retries with
backoff and affects nothing else. It succeeds on its own once DNS moves.

## DNS

```bash
dig +short NS cofifi.com      # where is DNS actually managed?
dig +short A  cofifi.com      # 198.185.159.144 = still Squarespace
```

The domain came with Google Workspace, so the nameservers are most likely
Squarespace's — Google sold Google Domains to Squarespace in 2023 and those
registrations moved. The Workspace admin console links through to domain
management: **admin.google.com → Account → Domains → Manage domains**. If `dig`
says something else, go where it points.

| Type | Host | Value |
|---|---|---|
| A | `@` | this VPS's IPv4 |
| A | `www` | this VPS's IPv4 |

**Do not touch the MX records.** They point at Google and they are what makes
hello@cofifi.com work. Changing an A record does not affect mail; deleting MX
records does, instantly and silently.

## Now it is live, and closed

```bash
curl -I https://cofifi.com
```

`HTTP/2 503` with `X-Robots-Tag: noindex` is success. The 503 tells Google to
come back later instead of indexing a holding page as your homepage.

Then check the thing that would be embarrassing to get wrong:

```bash
curl -s -o /dev/null -w '%{http_code}\n' \
  https://cofifi.com/wp-content/themes/cofifi/deploy/.env
```

Must print `404`. If it prints `200`, your database password is on the public
internet — stop and fix the Caddyfile block before doing anything else.

Get the preview link:

```bash
./preview-link.sh
```

That URL opens the real shop for a week, for anyone who has it, no login. It is
a password. Send it privately.

Set what the holding page says in **Customize → COFiFi details → Launch line**.

## Opening

```bash
./open-shop.sh      # MAINTENANCE=false, restart
./close-shop.sh     # back to the holding page
```

Before opening, walk the shop signed out through the preview link and check what
is still placeholder: prices, `[batch number]`, `[R950]`, the address lines. And
read **CBD compliance** in the theme README — the THC notices are in place, but
nothing has been through a lawyer.

## Deploying a change

```bash
cd ~/cofifi/deploy && ./update.sh
```

Pulls the theme, re-runs provisioning (idempotent, never deletes), flushes and
restarts PHP so opcache picks up the new files. Does not touch Caddy.

## Removing it

```bash
./remove.sh               # containers and network; data volumes kept
./remove.sh --with-data   # everything, not recoverable
```

Both scoped to the `cofifi` compose project by name. Nothing here runs
`docker system prune` or anything else that could reach another project — that
is deliberate, and it is why removal is a script rather than a paragraph telling
you which things to stop. Remember to remove the Caddyfile block too, in the
PMS03 repo.

## The waiting list

Addresses from the holding page land in **Tools → COFiFi waiting list**, with a
CSV download. To send them to a list provider instead, filter
`cofifi_newsletter_action` to its form endpoint; nothing is stored locally then.

## Backups

**Not set up, and this stack does not do it for you.** Two volumes hold
everything: `cofifi_db` and `cofifi_wp`. Before you open to the public, get a
`mysqldump` and an uploads copy onto something that is not this VPS. Four other
projects share this box; whatever already backs them up is the obvious place to
add a fifth.

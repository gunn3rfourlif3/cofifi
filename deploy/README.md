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

## What has to be on the VPS already

| | Why |
|---|---|
| Docker + Compose v2 | the stack |
| A reverse proxy on :80/:443 | you already have one — Buddhapets is behind it |
| certbot, or whatever issues your certs | TLS |
| A free loopback port | `HTTP_PORT`; nothing binds to a public interface here |

## First run

```bash
ssh you@your-vps
sudo mkdir -p /srv/cofifi && sudo chown "$USER" /srv/cofifi
git clone https://github.com/gunn3rfourlif3/cofifi.git /srv/cofifi
cd /srv/cofifi/deploy

cp .env.example .env
# Fill in DB_PASSWORD, DB_ROOT_PASSWORD, ADMIN_EMAIL. Leave MAINTENANCE=true.
# Generate passwords rather than inventing them:  openssl rand -base64 24
nano .env
chmod 600 .env

./first-run.sh
```

`first-run.sh` brings the stack up, installs WordPress and WooCommerce, activates
the theme, runs `provision.php` and prints the admin password **once** if you
did not set one. Save it there and then.

It is idempotent — if it fails halfway, fix the cause and run it again.

### A word about this folder

The theme repo is mounted into the web root, and `deploy/` travels with it — so
`.env`, with your database password, is sitting inside a folder the web server
can reach. Two things stop it being served:

- the `location ~ ^/wp-content/themes/cofifi/(deploy|setup)/` rule in the vhost
  below, which is the one that actually protects you;
- `.htaccess` files in `deploy/` and `setup/`, as a second line for anyone who
  fronts this differently.

**After nginx is up, check it yourself** — do not take my word for it:

```bash
curl -s -o /dev/null -w '%{http_code}\n' https://cofifi.com/wp-content/themes/cofifi/deploy/.env
```

That must print `404`. If it prints `200`, stop and fix the vhost before you
point DNS at this box.

### Point the proxy at it

```bash
sudo cp nginx-cofifi.conf.example /etc/nginx/sites-available/cofifi.com
sudo nano /etc/nginx/sites-available/cofifi.com     # check HTTP_PORT matches .env
sudo ln -s /etc/nginx/sites-available/cofifi.com /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

Certificates come after DNS, not before — Let's Encrypt has to resolve the
domain to this box to issue.

## DNS

**Find out where DNS actually is before changing anything:**

```bash
dig +short NS cofifi.com
```

The domain came with Google Workspace, so the nameservers are most likely
Squarespace's — Google sold Google Domains to Squarespace in 2023 and those
registrations moved across. The Workspace admin console still links through to
domain management: **admin.google.com → Account → Domains → Manage domains**.
If `dig` says something else, go wherever it points instead.

At that DNS host:

| Type | Host | Value |
|---|---|---|
| A | `@` | your VPS IPv4 |
| A (or CNAME) | `www` | your VPS IPv4, or `cofifi.com` |

**Do not touch the MX records.** They point at Google and they are what makes
hello@cofifi.com work. Changing the A record does not affect mail; deleting MX
records does, immediately and silently.

Then wait for propagation and issue the certificate:

```bash
dig +short A cofifi.com            # should return your VPS IP
sudo certbot --nginx -d cofifi.com -d www.cofifi.com
```

## Now it is live, and closed

Open `https://cofifi.com` in a private window. You should get the holding page,
and `curl -I https://cofifi.com` should say `HTTP/2 503` with
`X-Robots-Tag: noindex`. The 503 is deliberate: it tells Google "come back
later" instead of indexing a holding page as your homepage.

Get the preview link:

```bash
./preview-link.sh
```

That URL opens the real shop for a week, for anyone who has it, with no login.
It is a password — send it over something private, not a public channel.

Set the launch line the holding page shows in **Customize → COFiFi details →
Launch line** (e.g. "Opening in March").

## Opening

```bash
./open-shop.sh      # MAINTENANCE=false, restart
./close-shop.sh     # back to the holding page
```

Before you open, walk the shop signed out via the preview link and check the
things that are still placeholders: prices, `[batch number]`, `[R950]`, the
address lines. And read **CBD compliance** in the theme README — the THC notices
are in place but nothing has been through a lawyer.

## Deploying a change

```bash
cd /srv/cofifi/deploy && ./update.sh
```

Pulls the theme, re-runs provisioning (idempotent, never deletes), flushes and
restarts PHP so opcache picks up the new files.

## The waiting list

Addresses from the holding page land in **Tools → COFiFi waiting list**, with a
CSV download. If you would rather they went straight to a list provider, filter
`cofifi_newsletter_action` to your provider's form endpoint and the form will
post there instead — nothing is stored locally in that case.

## Backups

Not set up here, and the stack does not do it for you. Two volumes hold
everything: `cofifi_db` and `cofifi_wp`. Before you open to the public, get a
`mysqldump` and an uploads copy onto something that is not this VPS.

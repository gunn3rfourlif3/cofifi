# What this touches on a VPS that is already busy

Written because COFiFi is going onto a box that is already serving other
projects, and "it should be fine" is not an answer.

## Creates

| | Name | Note |
|---|---|---|
| Containers | `cofifi-db-1`, `cofifi-wordpress-1` | prefixed by the compose project name |
| Volumes | `cofifi_db`, `cofifi_wp` | |
| Network | `cofifi_default` | its own bridge, isolated |
| Listening socket | `127.0.0.1:<HTTP_PORT>` | loopback only — not reachable from outside the box |
| Files | `/srv/cofifi/` | the git checkout |
| Files | one nginx vhost, one Let's Encrypt cert | only if you add them, in the DNS step |

## Never touches

- **Ports 80 and 443.** Nothing here binds them. Your existing proxy stays the
  only thing on the front door.
- **Any other container, volume, network or compose project.** Every command in
  every script is scoped to the `cofifi` project by name. There is no
  `docker system prune`, no `docker volume prune`, no bare `docker stop`.
- **The host's PHP, MySQL, nginx binaries or config**, beyond the one vhost file
  you add by hand.
- **The MX records.** Changing an A record does not affect mail. Deleting MX
  records does, instantly.

## Cannot run away

Both services are capped and cannot grow past it:

| | Memory | Swap | PIDs | Logs |
|---|---|---|---|---|
| `db` | 384 MB | none | 200 | 10 MB × 3, rotated |
| `wordpress` | 384 MB | 128 MB | 200 | 10 MB × 3, rotated |

768 MB is the ceiling for the pair. MariaDB is additionally told
`--innodb-buffer-pool-size=128M`, because left alone it sizes its buffer pool
from the host's *total* RAM — a number that is a lie on a shared box.

The log caps matter as much as the memory ones. Unrotated container logs
quietly filling the disk is one of the most common ways a busy VPS falls over,
and it takes every site on the box with it, not just the noisy one.

## The two things that could still bite

**Memory pressure.** The cap stops COFiFi growing, but it does not create RAM
that is not there. If the box has under ~1.2 GB free, adding 768 MB of ceiling
is how you meet the OOM killer — and it does not necessarily pick the new
arrival. `preflight.sh` checks this and refuses below 700 MB. If there is no
swap it warns, because swap is what turns a spike into slowness instead of a
dead process.

**Whatever already answers on 80/443.** The nginx vhost assumes host nginx. If
a container owns the front door (Traefik, nginx-proxy) then editing
`/etc/nginx` does nothing at best, and running `certbot --nginx` on a box whose
certificates are managed by a container is a good way to break renewals for
everything. `preflight.sh` identifies what is actually there;
`docker-compose.yml` carries label blocks for the container cases.

## Undo

```bash
./remove.sh               # containers and network; keeps the data volumes
./remove.sh --with-data   # everything, not recoverable
```

Both are scoped to the `cofifi` project. Neither can reach another one.

## Rehearsal

If you would rather not find out on the live box: `preflight.sh` is read-only
and safe to run right now, today, before anything is installed. It will tell
you what it finds and create nothing.

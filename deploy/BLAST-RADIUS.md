# What this touches on a VPS that is already busy

Written because COFiFi is going onto a box that is already serving other
projects, and "it should be fine" is not an answer.

## Creates

| | Name | Note |
|---|---|---|
| Containers | `cofifi-db-1`, `cofifi-wordpress-1` | prefixed by the compose project name |
| Volumes | `cofifi_db`, `cofifi_wp` | |
| Network | `cofifi_default` | its own bridge, isolated |
| Network membership | joins `deploy_default` as `cofifi-wp` | Caddy's network; we never create or remove it |
| Files | `~/cofifi/` | the git checkout |
| Text | one site block in PMS03's Caddyfile | added in that repo, not on the box |

## Never touches

- **Any port at all.** Not 80, not 443, not a loopback port. COFiFi is reachable
  only through Caddy, over the shared docker network.
- **Caddy's containers, volumes or certificates.** One site block is added to
  its config file, in the PMS03 repo. `reload`, never `restart`.
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

**The Caddyfile is shared.** It is the one file COFiFi touches that belongs to
another project, and a syntax error in it takes down all five sites, not one.
So: edit it in the PMS03 repo, `caddy validate` before `caddy reload`, and
reload rather than restart. If validate fails, nothing has changed yet.

**The shared network is not a wall.** `deploy_default` carries Locare's
postgres and redis. Anything on it can route to anything else on it — that is
already true of BuddhaPets, and it is why COFiFi's own database stays off that
network and only the web container joins.

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

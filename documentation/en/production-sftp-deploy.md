# Production SFTP deploy (IONOS / xdm VRP)

**FR:** [mise-en-production-sftp.md](../fr/mise-en-production-sftp.md)

## Command

```bash
cd ~/Projects/VRP-Resource-Planning
./scripts/deploy-xdm-vrp.sh --dry-run   # list files
./scripts/deploy-xdm-vrp.sh --upload    # build + route:cache + SFTP
```

Credentials: `VRP_SFTP_*` env vars or FileZilla site **`xdm VRP`**.

## What the script uploads

| Always | Git diff since `.deploy-base-ref` |
|--------|-----------------------------------|
| `public/build/**` | `app/`, `resources/`, `routes/`, `config/` |
| `bootstrap/cache/routes-v7.php` | `database/migrations/`, `public/` (except build) |

After a successful `--upload`, `.deploy-base-ref` is updated to local HEAD.

## Never do this

These mistakes have already broken production:

| Action | Effect |
|--------|--------|
| **Delete** remote `bootstrap/cache/config.php` | Laravel falls back to incomplete `.env` → **DB down** |
| Upload local `bootstrap/cache/config.php` | Overwrites prod DB credentials with local cache |
| Upload `services.php` / `packages.php` / `events.php` | Local provider cache ≠ prod |
| `rsync --delete` on `VRP/` | Can wipe storage / server-only files |
| Hand-rolled rsync without the checklist | Bypasses safety guards |

The script **rejects** those paths and never uses `--delete`.

### Protected files

- `bootstrap/cache/config.php` — **prod** config cache (MySQL, APP_KEY, …)
- `bootstrap/cache/prod.config.php` — local **backup** of that cache
- `bootstrap/cache/services.php`, `packages.php`, `events.php`
- `.env` / `.env.*`

Only **`routes-v7.php`** under `bootstrap/cache/` may be uploaded.

## Emergency restore (config.php deleted)

On the deploy machine:

```bash
cd ~/Projects/VRP-Resource-Planning
cp bootstrap/cache/prod.config.php bootstrap/cache/config.php
# rsync ONLY that file to VRP/bootstrap/cache/config.php
rm bootstrap/cache/config.php
```

Do not run `config:cache` locally and upload it (that would be **local** config).

## Database

1. Apply **ALTER / migrations** on the prod DB **before or with** the code deploy.
2. This script does **not** run `php artisan migrate` on the server.
3. Uploading migration files is documentation; apply SQL manually.

## Agent / human checklist

1. `./scripts/deploy-xdm-vrp.sh --dry-run` — review the list
2. Confirm no `bootstrap/cache/` path except `routes-v7.php`
3. DB migrations already applied (or planned)
4. `./scripts/deploy-xdm-vrp.sh --upload`
5. Smoke test: login, invoice list, one school page

## Electronic invoicing (server `.env` only)

SFTP never uploads `.env`. After this code is live, follow [electronic-invoicing-go-live.md](electronic-invoicing-go-live.md):

1. Apply `electronic_invoicing_enabled` on prod MySQL (migration file is uploaded as documentation).
2. Set SuperPDP production variables **on IONOS** (`E_INVOICE_ALLOW_PRODUCTION` stays `false` until cut-over).
3. `php artisan superpdp:go-live-check` on the server (prints no secrets).

## See also

- [AGENTS.md](../../AGENTS.md)
- Script: [`scripts/deploy-xdm-vrp.sh`](../../scripts/deploy-xdm-vrp.sh)

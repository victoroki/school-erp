# Deployment Guide

FTP-only shared hosting pipeline (cPanel/DirectAdmin + LiteSpeed). No SSH.
GitHub Actions uploads builds over FTP and triggers `public/deploy-hook.php`
over HTTP to run migrations, `storage:link`, and config/view caches.

## Server requirements (per school)

| Requirement | Value |
|---|---|
| PHP | >= 8.2 |
| Domain document root | `<SERVER_DIR>/public` — **must** point at the deployed `public/` folder |
| `SCHOOL1_SERVER_DIR` | The **parent** folder that contains `public/`, e.g. `/home/user/domains/school.example.com` |
| Database | MySQL reachable from the host |

> If the document root points anywhere else (e.g. an old `public_html` copy),
> requests never reach the freshly deployed code: `deploy-hook.php` 404s through
> Laravel's router and the site keeps serving stale files.

## Required GitHub secrets (prefix per school: SCHOOL1_ / SCHOOL2_ / SCHOOL3_)

| Secret | Required | Notes |
|---|---|---|
| `_FTP_SERVER` | yes | FTP hostname |
| `_FTP_USERNAME` / `_FTP_PASSWORD` | yes | FTP credentials |
| `_SERVER_DIR` | yes | Parent of `public/`; trailing slash optional |
| `_APP_URL` | yes | Public https URL of the school site |
| `_APP_NAME` | yes | Shown in UI/mail |
| `_APP_KEY` | yes | `base64:…` — generate once, keep stable across deploys |
| `_DB_DATABASE` / `_DB_USERNAME` / `_DB_PASSWORD` | yes | |
| `_DB_HOST` | no | Defaults to `127.0.0.1` |
| `_DEPLOY_HOOK_SECRET` | yes | Random ≥32 chars; written into server `.env` and sent as `X-Deploy-Token` |
| `_SETUP_TOKEN`, `_SAAS_OWNER_*`, `_MAIL_*` | no | Setup link, owner seeding, SMTP |

Missing required secrets → that school is skipped gracefully (pipeline stays green).

## Security invariants

- **`APP_DEBUG=false` in production.** Hardcoded by both workflows when generating
  the server `.env`. Never enable it on a live school — error pages would leak
  file paths, config values and stack traces.
- `deploy-hook.php` requires the deploy token on every call
  (`X-Deploy-Token` header, or `?token=` as a WAF workaround).
- `.env` is regenerated from secrets each deploy and excluded from FTP sync;
  student data/storage/logs are never overwritten.

## Known hosting quirks

- **ModSecurity/LiteSpeed blocks automated POSTs** (HTTP 406/403 with empty body).
  The workflows retry the hook as an authenticated GET (`?token=…`), which passes.
  No host-side change needed; if the GET fallback ever fails too, ask the host to
  whitelist `/deploy-hook.php`.
- A browser-like User-Agent is mandatory: default `curl/*` UAs are blocked outright.

## Post-deploy verification

1. Actions step "Verify uploaded files" lists remote `public/` with timestamps —
   confirm `deploy-hook.php` is fresh.
2. Probe table in the failed-run summary (if any):
   root `200/302`, hook GET `405`, POST/fallback `200`.
3. Site loads and redirects to `/login`.

## Rollback

Actions → **Rollback** → Run workflow → enter a `deploy-YYYYMMDD-HHMMSS` tag.
Re-deploys that exact tree to all configured schools and re-runs the hook.

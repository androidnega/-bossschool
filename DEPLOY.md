# Deploying BossSchool to cPanel shared hosting

This is the end-to-end checklist for getting **bossschoolapp.com** (or any
domain hosted on cPanel) running from this GitHub repo.

> Repo: <https://github.com/androidnega/-bossschool>
> Local entry points (for reference): `public/index.php`, `routes/web.php`

---

## 0. Pre-flight checks

In cPanel, confirm:

| What | Where in cPanel | What to set |
| ---- | ---- | ---- |
| PHP version | **MultiPHP Manager** | `bossschoolapp.com` → **PHP 8.2 or higher** (Laravel 13 requires it). |
| Required PHP extensions | **Select PHP Version** → Extensions | Enable: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`, `gd` (for QR code images), `zip`. |
| Composer | **Terminal** (or SSH) | Run `composer --version` to confirm Composer is in PATH. If not, your host usually ships it at `/usr/local/bin/composer`. |
| MySQL DB | **MySQL Databases** | Create a database + a DB user, assign the user to the database with **ALL PRIVILEGES**. Note the names — they'll look like `cpaneluser_bossschool` and `cpaneluser_bsadmin`. |

---

## Path A — Deploy via cPanel Git Version Control (recommended)

This is the cleanest flow: cPanel pulls the repo, then runs `.cpanel.yml` to
sync code into `public_html/`, install dependencies, migrate, and warm caches.

### A.1 — One-time setup

1. **cPanel → Git Version Control → Create**
   - Clone URL: `https://github.com/androidnega/-bossschool.git`
   - Repository Path: `/home/<your-cpanel-user>/repos/bossschool`
   - Repository Name: `bossschool`
   - Click **Create**.
2. After the clone finishes, click **Manage** → **Pull or Deploy** tab.
3. Click **Deploy HEAD Commit**.
   cPanel reads `.cpanel.yml` at the repo root and runs the tasks:
   - `rsync` source into `~/public_html/`
   - Drop the root `.htaccess` + fallback `index.php` from
     `deploy/public_html/` into `~/public_html/`
   - `composer install --no-dev --optimize-autoloader`
   - Idempotent migrations + platform/template seeders
   - `storage:link`
   - `config:cache`, `route:cache`, `view:cache`

### A.2 — Set up `.env` once (this file is NEVER deployed by the script)

```bash
cd ~/public_html
cp .env.example .env
nano .env           # fill in DB_*, APP_URL=https://bossschoolapp.com,
                    # APP_ENV=production, APP_DEBUG=false, MAIL_*, etc.
php artisan key:generate --force
```

### A.3 — Future deploys

Every time you push to GitHub, in cPanel:

1. **Git Version Control** → **Manage** → **Pull or Deploy**
2. Click **Update from Remote** (pulls)
3. Click **Deploy HEAD Commit** (runs `.cpanel.yml` tasks)

> **Want it fully automated?** Add a webhook from GitHub to cPanel's deploy
> endpoint, or wire a GitHub Action that SSHes in and runs
> `bash deploy/deploy.sh`. Out of scope for this doc.

---

## Path B — Manual upload via File Manager / FTP

Use this if you don't want Git Version Control on the server.

### B.1 — First-time setup

1. On your local machine, build a release tarball:
   ```bash
   git clone https://github.com/androidnega/-bossschool.git release
   cd release
   composer install --no-dev --optimize-autoloader
   tar -czf bossschool.tar.gz --exclude='.git' --exclude='node_modules' --exclude='tests' .
   ```
2. Upload `bossschool.tar.gz` to your home directory on the server (cPanel
   **File Manager** → upload → extract). The contents should end up in
   `~/public_html/`.
3. Copy the two root-forwarding files into the web root:
   ```bash
   cd ~/public_html
   cp deploy/public_html/.htaccess .htaccess
   cp deploy/public_html/index.php index.php
   ```
4. Create and fill in `.env`:
   ```bash
   cp .env.example .env
   nano .env
   ```
5. Run the install script:
   ```bash
   cd ~/public_html
   bash deploy/install.sh
   ```

### B.2 — Future deploys

Re-upload the changed files (or the whole tarball) and run:

```bash
cd ~/public_html
bash deploy/deploy.sh
```

---

## What lives where after deploy

```
~/public_html/
  .htaccess              ← from deploy/public_html/ ; forwards / → /public
  index.php              ← from deploy/public_html/ ; fallback when mod_rewrite is off
  .env                   ← created by you, NEVER in git
  app/                   ← Laravel source
  bootstrap/
  config/
  database/
  public/                ← Laravel's real document root
    index.php            ← real front controller
    .htaccess            ← standard Laravel routing
    css/, js/, build/
  resources/
  routes/
  storage/               ← chmod 775
  vendor/                ← created by composer install
```

> **Even cleaner option:** in cPanel → **Domains**, change
> `bossschoolapp.com`'s **Document Root** from `public_html` to
> `public_html/public`. Then **delete** the root `.htaccess` + `index.php`
> we just dropped in — Apache will serve `/public` directly and Laravel's
> own `public/.htaccess` takes over with zero proxy hops. This is the
> recommended long-term setup.

---

## First-run smoke test

After install:

1. Visit `https://bossschoolapp.com/` — should render the marketing homepage.
2. Visit `https://bossschoolapp.com/.env` — **must** return 403/404.
3. Visit `https://bossschoolapp.com/composer.json` — **must** return 403/404.
4. Visit `https://bossschoolapp.com/setup/superadmin` — the one-time
   SuperAdmin setup form. Fill it in. After submit, the route locks itself.
5. Log in. Enrol 2FA. You're live.

---

## Troubleshooting

### `HTTP ERROR 500`

Read the real error:

```bash
cd ~/public_html
sed -i 's/^APP_DEBUG=.*/APP_DEBUG=true/' .env
rm -f bootstrap/cache/config.php bootstrap/cache/routes-*.php
tail -n 100 storage/logs/laravel.log
```

Reload the page — Whoops will show the exception. Remember to set
`APP_DEBUG=false` and `php artisan config:cache` once fixed.

| Symptom in the trace | Fix |
| ---- | ---- |
| *No application encryption key has been specified.* | `php artisan key:generate --force` |
| *Class "..." not found* | `composer install --no-dev --optimize-autoloader` |
| *SQLSTATE\[HY000\] \[2002\] / Access denied* | Wrong `DB_HOST`/`DB_USERNAME`/`DB_PASSWORD` in `.env`. |
| *file_put_contents(/.../storage/framework/views/...): failed to open stream: Permission denied* | `chmod -R 775 storage bootstrap/cache` |
| *Vite manifest not found at: .../public/build/manifest.json* | Already fixed in this repo (we don't ship a Vite build — we use the Tailwind CDN). If you see this on an old commit, pull latest. |

### Directory listing instead of the app

The root `.htaccess` didn't make it into `public_html/`. Re-copy
`deploy/public_html/.htaccess` and `deploy/public_html/index.php` into
`~/public_html/`.

### 403 on every page

Permissions issue, or `Options -Indexes` is denying without an
`index.php` to fall back on. Make sure `public_html/index.php` exists
(the one from `deploy/public_html/index.php`).

### "Webhook signature invalid" on Paystack

The Paystack secret in **/platform/payments/settings** doesn't match the
secret in your Paystack dashboard. Re-enter it. The webhook URL Paystack
should call is `https://bossschoolapp.com/api/webhooks/payments/paystack`.

---

## Things that are intentionally NOT in this deploy

- `node_modules/`, Vite build output — the project uses the Tailwind CDN
  for styling, so no Node build step on the server.
- `tests/` — removed from this repo.
- `.env` — must be created by hand on each server.
- Demo seeders — `production:prepare` will purge them; the seeders we
  run on deploy (`PlatformBootstrapSeeder`, `GhanaBasicSchoolTemplateSeeder`,
  `PlansSeeder`, `PermissionsSeeder`) are production-safe and idempotent.

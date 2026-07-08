# Gourmet Haven API (Laravel)

Backend for the Gourmet Haven React app: menu browsing, placing orders, booking
tables, and an admin API (token auth via Sanctum) for managing everything.

This folder only contains the **application-specific files** (models, migrations,
controllers, routes, seeders, CORS config) — not a full Laravel skeleton, since
skeleton files (artisan, bootstrap, vendor/, base config) need to come from a
fresh `composer create-project` run on your machine with internet access.

## 1. Scaffold a fresh Laravel app

```bash
composer create-project laravel/laravel gourmet-haven-api
cd gourmet-haven-api
composer require laravel/sanctum
```

## 2. Copy these files in

Copy every file from this package into the matching path in your new project,
overwriting where needed:

```
app/Models/*.php                          -> app/Models/
app/Http/Controllers/Api/*.php             -> app/Http/Controllers/Api/
app/Http/Controllers/Api/Admin/*.php       -> app/Http/Controllers/Api/Admin/
app/Providers/AppServiceProvider.php       -> app/Providers/AppServiceProvider.php
database/migrations/*.php                  -> database/migrations/
database/seeders/*.php                     -> database/seeders/
routes/api.php                             -> routes/api.php
config/cors.php                            -> config/cors.php
Dockerfile                                 -> Dockerfile
.dockerignore                              -> .dockerignore
conf/nginx/nginx-site.conf                 -> conf/nginx/nginx-site.conf
scripts/00-laravel-deploy.sh               -> scripts/00-laravel-deploy.sh (keep it executable: chmod +x)
```

The `Dockerfile`, `.dockerignore`, `conf/`, and `scripts/` files are only
needed when you get to deployment (Section 7) — skip them for now if you're
just running locally.

## 3. Configure Sanctum

Sanctum needs its middleware/config published. Run:

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

This app uses plain Sanctum **API tokens** (Bearer tokens), not the
cookie-based SPA flow, so no `SANCTUM_STATEFUL_DOMAINS` setup is required. The
`auth:sanctum` middleware is already applied to admin routes in `routes/api.php`.

## 4. Install PostgreSQL locally and configure your `.env`

This project uses **PostgreSQL** (not MySQL) so local development matches
what you'll deploy to Render.

**Install Postgres:**
- Windows/Mac: download the installer from https://www.postgresql.org/download/ —
  it bundles **pgAdmin**, a phpMyAdmin-equivalent GUI for creating databases
  and browsing tables.
- During install you'll set a password for the default `postgres` user — remember it.

**Create the database** (via pgAdmin, or the command line):
```bash
psql -U postgres -c "CREATE DATABASE gourmet_haven"
```

**Set your `.env`** (a template is included as `.env.example` — copy it to `.env`
and fill in your password):

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=gourmet_haven
DB_USERNAME=postgres
DB_PASSWORD=your-postgres-password
```

Then generate your app key if you haven't already:
```bash
php artisan key:generate
```

## 5. Migrate and seed

```bash
php artisan migrate
php artisan db:seed
```

## 5b. Enable serving uploaded images

Menu item photos uploaded from the admin panel are saved to `storage/app/public/menu-items`.
For them to be reachable over HTTP, Laravel needs its public storage symlink:

```bash
php artisan storage:link
```

Run this once. Without it, uploaded images will save fine but return 404 when
the browser tries to load them.


This creates the tables and seeds:
- The same 10 menu items (across Starters/Mains/Desserts/Drinks) already used
  in the React frontend, so the two stay in sync.
- A default admin user:
  - **email:** `admin@gourmethaven.test`
  - **password:** `password`

  Change this password immediately if this ever goes near production.

## 6. Run the API

```bash
php artisan serve
```

The API is now available at `http://127.0.0.1:8000/api`.

---

## API Reference

### Public endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/categories` | List categories |
| GET | `/api/menu-items` | List available menu items (optional `?category=mains`) |
| GET | `/api/menu-items/{id}` | Get a single menu item |
| POST | `/api/orders` | Place an order (see body below) |
| GET | `/api/orders/{orderNumber}` | Look up an order by its order number |
| POST | `/api/reservations` | Book a table (see body below) |
| GET | `/api/reservations/{reservationNumber}` | Look up a reservation |

**POST `/api/orders` body:**
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "phone": "+1 555 012 3456",
  "fulfillment": "Pickup",
  "address": null,
  "preferred_time": "18:30",
  "payment_method": "Card on file",
  "notes": "No nuts please",
  "items": [
    { "menu_item_id": 3, "quantity": 2 },
    { "menu_item_id": 6, "quantity": 1 }
  ]
}
```
Prices, subtotal, tax (8%), and total are all computed server-side from the
current menu prices — the client only sends item IDs and quantities.

**POST `/api/reservations` body:**
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "phone": "+1 555 012 3456",
  "date": "2026-07-10",
  "time": "19:00",
  "guests": 4,
  "notes": "Window seat if possible"
}
```

### Admin endpoints (require `Authorization: Bearer {token}`)

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/admin/login` | `{ "email", "password" }` -> returns `token` |
| POST | `/api/admin/logout` | Revoke current token |
| GET | `/api/admin/me` | Current admin user |
| GET/POST | `/api/admin/categories` | List / create categories |
| PUT/DELETE | `/api/admin/categories/{id}` | Update / delete a category |
| GET/POST | `/api/admin/menu-items` | List / create menu items |
| PUT/DELETE | `/api/admin/menu-items/{id}` | Update / delete a menu item |
| GET | `/api/admin/orders` | Paginated list, optional `?status=pending` |
| GET | `/api/admin/orders/{id}` | Order detail |
| PATCH | `/api/admin/orders/{id}` | `{ "status": "preparing" }` |
| GET | `/api/admin/reservations` | Paginated list, optional `?status=pending` |
| GET | `/api/admin/reservations/{id}` | Reservation detail |
| PATCH | `/api/admin/reservations/{id}` | `{ "status": "confirmed" }` |

---

## 7. Deploying to Render (PostgreSQL + Docker)

Render doesn't run PHP natively, so Laravel apps deploy there as a Docker
container. This project already includes everything needed:
`Dockerfile`, `.dockerignore`, `conf/nginx/nginx-site.conf`, and
`scripts/00-laravel-deploy.sh` (this runs automatically on every deploy —
installs dependencies, caches config/routes, links storage, and runs
migrations).

**Steps:**

1. Push this project to a GitHub repo (make sure `.env` is in `.gitignore`
   and is **not** committed).

2. In Render, create a **PostgreSQL** database (New → PostgreSQL). Once it's
   ready, copy its **Internal Database URL** — you'll need it in step 4.

3. In Render, create a **Web Service** from your repo, and choose **Docker**
   as the runtime when prompted (Render will detect the `Dockerfile`
   automatically).

4. Under the service's **Environment** tab, add:

   | Key | Value |
   |---|---|
   | `DATABASE_URL` | The Internal Database URL from step 2 |
   | `DB_CONNECTION` | `pgsql` |
   | `APP_KEY` | Output of running `php artisan key:generate --show` locally |
   | `APP_ENV` | `production` |
   | `APP_DEBUG` | `false` |
   | `APP_URL` | Your Render service URL, e.g. `https://gourmet-haven-api.onrender.com` |

   Laravel's default `config/database.php` already reads `DATABASE_URL`
   automatically for the `pgsql` connection — no extra config needed.

5. Deploy. Watch the build logs — `scripts/00-laravel-deploy.sh` runs
   automatically and will create all your tables via `migrate --force`.

6. **Seed once, manually**, after the first successful deploy — don't leave
   seeding in the automatic deploy script, or every redeploy would re-run it.
   Use Render's **Shell** tab on the service to run:
   ```bash
   php artisan db:seed --force
   ```

7. Update your React app's `.env` (or hosting provider's env vars) so
   `VITE_API_URL` points at `https://your-service.onrender.com/api` instead
   of `http://127.0.0.1:8000/api`, and add your deployed frontend's URL to
   `config/cors.php`'s `allowed_origins`.

**Note on uploaded images:** Render's filesystem is ephemeral — anything
written to disk (including files uploaded via `storage:link`) is wiped on
every redeploy or restart. For a portfolio/demo this is usually fine, but for
production you'll eventually want to swap local disk storage for Render's
persistent Disks or an S3-compatible bucket. Ask if you want help wiring that up.

---

## Next step: wire the React frontend

Once this is running, the frontend needs to:
1. Fetch `/api/menu-items` instead of the local `menuData.js` array on the Menu/Home pages.
2. POST to `/api/orders` from Checkout instead of generating a fake order number client-side.
3. POST to `/api/reservations` from BookTable instead of generating a fake reservation number client-side.
4. Point `fetch`/`axios` base URL at `http://127.0.0.1:8000/api` in development (and set up CORS-friendly env vars for production).

Happy to build that integration next — just say the word.

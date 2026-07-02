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
database/migrations/*.php                  -> database/migrations/
database/seeders/*.php                     -> database/seeders/
routes/api.php                             -> routes/api.php
config/cors.php                            -> config/cors.php
```

## 3. Configure Sanctum

Sanctum needs its middleware/config published. Run:

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

This app uses plain Sanctum **API tokens** (Bearer tokens), not the
cookie-based SPA flow, so no `SANCTUM_STATEFUL_DOMAINS` setup is required. The
`auth:sanctum` middleware is already applied to admin routes in `routes/api.php`.

## 4. Configure your `.env` for MySQL

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gourmet_haven
DB_USERNAME=root
DB_PASSWORD=
```

Create the `gourmet_haven` database in MySQL first (e.g. via
`mysql -u root -e "CREATE DATABASE gourmet_haven"`).

## 5. Migrate and seed

```bash
php artisan migrate
php artisan db:seed
```

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

## Next step: wire the React frontend

Once this is running, the frontend needs to:
1. Fetch `/api/menu-items` instead of the local `menuData.js` array on the Menu/Home pages.
2. POST to `/api/orders` from Checkout instead of generating a fake order number client-side.
3. POST to `/api/reservations` from BookTable instead of generating a fake reservation number client-side.
4. Point `fetch`/`axios` base URL at `http://127.0.0.1:8000/api` in development (and set up CORS-friendly env vars for production).

Happy to build that integration next — just say the word.

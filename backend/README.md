# Krishi Sathi: backend (API + admin panel)

Laravel 12 · PHP 8.2+ · MySQL/MariaDB · Sanctum tokens · Blade + Bootstrap (assets bundled, no Node build)

## Setup with XAMPP

1. In `C:\xampp\php\php.ini`, make sure these lines are **not** commented out:
   `extension=pdo_mysql`, `extension=mbstring`, `extension=openssl`, `extension=fileinfo`, `extension=gd`, `extension=zip`.
2. Start **MySQL** in the XAMPP Control Panel. Create the database `krishi_sathi` (collation `utf8mb4_unicode_ci`).
3. Then:
   ```bash
   composer install
   copy .env.example .env        # cp on macOS/Linux
   php artisan key:generate
   php artisan migrate --seed
   php artisan serve             # http://localhost:8000
   ```
4. Admin panel: http://localhost:8000/admin
   (`admin@krishisathi.test` / `Admin@12345`, `expert@krishisathi.test` / `Expert@12345`, `prices@krishisathi.test` / `Prices@12345`)

To let a phone on your Wi-Fi reach the API: `php artisan serve --host=0.0.0.0 --port=8000` and allow port 8000 in the firewall.

Using XAMPP's Apache instead of `artisan serve`: point a virtual host's `DocumentRoot` to `backend/public` with `AllowOverride All`. No `storage:link` is needed; photos are served by the `/media/...` route.

## Useful commands

| | |
|---|---|
| `php artisan migrate:fresh --seed` | Rebuild the database with reference **and demo** data |
| `php artisan db:seed --class=ReferenceDataSeeder` | Reference data only (districts, knowledge base, markets): use for production |
| `php artisan krishi:export-content` | Copy the knowledge base + districts into `../mobile/assets/seed/` for offline first launch |
| `php artisan test` | 64 tests on in-memory SQLite |
| `DB_CONNECTION=mysql DB_DATABASE=krishi_sathi_test php artisan test` | Same tests on MySQL (create that database first) |
| `./vendor/bin/pint` | Code style |

## Where things are

| Path | What |
|---|---|
| `routes/api.php` | Mobile API v1 (see `../docs/API.md`) |
| `routes/web.php` | Admin panel routes, grouped by permission |
| `app/Providers/AppServiceProvider.php` | **Role permission table** (Gates) and rate limits |
| `app/Http/Controllers/Api/V1` · `Admin` | Controllers |
| `app/Services` | Weather (Open-Meteo + cache), farm advisory rules, prices/trends, image resizing, content bundle |
| `app/Http/Middleware` | Active-user check, staff-only, security headers, API locale, JSON gzip/UTF-8 |
| `config/krishi.php` | Helpline number, bigha size, token lifetime, weather, upload limits |
| `database/seeders/data/*.php` | **The seed knowledge base**: edit crops, problems, articles here (or in the admin panel) |
| `lang/bn.json`, `lang/bn/validation.php` | Bangla API error messages |
| `resources/views/admin` | Admin Blade views; `components/form/*` are reusable form fields |
| `tests/` | Unit + feature tests |

See [`../PROJECT_PLAN.md`](../PROJECT_PLAN.md) for the architecture, database design, security and deployment notes.

# Laravel Fintech API

A REST API built with Laravel 12 and JWT authentication, demonstrating a Service–Repository architecture for Customer management. Portfolio-ready learning project focused on clean separation of concerns, API authentication, and test scaffolding.

> **Status:** Educational MVP. Core CRUD scaffolding and JWT setup are present. Auth wiring needs fixes before production use — see [Known Limitations](#known-limitations).

## Table of Contents

- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Verified Features](#verified-features)
- [Installation](#installation)
- [Environment Configuration](#environment-configuration)
- [API Endpoints](#api-endpoints)
- [Authentication](#authentication)
- [Request / Response Formats](#request--response-formats)
- [Testing](#testing)
- [Project Structure](#project-structure)
- [Known Limitations](#known-limitations)
- [Roadmap](#roadmap)
- [License / Author](#license--author)

## Tech Stack

Verified from `composer.json`:

- **PHP:** `^8.2`
- **Laravel Framework:** `^12.0`
- **Auth:** `tymon/jwt-auth ^2.2` (TTL 60 minutes by default in `config/jwt.php`)
- **Dev / Test:** `phpunit/phpunit ^11.5.3`, `fakerphp/faker`, `mockery/mockery`, `laravel/pint`, `laravel/sail`
- **Database:** SQLite by default (`DB_CONNECTION=sqlite` in `.env.example`), MySQL-compatible via Eloquent
- **Tests DB:** SQLite `:memory:` per `phpunit.xml`

## Architecture

```text
routes/api.php
  -> Controllers (app/Http/Controllers/Api/)
  -> Services (app/Services/CustomerService.php)
  -> Repositories (CustomerRepositoryInterface + CustomerRepository)
  -> Models (User implements JWTSubject, Customer)
```

- `AuthController@login` calls `auth()->attempt(['email','password'])` and returns `{ token }`.
- `CustomerController` delegates all logic to `CustomerService` (`list/store/show/update/delete`).
- `CustomerService` depends only on `CustomerRepositoryInterface`, bound in `AppServiceProvider@register`.
- Custom `JwtAuthMiddleware` parses token via `JWTAuth::parseToken()->authenticate()`.
- Migrations: `users` (+ `password_reset_tokens`, `sessions`) and `customers (id, name, email unique, phone, timestamps)`.
- Seeders: `DatabaseSeeder` creates one `User`; `CustomerSeeder` creates one standalone `Customer`.

## Verified Features

Only what exists in code:

- [x] `POST /api/login` returns JWT on valid credentials
- [x] Customer CRUD behind JWT middleware group
- [x] Validation on create: `name: required, email: required|email, phone: required`
- [x] Service–Repository separation with interface binding
- [x] Eloquent models, migrations, seeders, factory (`UserFactory`)
- [x] PHPUnit scaffolding (1 Feature + 1 Unit test)
- [ ] Not implemented: logout/refresh/me, pagination, API Resources, policies, rate-limiting

## Installation

```bash
git clone https://github.com/Kazi-Bablu/laravel-api.git
cd laravel-api

composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate
php artisan db:seed
# CustomerSeeder is standalone (not called by DatabaseSeeder):
php artisan db:seed --class=Database\\Seeders\\CustomerSeeder

php artisan serve
# API base URL: http://localhost:8000/api
```

Requirements: PHP ^8.2 with `pdo_sqlite` (or MySQL), Composer, no Node build needed for API-only use.

## Environment Configuration

`.env.example` defaults:

```env
APP_URL=http://localhost
DB_CONNECTION=sqlite
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
MAIL_MAILER=log
```

JWT variables are **not** in `.env.example` yet — add them after `php artisan jwt:secret`:

```env
JWT_SECRET= # generated, never commit
JWT_TTL=60
JWT_BLACKLIST_ENABLED=true
```

Security notes:

- Never commit `.env` or `JWT_SECRET`.
- Set `APP_DEBUG=false` and a strong `APP_KEY` in production.
- Set `AUTH_GUARD` / add an `api` guard if you introduce JWT guard config.

## API Endpoints

Base: `http://localhost:8000/api`. All `/customers/*` require auth header.

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/login` | No | Obtain JWT |
| GET | `/api/customers` | Bearer | List customers |
| POST | `/api/customers` | Bearer | Create customer |
| GET | `/api/customers/{id}` | Bearer | Show one customer |
| PUT / PATCH | `/api/customers/{id}` | Bearer | Update customer |
| DELETE | `/api/customers/{id}` | Bearer | Delete customer |

Required headers for protected routes:

```http
Accept: application/json
Authorization: Bearer <jwt>
```

## Authentication

Login (public):

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

Success `200` (actual `AuthController` shape):

```json
{ "token": "<jwt>" }
```

Failure `401`:

```json
{ "error": "Unauthorized" }
```

Use the token:

```bash
curl http://localhost:8000/api/customers \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <jwt>"
```

Seeded login user (from `DatabaseSeeder` + `UserFactory`, password is `password`):

```json
{ "name": "Test User", "email": "test@example.com" }
```

## Request / Response Formats

**Create customer** `POST /api/customers`:

```bash
curl -X POST http://localhost:8000/api/customers \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <jwt>" \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","phone":"017000000"}'
```

Validation rules (actual `CustomerController@store`):

```php
'name'  => 'required',
'email' => 'required|email',
'phone' => 'required'
```

- `update` currently uses `$request->all()` with no validation.
- `show/update/delete` use `findOrFail` — missing IDs return 404.
- Unauthenticated access returns (actual `JwtAuthMiddleware`):

```json
{ "status": false, "message": "Unauthorized" }
```

- Validation failure returns Laravel default `422` with `errors` object.
- Duplicate `email` fails at DB level — no unique rule yet.

`customers` schema (actual migration):

```text
id: integer PK, name: string, email: string unique, phone: string, created_at, updated_at
```

## Testing

```bash
php artisan test
# or
./vendor/bin/phpunit --testsuite=Unit,Feature
```

Current coverage (verified, not invented):

- `tests/Feature/CustomerTest::test_customer_list` — asserts `GET /api/customers` returns `401` when unauthenticated.
- `tests/Unit/CustomerServiceTest::test_example` — placeholder `assertTrue(true)`.
- `phpunit.xml` uses `sqlite :memory:`, array cache/session/queue for isolation.

> Test execution in audit environment: PHP runtime was not available, so tests were **not run**. Run locally and paste results into your PR/LinkedIn post.

## Project Structure

```text
app/Http/Controllers/Api/AuthController.php
app/Http/Controllers/Api/CustomerController.php
app/Http/Controllers/Middleware/JwtAuthMiddleware.php
app/Services/CustomerService.php
app/Repositories/CustomerRepository.php
app/Repositories/CustomerRepositoryInterface.php
app/Models/User.php / Customer.php
app/Providers/AppServiceProvider.php
routes/api.php / bootstrap/app.php
config/auth.php / config/jwt.php
database/migrations/*_create_users_table.php
database/migrations/*_create_customers_table.php
database/seeders/DatabaseSeeder.php / CustomerSeeder.php
tests/Feature/CustomerTest.php / tests/Unit/CustomerServiceTest.php
```

## Known Limitations

Disclosed honestly — fix before calling production-ready:

1. Route uses `jwt:api` but `bootstrap/app.php` registers alias `jwt.auth` — missing middleware error.
2. Middleware file lives at `app/Http/Controllers/Middleware/` but namespace is `App\Http\Middleware` — PSR-4 mismatch.
3. `CustomerController::__construct` calls `$this->middleware('auth:api')`, but Laravel 12 base `Controller` has no such method — use route middleware or `HasMiddleware`.
4. `config/auth.php` defines only `web/session` guard, no `api/jwt` guard.
5. `Customer` model has no `$fillable` — `Customer::create()` will throw `MassAssignmentException`.
6. `CustomerSeeder` is not called from `DatabaseSeeder`; `.env.example` lacks `JWT_*` keys.
7. No FormRequests, API Resources, pagination, authorization, throttling, or logout/refresh.

## Roadmap

- Fix guard + middleware wiring, add `$fillable`, wire seeder
- Add `StoreCustomerRequest` / `UpdateCustomerRequest`, `CustomerResource`, JSON 404 handler
- Add Feature tests for login, CRUD happy paths, 422/401/404 cases
- Add `JWT_*` to `.env.example`, Postman collection, GitHub Actions (`pint --test`, `phpunit`), `LICENSE`

## License / Author

MIT (Laravel skeleton default). Maintained by [Kazi-Bablu](https://github.com/Kazi-Bablu) — `https://github.com/Kazi-Bablu/laravel-api.git`. Stars and contributions welcome.


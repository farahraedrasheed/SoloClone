# Solo Clone API

A REST API backend for a content browsing/watchlist platform ("Solo Clone"), built with Laravel and Laravel Sanctum. The API is JSON-only — no server-rendered views — and is designed to be consumed by a separate frontend (Vanilla JS / HTML / CSS).

## Tech Stack

| Layer | Technology |
|---|---|
| Backend Framework | Laravel 13 |
| Language | PHP 8.2+ |
| Database | MySQL / MariaDB |
| ORM | Eloquent |
| Authentication | Laravel Sanctum (API token auth) |
| API Format | REST (JSON) |
| Testing | PHPUnit (feature tests) |

## Features

- **Auth** — register, login, logout with Sanctum-issued API tokens, rate-limited against brute force.
- **Content browsing** — list, search/filter, and view content details (public, no auth required).
- **Cart & Watchlist** — authenticated users can add/remove/list content in a cart and a watchlist, with per-user isolation.

## Database Schema

| Table | Purpose |
|---|---|
| `users` | Standard Laravel users table |
| `contents` | `title`, `thumbnail`, `description`, `category`, `slug` (unique) |
| `user_actions` | Cart + Watchlist in one table: `user_id`, `content_id`, `action_type` (`cart` \| `watchlist`), unique per (user, content, type) |

## Requirements

- PHP 8.2+
- Composer
- MySQL or MariaDB

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=solo_clone
DB_USERNAME=root
DB_PASSWORD=
```

Create the database, then run migrations:

```bash
php artisan migrate
```

Start the dev server:

```bash
php artisan serve
```

## API Endpoints

### Auth

| Method | Endpoint | Auth |
|---|---|---|
| POST | `/api/register` | ❌ |
| POST | `/api/login` | ❌ |
| POST | `/api/logout` | ✅ |

### Content

| Method | Endpoint | Auth |
|---|---|---|
| GET | `/api/contents` | ❌ |
| GET | `/api/contents/search?q=` | ❌ |
| GET | `/api/contents/{slug}` | ❌ |

### Cart / Watchlist

| Method | Endpoint | Auth |
|---|---|---|
| GET | `/api/cart` | ✅ |
| POST | `/api/cart/{content}` | ✅ |
| DELETE | `/api/cart/{content}` | ✅ |
| GET | `/api/watchlist` | ✅ |
| POST | `/api/watchlist/{content}` | ✅ |
| DELETE | `/api/watchlist/{content}` | ✅ |

Authenticated requests use a Bearer token from `/api/register` or `/api/login`:

```
Authorization: Bearer <token>
```

## Testing

Tests run against a separate `solo_clone_testing` MySQL database (configured in `phpunit.xml`).

```bash
php artisan test
```

Test coverage includes registration/login/logout, route protection, and cart/watchlist persistence and per-user isolation.

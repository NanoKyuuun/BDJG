# BDJG — Creative Studio Website & Studio Management System

## Prerequisites

- PHP 8.5+
- Node.js 24+
- pnpm 11+
- Composer 2.x
- Docker (for MySQL + Redis)

## Quick Start

```bash
# 1. Start infrastructure
docker compose up -d

# 2. Install backend deps
cd apps/api
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
cd ../..

# 3. Install frontend deps
pnpm install

# 4. Run dev servers
pnpm dev:api    # Laravel at :8000
pnpm dev:web    # Next.js at :3000
```

## Health Check

```bash
curl http://localhost:8000/api/health
# {"status":"ok"}
```

## Commands

| Command | What |
|---|---|
| `pnpm dev:api` | Start Laravel API server |
| `pnpm dev:web` | Start Next.js dev server |
| `pnpm build` | Build Next.js for production |
| `pnpm lint` | Lint frontend |
| `pnpm typecheck` | Typecheck frontend |
| `cd apps/api && vendor/bin/pest` | Run backend tests |

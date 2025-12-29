# Bluesky Scheduler

A full-stack Bluesky scheduling tool built with Laravel 11 (API-only) and React 18 + Vite. The API links multiple Bluesky accounts, stores app-password credentials securely, and dispatches scheduled posts through a queued worker. The React client provides an architected dashboard to link accounts, compose posts, and monitor the queue.

## Architecture

| Layer | Tech | Notes |
| --- | --- | --- |
| API | Laravel 11, PostgreSQL, Queue worker | Endpoints live in [api/routes/api.php](api/routes/api.php); background publishing handled by [PublishScheduledPost](api/app/Jobs/PublishScheduledPost.php). |
| Frontend | React 18, Vite, Tailwind CSS, TanStack Query | UI shell in [web/src/App.tsx](web/src/App.tsx) with reusable components under `web/src/components`. |
| Containerization | Docker Compose | `docker-compose.yml` spins up Postgres, API, queue worker, and Vite dev server. |

## Local development

### 1. Environment

```bash
cp api/.env.example api/.env
cp web/.env.example web/.env
```

Update the following keys:

- `api/.env`: `APP_KEY` (run `php artisan key:generate` if blank), `BLUESKY_BASE_URL`, and the `BLUESKY_APP_PASSWORD` you plan to use for testing.
- `web/.env`: `VITE_API_BASE_URL` (defaults to `http://localhost:8000/api/v1`).

### 2. Dependencies

```bash
cd api && composer install && php artisan key:generate
cd ../web && npm install
```

### 3. Database

Laravel defaults to PostgreSQL (`DB_CONNECTION=pgsql`). Update credentials to match your setup or run Postgres via Docker (see below). For quick prototyping, switch to SQLite by setting `DB_CONNECTION=sqlite` and `DB_DATABASE=database/database.sqlite`.

Run migrations:

```bash
cd api
php artisan migrate
```

### 4. Docker workflow (optional but recommended)

```bash
docker compose up -d postgres
docker compose run --rm api composer install
docker compose run --rm api php artisan migrate --seed
docker compose up --build
```

Services exposed:

- API: http://localhost:8000
- Frontend: http://localhost:5173
- Postgres: localhost:5432 (user `bluesky`, password `secret`)

### 5. Running locally without Docker

```bash
# Terminal 1 (API & queue)
cd api
php artisan serve --host=0.0.0.0 --port=8000
php artisan queue:work --queue=scheduled-posts

# Terminal 2 (Vite)
cd web
npm run dev
```

> **Queue/DB tip:** When running artisan commands on the host, ensure `api/.env` uses `DB_HOST=127.0.0.1` (or another resolvable hostname). The Docker services override this internally with `DB_HOST=postgres`, but host processes need the published port. If you prefer to let Docker handle the worker, simply run `docker compose up queue` instead of the local `queue:work` command.

## API surface

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/api/v1/accounts` | List linked Bluesky accounts. |
| `POST` | `/api/v1/accounts` | Link a new account using handle + app password. |
| `POST` | `/api/v1/accounts/{account}/refresh` | Refresh and persist tokens. |
| `DELETE` | `/api/v1/accounts/{account}` | Remove an account. |
| `GET` | `/api/v1/schedules` | List all scheduled posts. |
| `POST` | `/api/v1/schedules` | Schedule a new post. |
| `POST` | `/api/v1/schedules/{scheduledPost}/send` | Force-send immediately. |
| `DELETE` | `/api/v1/schedules/{scheduledPost}` | Cancel a pending post. |

Key internals:

- Validation rules live in [StoreBlueskyAccountRequest](api/app/Http/Requests/StoreBlueskyAccountRequest.php) and [StoreScheduledPostRequest](api/app/Http/Requests/StoreScheduledPostRequest.php).
- Scheduled dispatches run via [PublishScheduledPost](api/app/Jobs/PublishScheduledPost.php) and the rescue command [bluesky:dispatch-due](api/app/Console/Commands/DispatchDueSchedules.php). Add the command to your supervisor/cron: `* * * * * php artisan bluesky:dispatch-due`.

## Frontend scripts

```bash
cd web
npm run dev     # start Vite with fast refresh
npm run build   # type-check + production build
npm run lint    # eslint
```

The React client pulls data through `@tanstack/react-query`, hitting the JSON API via the Axios helper in [web/src/api/client.ts](web/src/api/client.ts). Styling uses Tailwind with a custom palette defined in [web/tailwind.config.js](web/tailwind.config.js).

## Testing

```bash
cd api
php artisan test

cd ../web
npm run build   # tsc + vite build ensures type-safety
```

Feature coverage includes account linking, token refresh, and schedule dispatch flows (`tests/Feature/Api`). Extend these tests as endpoints evolve.

## Deployment notes

1. **Queue worker**: Run `php artisan queue:work --queue=scheduled-posts` (consider Horizon or Supervisor for resilience).
2. **Scheduler**: Register the `bluesky:dispatch-due` command every minute to requeue overdue posts.
3. **Secrets**: Never log Bluesky app passwords. They are stored encrypted via Laravel's `encrypted` cast; rotate keys if compromised.
4. **Frontend**: Point `VITE_API_BASE_URL` to the deployed API (e.g., `https://api.example.com/api/v1`) before running `npm run build`.

---

Need to debug or extend the workflow? See the inline comments within the controllers, jobs, and React hooks for guidance.

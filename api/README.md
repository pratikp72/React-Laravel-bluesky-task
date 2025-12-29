## Bluesky Scheduler API

Laravel 11 API that manages Bluesky account sessions and publishes scheduled posts through the official Bluesky AT Protocol endpoints.

### Key paths

- Routing: [routes/api.php](routes/api.php)
- Controllers: [App\Http\Controllers\Api](app/Http/Controllers/Api)
- Requests & validation: [App\Http\Requests](app/Http/Requests)
- Queue job: [PublishScheduledPost](app/Jobs/PublishScheduledPost.php)
- Rescue command: [DispatchDueSchedules](app/Console/Commands/DispatchDueSchedules.php)

### Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
```

Set the following in `.env`:

| Key | Description |
| --- | --- |
| `BLUESKY_BASE_URL` | API host, defaults to `https://bsky.social`. |
| `BLUESKY_HTTP_TIMEOUT` | HTTP timeout (seconds) for Bluesky requests. |
| `FRONTEND_URL` / `CORS_ALLOWED_ORIGINS` | Comma-separated origins allowed to hit the API. |
| `QUEUE_CONNECTION` | Use `database`, `redis`, or `sqs` depending on infra. |

> **Host vs Docker:** If you run artisan commands directly on your machine (outside Docker), point `DB_HOST` to `127.0.0.1` so the queue worker can reach the published Postgres port. The Docker services already override `DB_HOST` with `postgres`, so no changes are required inside containers.

### Commands & workflows

```bash
php artisan serve --host=0.0.0.0 --port=8000   # API server
php artisan queue:work --queue=scheduled-posts  # Publish posts
php artisan bluesky:dispatch-due                # Rescan for overdue posts
php artisan test                                # Run feature + unit tests
```

Add the rescue command to cron every minute:

```
* * * * * php /var/www/html/artisan bluesky:dispatch-due >> /var/log/cron.log 2>&1
```

### Testing

`php artisan test` covers:

- Linking accounts + token refresh ([tests/Feature/Api/BlueskyAccountTest.php](tests/Feature/Api/BlueskyAccountTest.php))
- Schedule creation + manual send ([tests/Feature/Api/ScheduledPostTest.php](tests/Feature/Api/ScheduledPostTest.php))

SQLite in-memory is used automatically during the test suite via `phpunit.xml`.

### Deployment checklist

1. Configure a persistent cache + queue backend (Redis or SQL).
2. Run at least one queue worker dedicated to the `scheduled-posts` queue.
3. Schedule `bluesky:dispatch-due` to catch any posts that were not queued.
4. Keep `APP_KEY` and encryption keys safe; revoke Bluesky app passwords if leaked.

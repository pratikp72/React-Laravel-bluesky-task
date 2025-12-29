# Bluesky Scheduler Web

React 18 + Vite interface for linking Bluesky accounts and orchestrating scheduled posts through the Laravel API.

## Available scripts

```bash
npm run dev      # Vite dev server with React Compiler enabled
npm run build    # Type-check + production bundle
npm run lint     # ESLint (flat config)
```

## Environment variables

| Key | Description |
| --- | --- |
| `VITE_API_BASE_URL` | Base URL for the Laravel API. Defaults to `http://localhost:8000/api/v1`. |

Copy `.env.example` to `.env` and adjust as needed before running any scripts.

## Data & state

- HTTP client lives in [src/api/client.ts](src/api/client.ts) (Axios + interceptors).
- `@tanstack/react-query` handles caching/mutations (`useAccounts` & `useSchedules`).
- Forms use `react-hook-form` + `zod` validation.
- Tailwind configuration + palette defined in [tailwind.config.js](tailwind.config.js).

## Design language

- Typography uses Space Grotesk + IBM Plex Sans with a neon palette.
- Layout and motion rely on Tailwind utility classes; see `glass-panel` helpers in [src/index.css](src/index.css).
- Components live in `src/components` and are composed in [src/App.tsx](src/App.tsx).

## Building for production

1. Ensure `VITE_API_BASE_URL` points to the deployed API (e.g., `https://api.example.com/api/v1`).
2. Run `npm run build` to emit `dist/`.
3. Serve the static bundle behind any CDN or drop it into Laravel's `public` directory if desired.

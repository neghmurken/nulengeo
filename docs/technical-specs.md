# Nulengeo — Technical Specification

This document locks the architecture, stack, and libraries needed to bootstrap implementation. It complements [functional-specs-fr.md](./functional-specs-fr.md), which defines game rules and scope.

## Architecture

Decoupled architecture: a PHP JSON API backend and a standalone TypeScript/React single-page application, communicating over HTTP. The map/round interaction is the entire product surface, which favors a client-heavy SPA over server-rendered pages.

Monorepo, single git repository, two top-level apps:

```
apps/
  api/   — Symfony JSON API
  web/   — React SPA
```

No shared package between the two apps at this stage; each has its own dependency manifest (`composer.json`, `package.json`).

## Backend (`apps/api`)

### Framework

Symfony, latest stable release, installed from `symfony/skeleton`. No API Platform: the API surface is a small set of RPC-style actions (start a game, submit a guess, advance to the next city, get results) rather than CRUD over resources, so API Platform's resource/CRUD conventions and auto-generated OpenAPI/Hydra layer would add more ceremony than value. Controllers and routes are hand-written.

REST-ish JSON over HTTP was chosen over GraphQL: there is a single consumer (the SPA) and no relational/nested querying need that GraphQL's flexible query shape would solve — it would add a schema/resolver layer for no real gain, and fits this domain even worse than API Platform did.

### Data store

SQLite, no database server. The city dataset is static, read-only reference data — a DB server process (with associated docker-compose service, connection pooling, etc.) isn't justified for it. Access via **Doctrine DBAL only** (no ORM/entities/UnitOfWork): a single reference table doesn't need entity mapping, and DBAL's query builder still gives clean, parameterized SQL without ORM overhead.

Schema is versioned with **Doctrine Migrations**, used standalone against DBAL (it doesn't require the full ORM). This keeps schema changes tracked and repeatable without requiring entity mapping.

### City dataset ingestion

Source: the [data.gouv.fr "Communes et villes de France"](https://www.data.gouv.fr/datasets/communes-et-villes-de-france-en-csv-excel-json-parquet-et-feather) CSV, a single file covering everything needed — no join between separate population/geo datasets required.

Current direct download (2026 edition): `https://www.data.gouv.fr/api/1/datasets/r/c63fd0b1-7987-46f6-b779-8b3ed889090c`

Columns used:

 - `code_insee` — commune identifier
 - `nom_standard` — commune name
 - `population` — municipal population
 - `latitude_centre` / `longitude_centre` — commune territory centroid (the "true position" anchor per the functional spec)

A Symfony console command (`app:city:import`) fetches this CSV, filters to metropolitan communes (excluding overseas departments/collectivities, whose INSEE codes start with department prefix `97`/`98`) with population > 5,000, ranks them by population to tag the 30 most populous as the fixed-size `huge` tier, then computes the population tier for the rest (Small/Medium/Large per the functional spec, using a strict-lower/inclusive-upper split at the 20,000/80,000 thresholds), and populates the SQLite table (existing rows are replaced, so re-running the command is idempotent).

The downloaded CSV is cached on disk (not committed to the repo — gitignored under `var/`) so repeated runs don't re-fetch it. `app:city:import --force` bypasses the cache and re-downloads, for picking up a newer INSEE-derived release. The source URL and cache path are Symfony container parameters (`app.city_import_csv_url`, `app.city_import_cache_path` in `services.yaml`), not hardcoded, so either can be overridden without touching the command.

 - HTTP fetch: `symfony/http-client`
 - CSV parsing: `league/csv` (column access by header name — safer than index-based `fgetcsv()` against a 62-column source file)

City draw for a game round: `SELECT ... WHERE tier = ? ORDER BY RANDOM() LIMIT 6` — SQLite performs the sampling directly rather than loading the full tier pool into PHP memory.

### Session & game state

Server-side session (native Symfony session, cookie-based, file storage backend for this version). The server holds the drawn city list, current round index, and running score; the client only receives a city's true position and distance/score after a guess is submitted for that round. No accounts, no login — sessions are anonymous and ephemeral, consistent with the functional spec's session-only scope for this version. Starting a new game always overwrites any game already in the session — nothing persists, so there is no in-progress state worth protecting.

Game state (`App\Model\GameState`) is a small immutable domain object, not a passive DTO: it owns the round state machine — `answerRound()` and `advance()` — and throws a domain exception on illegal transitions (guessing twice, advancing before guessing, acting on a finished game). `App\Repository\GameStates` only loads/saves it to/from the session; the absence of a stored game is its own domain exception (`NoActiveGameException`).

Six RPC-style routes, all under `/api/games`:

 - `POST /api/games` — start a game (`{mode: 'easy'|'medium'|'hard'|'expert'}`, mapped to city tier `huge`/`large`/`medium`/`small`)
 - `POST /api/games/guess` — submit a guess for the current round (`{latitude, longitude}`)
 - `POST /api/games/next` — advance to the next round (finishes the game after the last one)
 - `GET /api/games/current` — current game state; the frontend's single source of truth for which screen to show (`idle` / `playing` / `finished`)
 - `DELETE /api/games/current` — abandon the current game, clearing it from the session; used by the "give up" and "back to menu" actions so a page refresh afterwards reports `idle` instead of resurrecting the abandoned game
 - `GET /api/games/modes` — list the available modes, sourced from the `Mode` enum; the frontend fetches this rather than hardcoding the mode list, so adding/renaming a mode is a backend-only change

Every endpoint returns the same envelope shape, keyed by `status`; the four mutating endpoints return the fresh state directly rather than requiring a follow-up `GET /current`. `GET /current` reports `idle` (not an error) when the session holds no game — "no game yet" is a fresh session's default state, not an exceptional one.

Round scoring uses `App\Service\Score\Calculator` (haversine distance + the exponential score formula from the functional spec). `app.game_max_score`, `app.game_calibration_distance_km`, and `app.game_round_count` are Symfony parameters (`services.yaml`) — game-balance numbers, deliberately overridable without a code change. The `ln(2)` decay base stays a literal: it's mathematically derived from the calibration distance, not an independent tunable.

### Cross-origin requests

Not needed for now. The development environment routes both apps under a single host (`nulengeo.localhost`) via Traefik, path-based (see [Development environment](#development-environment)), so the SPA and the API share the same origin and no CORS bundle is required. `nelmio/cors-bundle` gets added if a cross-origin deployment topology (separate API/web domains in production) is decided later.

### Error format

RFC 7807 (Problem Details for HTTP APIs), via Symfony's built-in `ErrorController`/normalizers. Gives the frontend one predictable error shape across every endpoint.

### Testing & quality tooling

 - Tests: PHPUnit
 - Static analysis: PHPStan
 - Code style: PHP-CS-Fixer

## Frontend (`apps/web`)

### Stack

 - React + TypeScript
 - Build tool: Vite
 - Package manager: Yarn

### State & navigation

No routing library. The app is a single linear flow (menu → mode select → round loop → results) with no bookmarkable/deep-linkable intermediate states, so a top-level state machine (`idle` / `playing` / `finished`) drives which screen renders, using React's built-in `useState`/`useReducer` + `Context`. No Redux/Zustand/TanStack Query: the API surface is a handful of sequential, non-cacheable game-progression calls, not overlapping/cached server data.

HTTP calls use native `fetch` (with `credentials: 'include'` for the session cookie) — the API surface is small enough that axios's interceptor/cancellation conveniences aren't worth the extra dependency.

### Styling

CSS Modules — scoped plain CSS, no extra build dependency, sufficient for the app's fairly simple UI (map, prompt banner, score display, results screen).

### Internationalization

`react-i18next`. Per the functional spec, the UI is structured for i18n from day one even though only French ships initially — JSON translation files per locale, adding a second language later is just a new file.

### Map

 - Library: **MapLibre GL JS** (vector-tile based, GPU-rendered, full control over a custom style to show only relief/hydrography and strip all labels/roads/borders/POIs).
 - Tile source: **MapTiler**, third-party hosted (not self-hosted PMTiles). A custom, zoom-capped, trimmed self-hosted extract was estimated at tens–a few hundred MB, but hosted third-party tiles were chosen to avoid the tile-build pipeline.
 - API key: embedded client-side, restricted by domain in MapTiler's dashboard. This is standard practice for map tile providers (the key is a quota/billing identifier, not a secret) and avoids proxying every tile request through the backend.

### Testing & quality tooling

 - Tests: Vitest + React Testing Library (Vitest shares Vite's config/transform pipeline)
 - Lint/format: ESLint + Prettier

## Development environment

Docker Compose, using **FrankenPHP** for the PHP side (Symfony's officially recommended modern runtime — single binary built on Caddy). No separate nginx + PHP-FPM pair. `apps/api`'s Dockerfile is a lean, hand-written, dev-only build (`dunglas/frankenphp:1-php8.4` + the extensions Symfony needs) rather than the `dunglas/symfony-docker` community template — that template bundles Mercure/Vulcain/worker-mode support this project doesn't use, so a custom Dockerfile is shorter and has nothing to strip. `apps/web`'s Dockerfile is similarly a single dev-only stage (`node:24-alpine`, Yarn Berry) running the Vite dev server. Neither app publishes a host port directly; both are reachable only through Traefik.

A project-local **Traefik** (v3) reverse proxy fronts both apps over plain HTTP (no TLS — no production target yet) on a single host, `nulengeo.localhost`, path-based: `/` routes to `web`, `/api/*` routes to `api` (Symfony routes keep the `/api` prefix baked in, no prefix-stripping). This keeps the SPA and API same-origin in development, which is why CORS isn't needed (see [Cross-origin requests](#cross-origin-requests)). Traefik's dashboard is exposed on port 8080 for debugging routes (dev only, `--api.insecure=true`).

### Task automation

A root-level `Taskfile.yml` (`go-task`) provides a single cross-cutting entrypoint over Docker Compose, Composer, and Yarn, so contributors don't need to remember per-tool invocations (e.g. `docker compose exec api bin/console ...` vs `yarn --cwd apps/web ...`). Per-app scripts still live in `composer.json`/`package.json`; the Taskfile wraps them plus Docker orchestration:

 - `task build` — build the Docker Compose stack images
 - `task up` / `task down` — start/stop the Docker Compose stack
 - `task import` — run the city dataset import command inside the API container
 - `task migrate` — run Doctrine Migrations inside the API container
 - `task test` — run both test suites (PHPUnit + Vitest)
 - `task lint` — run both lint/static-analysis chains (PHPStan + PHP-CS-Fixer, ESLint + Prettier)

## Out of scope for this version

 - **CI pipeline**: none for now; checks (PHPUnit/PHPStan/PHP-CS-Fixer, Vitest/ESLint) run locally only.
 - **Production deployment target**: not needed to start implementation; a local Docker Compose setup is enough to build and iterate on the game. Revisit once there's something working to deploy.

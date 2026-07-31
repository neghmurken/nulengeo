# Nulengeo

Geography quiz game: place French cities on a stripped-down topographic map (no names, no roads, no labels) as close as possible to their real location.

- Game rules: [functional-specs-fr.md](./functional-specs-fr.md) (French)
- Architecture & stack: [technical-specs.md](./technical-specs.md)

## Stack

- **API** (`apps/api`): Symfony, SQLite via Doctrine DBAL, MapLibre-fed by MapTiler on the frontend side.
- **Web** (`apps/web`): React + TypeScript, Vite, MapLibre GL JS.
- **Dev environment**: Docker Compose (FrankenPHP), orchestrated via a root `Taskfile.yml`.

## Quick start

```bash
task up        # start the Docker Compose stack
task migrate   # create the SQLite schema
task import    # import the city dataset
task test      # run both test suites
```

See [technical-specs.md](./technical-specs.md) for details on every command and design decision.

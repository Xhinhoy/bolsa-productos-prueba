.RECIPEPREFIX = >
.PHONY: up down fresh logs test lint

up:
> docker compose up -d --build

down:
> docker compose down

fresh:
> docker compose down -v && docker compose up -d --build

logs:
> docker compose logs -f

test:
> docker build --target test -t app-test ./app && docker run --rm app-test
> docker build --target test -t wm-test ./watermark-service && docker run --rm wm-test sh -c "ruff check . && mypy app && python -m pytest -q"

lint:
> docker run --rm app-test vendor/bin/pint --test
> docker run --rm app-test vendor/bin/phpstan analyse --memory-limit=512M

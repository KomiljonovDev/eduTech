# EduTech Docker Makefile

.PHONY: help build up down restart logs shell mysql redis fresh migrate seed test lint

# Default target
help:
	@echo "EduTech Docker Commands:"
	@echo ""
	@echo "  make build      - Build Docker images"
	@echo "  make up         - Start containers"
	@echo "  make down       - Stop containers"
	@echo "  make restart    - Restart containers"
	@echo "  make logs       - View container logs"
	@echo "  make shell      - Open shell in app container"
	@echo "  make mysql      - Open MySQL CLI"
	@echo "  make redis      - Open Redis CLI"
	@echo ""
	@echo "  make install    - First time setup"
	@echo "  make fresh      - Fresh migrate with seed"
	@echo "  make migrate    - Run migrations"
	@echo "  make seed       - Run seeders"
	@echo "  make test       - Run tests"
	@echo "  make lint       - Run Pint linter"
	@echo ""
	@echo "  make dev        - Start with Vite dev server"
	@echo "  make prod       - Production build"

# Docker commands
build:
	docker compose build

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

logs:
	docker compose logs -f

logs-app:
	docker compose logs -f app

logs-queue:
	docker compose logs -f queue

# Shell access
shell:
	docker compose exec app sh

shell-root:
	docker compose exec -u root app sh

mysql:
	docker compose exec mysql mysql -u edutech -psecret edutech

redis:
	docker compose exec redis redis-cli

# Laravel commands
install: build
	cp .env.docker .env
	docker compose up -d
	docker compose exec app composer install
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate --seed
	docker compose exec app php artisan storage:link
	@echo ""
	@echo "Installation complete! Visit http://localhost:8080"

fresh:
	docker compose exec app php artisan migrate:fresh --seed

migrate:
	docker compose exec app php artisan migrate

seed:
	docker compose exec app php artisan db:seed

test:
	docker compose exec app php artisan test

lint:
	docker compose exec app vendor/bin/pint

# Artisan shortcut
artisan:
	docker compose exec app php artisan $(filter-out $@,$(MAKECMDGOALS))

# Composer shortcut
composer:
	docker compose exec app composer $(filter-out $@,$(MAKECMDGOALS))

# Development with Vite
dev:
	docker compose --profile dev up -d

# Production build
prod:
	docker compose exec app npm install
	docker compose exec app npm run build

# Cache commands
cache-clear:
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan route:clear
	docker compose exec app php artisan view:clear

cache-warm:
	docker compose exec app php artisan config:cache
	docker compose exec app php artisan route:cache
	docker compose exec app php artisan view:cache

# Horizon (if using)
horizon:
	docker compose exec app php artisan horizon

# Permissions fix
permissions:
	docker compose exec -u root app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
	docker compose exec -u root app chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Clean everything
clean:
	docker compose down -v --remove-orphans
	docker system prune -f

%:
	@:

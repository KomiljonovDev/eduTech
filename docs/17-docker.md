# Docker

Loyihani Docker konteynerlarida ishga tushirish.

## Konteynerlar

| Konteyner | Image | Port | Tavsif |
|-----------|-------|------|--------|
| `edutech_app` | PHP 8.3-FPM Alpine | 9000 (internal) | Laravel application |
| `edutech_nginx` | Nginx Alpine | 8080 | Web server |
| `edutech_mysql` | MySQL 8.0 | 3307 | Database |
| `edutech_redis` | Redis Alpine | 6380 | Cache & Queue |
| `edutech_queue` | PHP 8.3-FPM Alpine | - | Queue worker |
| `edutech_scheduler` | PHP 8.3-FPM Alpine | - | Task scheduler |
| `edutech_node` | Node 20 Alpine | 5174 | Vite dev server |

## Tezkor Boshlash

```bash
# Birinchi marta o'rnatish
make install

# Yoki qo'lda
cp .env.docker .env
docker compose build
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

## Asosiy Buyruqlar

### Docker

```bash
# Konteynerlarni ishga tushirish
make up
# yoki
docker compose up -d

# To'xtatish
make down
# yoki
docker compose down

# Qayta ishga tushirish
make restart

# Loglarni ko'rish
make logs
make logs-app
make logs-queue
```

### Shell Kirish

```bash
# App konteyneriga kirish
make shell
# yoki
docker compose exec app sh

# Root sifatida
make shell-root

# MySQL CLI
make mysql

# Redis CLI
make redis
```

### Laravel Buyruqlari

```bash
# Migratsiya
make migrate

# Seed
make seed

# Fresh migrate + seed
make fresh

# Testlar
make test

# Lint
make lint

# Cache tozalash
make cache-clear

# Cache yaratish (production)
make cache-warm
```

### Artisan va Composer

```bash
# Artisan buyruqlari
make artisan migrate:status
make artisan route:list
make artisan tinker

# Composer buyruqlari
make composer require laravel/horizon
make composer update
```

## Fayl Strukturasi

```
docker/
├── nginx/
│   └── default.conf      # Nginx konfiguratsiyasi
└── php/
    ├── Dockerfile        # PHP image
    └── local.ini         # PHP konfiguratsiyasi

docker-compose.yml        # Konteynerlar ta'rifi
.env.docker              # Docker uchun env namunasi
.dockerignore            # Docker ignore
Makefile                 # Qulay buyruqlar
```

## Environment

### .env.docker

```env
# Database
DB_CONNECTION=mysql
DB_HOST=mysql           # Konteyner nomi
DB_PORT=3306
DB_DATABASE=edutech
DB_USERNAME=edutech
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis        # Konteyner nomi
REDIS_PORT=6379

# Cache & Queue
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## Development

### Vite bilan

```bash
# Vite dev serverni ishga tushirish
make dev

# Yoki
docker compose --profile dev up -d
```

Vite `http://localhost:5174` da ishlaydi.

### Hot Reload

Kod o'zgarishlari avtomatik qo'llanadi (volume mount).

## Production

### Build

```bash
# Asset build
make prod

# Yoki
docker compose exec app npm install
docker compose exec app npm run build
```

### Optimizatsiya

```bash
# Cache yaratish
make cache-warm

# Yoki
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

### Production docker-compose.override.yml

```yaml
services:
  app:
    restart: always
    environment:
      - APP_ENV=production
      - APP_DEBUG=false

  nginx:
    restart: always

  mysql:
    restart: always

  redis:
    restart: always

  queue:
    restart: always
    command: php artisan queue:work --sleep=3 --tries=3 --max-time=3600

  # Node dev server o'chirilgan
  node:
    profiles:
      - never
```

## Troubleshooting

### Permission xatoligi

```bash
make permissions
# yoki
docker compose exec -u root app chown -R www-data:www-data /var/www/storage
docker compose exec -u root app chmod -R 775 /var/www/storage
```

### MySQL ulanish xatosi

```bash
# MySQL tayyor bo'lishini kutish
docker compose logs mysql

# Qayta urinish
docker compose restart app
```

### Portlar band

```bash
# Port band bo'lsa docker-compose.yml da o'zgartiring
# Hozirgi portlar:
# - Nginx: 8080
# - MySQL: 3307
# - Redis: 6380
# - Vite: 5174
```

### Tozalash

```bash
# Hamma narsani o'chirish
make clean
# yoki
docker compose down -v --remove-orphans
docker system prune -f
```

## Volumes

| Volume | Maqsad |
|--------|--------|
| `mysql_data` | MySQL ma'lumotlari |
| `redis_data` | Redis ma'lumotlari |

## Network

Barcha konteynerlar `edutech` network ichida. Konteynerlar bir-biriga nom orqali murojaat qiladi:
- `app` - PHP-FPM
- `mysql` - Database
- `redis` - Cache
- `nginx` - Web server

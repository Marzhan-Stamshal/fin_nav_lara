# Laravel Deploy (Plesk) - fin_nav_laravel

## 1. Upload
1. Загрузите папку `fin_nav_laravel` на новый субдомен.
2. В Plesk у домена выставьте document root на `public`.

## 2. Environment (.env)
Минимально:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain`
- `DB_CONNECTION=mysql`
- `DB_HOST=...`
- `DB_PORT=3306`
- `DB_DATABASE=...`
- `DB_USERNAME=...`
- `DB_PASSWORD=...`

## 3. Install / build
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 4. Permissions
Папки `storage` и `bootstrap/cache` должны быть writable.

## 5. Optional demo seed
```bash
php artisan db:seed --force
```
Demo login:
- `demo@finnav.local`
- `demo12345`

## 6. Backup/restore
CSV:
- export: `GET /loans/export/csv`
- sample: `GET /loans/export/sample-csv`
- import: форма на дашборде (`Импорт CSV`)

JSON:
- export: `GET /backup/export/json`
- import: форма на дашборде (`Импорт JSON`)
- опция `Заменить` очищает текущие данные перед восстановлением

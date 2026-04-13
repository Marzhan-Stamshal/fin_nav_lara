# Production Checklist (Fin Navigation Laravel)

## Before deploy
- [ ] Проверен `.env` (prod values)
- [ ] `APP_DEBUG=false`
- [ ] База данных доступна с сервера
- [ ] Выполнены миграции
- [ ] Секреты не в репозитории

## Deploy commands
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `php artisan migrate --force`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`

## Smoke test
- [ ] Регистрация работает
- [ ] Логин работает
- [ ] Добавление кредита работает
- [ ] Массовая отметка оплаты работает
- [ ] Экспорт CSV работает
- [ ] Импорт CSV работает
- [ ] Экспорт JSON backup работает
- [ ] Импорт JSON backup работает
- [ ] Страница графика оплат работает
- [ ] Страница настроек работает

## Post deploy
- [ ] Включен HTTPS
- [ ] Настроен backup БД
- [ ] Проверены логи ошибок
- [ ] Сделан тестовый JSON backup

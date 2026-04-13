# Fin Navigation (Laravel)

Приложение для учета кредитов в разных банках с расчетами досрочного закрытия, экономии, стратегий и массовых действий.

## Что уже есть
- Авторизация: регистрация, вход, выход
- Кредиты: создание, редактирование, удаление, карточка кредита
- Дашборд:
  - досрочно сейчас / полностью до конца / экономия
  - фильтры: банк, группа, статус, срок, min/max досрочного
  - сортировки
  - массовые действия: оплачено за месяц, закрыла досрочно
  - группы кредитов и суммы по группам
  - прогноз закрытия по банкам (раскрывающийся)
  - платежи за 30 дней
  - напоминания 3/1 день
  - календарь оплат
  - аналитика по банкам/типам
  - сценарий +X в этом месяце
  - оценка риска по доходу
- Платежи:
  - история
  - отдельная страница "График оплат" с чекбоксами и массовой отметкой
- Стратегии:
  - выборочные кредиты
  - рефинансирование
  - сохранение/удаление сценариев
- Экспорт/импорт:
  - CSV экспорт кредитов
  - CSV шаблон
  - CSV импорт
  - полный JSON backup/restore (кредиты+платежи+сценарии)
- Настройки:
  - профиль
  - смена пароля
  - валюта/локаль/доход

## Технологии
- Laravel 13
- PHP 8.3+
- MySQL
- Blade

## Локальный запуск
```bash
cd fin_nav_laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Открыть: `http://127.0.0.1:8000`

## Демо-данные
```bash
php artisan db:seed --force
```
Демо-вход:
- email: `demo@finnav.local`
- пароль: `demo12345`

## Основные web-страницы
- `/dashboard`
- `/loans/create`
- `/payments`
- `/payments/schedule`
- `/scenarios`
- `/settings`

## API (Laravel)
Базовый префикс: `/api`

Auth:
- `POST /api/auth/register`
- `POST /api/auth/login`
- `GET /api/auth/me`
- `POST /api/auth/logout`

Loans:
- `GET /api/loans`
- `POST /api/loans`
- `GET /api/loans/{loan}`
- `PUT /api/loans/{loan}`
- `DELETE /api/loans/{loan}`
- `GET /api/loans/{loan}/payments`
- `POST /api/loans/{loan}/payments`

Payments:
- `GET /api/payments`

## Backup / Import
CSV:
- export: `/loans/export/csv`
- sample: `/loans/export/sample-csv`
- import: форма "Импорт CSV" на дашборде

JSON:
- export: `/backup/export/json`
- import: форма "Импорт JSON" на дашборде
- чекбокс "Заменить" очищает текущие кредиты/платежи/сценарии перед восстановлением

## Деплой
См. файл [DEPLOY_PLESK.md](DEPLOY_PLESK.md)

## Production checklist
См. файл [PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md)

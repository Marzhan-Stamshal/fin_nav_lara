<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ФинНавигатор</title>
    <style>
        body { margin:0; font-family: Arial, Helvetica, sans-serif; background: linear-gradient(135deg, #eff6ff, #e0e7ff); color:#1f2937; }
        .container { max-width:1280px; margin:0 auto; padding:0 16px; }
        .nav { background:#fff; box-shadow:0 1px 2px rgba(0,0,0,.08); }
        .nav-inner { display:flex; justify-content:space-between; align-items:center; padding:16px 0; }
        .brand { font-size:30px; font-weight:700; }
        .links { display:flex; gap:12px; align-items:center; }
        .btn { display:inline-block; text-decoration:none; border-radius:10px; padding:10px 16px; font-weight:700; }
        .btn-primary { background:#4f46e5; color:#fff; }
        .btn-primary:hover { background:#4338ca; }
        .btn-light { border:2px solid #4f46e5; color:#4f46e5; background:#fff; }
        .hero { text-align:center; padding:72px 0 36px; }
        .hero h1 { font-size:52px; margin:0 0 16px; }
        .hero p { font-size:22px; color:#4b5563; margin:0 0 26px; }
        .features { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:18px; margin-top:42px; }
        .card { background:#fff; border-radius:12px; padding:24px; box-shadow:0 10px 25px -10px rgba(0,0,0,.2); }
        .more { margin-top:36px; }
        .more h2 { margin:0 0 16px; font-size:32px; text-align:center; }
        .list { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:14px; }
        .cta { background:#4f46e5; color:#fff; margin-top:36px; text-align:center; }
        .cta p { color:#e0e7ff; }
        .footer { text-align:center; color:#6b7280; padding:32px 0; font-size:14px; }
        @media (max-width: 900px) {
            .hero h1 { font-size:34px; }
            .hero p { font-size:18px; }
            .nav-inner { flex-wrap:wrap; gap:10px; }
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="container nav-inner">
            <div class="brand">ФинНавигатор</div>
            <div class="links">
                <a href="{{ route('login') }}" style="font-weight:700;color:#374151;">Вход</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Регистрация</a>
            </div>
        </div>
    </nav>

    <section class="container hero">
        <h1>Управляйте своими кредитами умнее</h1>
        <p>Видьте все свои кредиты в одном месте и находите оптимальную стратегию досрочного погашения</p>
        <div class="links" style="justify-content:center;">
            <a href="{{ route('register') }}" class="btn btn-primary">Начать бесплатно</a>
            <a href="{{ route('login') }}" class="btn btn-light">Войти</a>
        </div>

        <div class="features">
            <div class="card">
                <h3>Полная картина долгов</h3>
                <p style="color:#6b7280;">Все кредиты в разных банках с разными ставками и сроками в одном месте.</p>
            </div>
            <div class="card">
                <h3>Умные стратегии</h3>
                <p style="color:#6b7280;">Выберите лучший сценарий досрочного погашения и экономьте.</p>
            </div>
            <div class="card">
                <h3>Прогнозы и план</h3>
                <p style="color:#6b7280;">Понимайте когда закроете долги и сколько сэкономите.</p>
            </div>
        </div>

        <div class="card more">
            <h2>Что вы сможете делать</h2>
            <div class="list">
                <div>Добавлять кредиты вручную</div>
                <div>Видеть общий долг и платежи в месяц</div>
                <div>Сравнивать стратегии закрытия</div>
                <div>Отслеживать платежи</div>
                <div>Просматривать аналитику по банкам</div>
                <div>Планировать нагрузку на месяц</div>
            </div>
        </div>

        <div class="card cta">
            <h2>Готовы взять долги под контроль?</h2>
            <p>Зарегистрируйтесь бесплатно и начните управлять кредитами уже сегодня</p>
            <a href="{{ route('register') }}" class="btn" style="background:#fff;color:#4f46e5;">Создать аккаунт</a>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div>&copy; 2026 ФинНавигатор. Все права защищены.</div>
            <div style="margin-top:8px;">Важно: расчеты ориентировочные, точные условия уточняйте в банке.</div>
        </div>
    </footer>
</body>
</html>

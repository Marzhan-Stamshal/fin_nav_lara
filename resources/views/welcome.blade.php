<!doctype html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/icont.png" sizes="any">
    <title>ФинНавигатор</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap');

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Manrope, "Segoe UI", Arial, sans-serif;
            color: #0f172a;
            background:
                radial-gradient(circle at 10% 15%, #bfdbfe 0%, transparent 38%),
                radial-gradient(circle at 88% 20%, #c4b5fd 0%, transparent 32%),
                linear-gradient(155deg, #eef4ff 0%, #e0ecff 55%, #d9e5ff 100%);
        }

        .container {
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 18px;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 30;
            border-bottom: 1px solid #dbeafe;
            background: rgba(255, 255, 255, .82);
            backdrop-filter: blur(10px);
        }

        .topbar-inner {
            min-height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .brand img {

            height: 72px;
            border-radius: 10px;
            object-fit: cover;

        }

        .brand strong {
            font-size: 24px;
            letter-spacing: -0.02em;
        }

        .links {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            border: 0;
            border-radius: 11px;
            text-decoration: none;
            padding: 11px 15px;
            font-weight: 800;
            cursor: pointer;
        }

        .btn-primary {
            background: #1d4ed8;
            color: #fff;
            box-shadow: 0 10px 18px -14px rgba(29, 78, 216, .9);
        }

        .btn-light {
            background: #fff;
            color: #1e293b;
            border: 1px solid #cbd5e1;
        }

        .hero {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 18px;
            padding: 34px 0 14px;
        }

        .panel {
            background: rgba(255, 255, 255, .82);
            border: 1px solid #dbeafe;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 20px 30px -25px rgba(30, 64, 175, .4);
        }

        .label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 700;
            color: #1e40af;
            background: #dbeafe;
            border-radius: 999px;
            padding: 6px 10px;
        }

        h1 {
            margin: 14px 0 10px;
            font-size: clamp(30px, 5vw, 56px);
            line-height: 1.02;
            letter-spacing: -0.03em;
        }

        .sub {
            margin: 0;
            color: #475569;
            font-size: 17px;
            line-height: 1.5;
            max-width: 56ch;
        }

        .hero-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .visual {
            position: relative;
            overflow: hidden;
            min-height: 360px;
            background: linear-gradient(160deg, #1e3a8a, #3730a3 50%, #4f46e5);
            color: #fff;
        }

        .visual .glow {
            position: absolute;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            filter: blur(30px);
            opacity: .34;
        }

        .visual .g1 {
            background: #7dd3fc;
            top: -66px;
            right: -42px;
        }

        .visual .g2 {
            background: #c4b5fd;
            bottom: -80px;
            left: -36px;
        }

        .visual-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            position: relative;
            z-index: 2;
        }

        .visual-card {
            position: relative;
            z-index: 2;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, .2);
            background: rgba(255, 255, 255, .12);
            padding: 12px;
            margin-bottom: 8px;
        }

        .amount {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .mini {
            font-size: 12px;
            color: rgba(255, 255, 255, .85);
        }

        .chart {
            margin-top: 8px;
            display: grid;
            gap: 7px;
        }

        .bar {
            height: 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .18);
            overflow: hidden;
        }

        .bar>span {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, #93c5fd, #34d399);
        }

        .features {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin: 14px 0;
        }

        .feature {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #dbeafe;
            padding: 16px;
            box-shadow: 0 16px 24px -24px rgba(30, 64, 175, .45);
        }

        .feature h3 {
            margin: 0 0 8px;
            font-size: 19px;
            letter-spacing: -0.02em;
        }

        .feature p {
            margin: 0;
            color: #475569;
            font-size: 14px;
            line-height: 1.45;
        }

        .feature .icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            margin-bottom: 10px;
            background: #e0ecff;
        }

        .bottom-cta {
            margin: 14px 0 28px;
            background: #0f172a;
            color: #fff;
            border-radius: 18px;
            border: 1px solid #0b1220;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .bottom-cta strong {
            display: block;
            font-size: 24px;
            letter-spacing: -0.02em;
            margin-bottom: 4px;
        }

        .bottom-cta span {
            color: #cbd5e1;
            font-size: 14px;
        }

        .footer {
            text-align: center;
            color: #64748b;
            font-size: 13px;
            padding: 0 0 26px;
        }

        @media (max-width: 980px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .features {
                grid-template-columns: 1fr;
            }

            .topbar-inner {
                min-height: 64px;
            }

            .brand strong {
                font-size: 22px;
            }

            .btn {
                padding: 10px 12px;
            }

            .visual {
                min-height: auto;
            }
        }
    </style>
</head>

<body>
    <nav class="topbar">
        <div class="container topbar-inner">
            <a href="{{ route('home') }}" class="brand">
                <img src="{{ asset('icon.png') }}" alt="ФинНавигатор">
            </a>
            <div class="links">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">Перейти в кабинет</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-light">Вход</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Регистрация</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="container">
        <section class="hero">
            <article class="panel">
                <span class="label">Ваши кредиты под контролем</span>
                <h1>Красиво, понятно и без путаницы</h1>
                <p class="sub">Смотрите досрочную сумму, сколько останется платить до конца, какую экономию получите
                    и где можно закрыть быстрее.</p>
                <div class="hero-actions">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">Открыть дашборд</a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-primary">Начать бесплатно</a>
                        <a href="{{ route('login') }}" class="btn btn-light">У меня уже есть аккаунт</a>
                    @endauth
                </div>
            </article>

            <aside class="panel visual">
                <span class="glow g1"></span>
                <span class="glow g2"></span>
                <div class="visual-head">
                    <strong>Мой прогноз</strong>
                    <img src="{{ asset('icon.png') }}" alt="" style="width:28px;height:28px;border-radius:8px;">
                </div>
                <div class="visual-card">
                    <div class="mini">Экономия при закрытии сейчас</div>
                    <div class="amount">276 713 ₸</div>
                </div>
                <div class="visual-card">
                    <div class="mini">Досрочно сейчас</div>
                    <strong style="font-size:20px;">335 377 ₸</strong>
                    <div class="mini" style="margin-top:2px;">Если платить до конца: 612 090 ₸</div>
                    <div class="chart">
                        <div class="bar"><span style="width:74%;"></span></div>
                        <div class="bar"><span style="width:61%;"></span></div>
                        <div class="bar"><span style="width:42%;"></span></div>
                    </div>
                </div>
            </aside>
        </section>

        <section class="features">
            <div class="feature">
                <div class="icon">🏦</div>
                <h3>По всем банкам</h3>
                <p>Kaspi, Jusan, Home и другие в одном экране с общей суммой нагрузки в месяц.</p>
            </div>
            <div class="feature">
                <div class="icon">⚡</div>
                <h3>Фокус на досрочке</h3>
                <p>Главный акцент на сумме досрочного погашения и реальной выгоде здесь и сейчас.</p>
            </div>
            <div class="feature">
                <div class="icon">📊</div>
                <h3>Наглядная аналитика</h3>
                <p>Сценарии +X, прогнозы закрытия и группировка кредитов без сложных таблиц.</p>
            </div>
        </section>

        <section class="bottom-cta">
            <div>
                <strong>Начните наводить порядок в долгах уже сегодня</strong>
                <span>Регистрация займет меньше минуты, все данные остаются только у вас.</span>
            </div>
            <div class="links">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">В мой кабинет</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary">Создать аккаунт</a>
                @endauth
            </div>
        </section>
    </main>

    <footer class="footer">
        &copy; 2026 ФинНавигатор. Расчеты ориентировочные, проверяйте условия в банке.
    </footer>
</body>

</html>

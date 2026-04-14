<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Fin Navigation (Laravel)' }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap');
        :root { color-scheme: light; }
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; font-family: Manrope, "Segoe UI", Helvetica, Arial, sans-serif; color: #0f172a; background: linear-gradient(145deg, #e8efff, #dbeafe 46%, #c7d2fe); min-height: 100vh; }
        a { color: inherit; text-decoration: none; }
        .wrap { max-width: 1280px; margin: 0 auto; padding: 16px; }
        .nav { position: sticky; top: 0; z-index: 80; background: rgba(255,255,255,.82); backdrop-filter: blur(10px); box-shadow: 0 2px 12px rgba(15, 23, 42, .08); border-bottom: 1px solid #dbe4ff; }
        .nav-inner { max-width: 1280px; margin: 0 auto; padding: 10px 16px; display: grid; gap: 10px; }
        .nav-top { display: flex; gap: 10px; align-items: center; justify-content: space-between; }
        .nav-menu { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .nav-links { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .brand { display: inline-flex; align-items: center; }
        .brand-logo { height: 36px; width: auto; display: block; }
        .link { color: #374151; font-weight: 700; padding: 7px 10px; border-radius: 8px; }
        .link:hover { color: #1d4ed8; background: #eff6ff; }
        .profile-pill { display:inline-flex; align-items:center; gap:8px; background:#f8fbff; border:1px solid #dbe4ff; padding:6px 10px; border-radius:999px; color:#334155; font-size:13px; font-weight:700; }
        .nav-toggle { display: none; border: 1px solid #dbe4ff; background: #ffffff; color: #334155; border-radius: 10px; padding: 7px 11px; font-size: 18px; font-weight: 700; line-height: 1; cursor: pointer; }
        .nav-right { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .card { background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 10px 25px -10px rgba(0, 0, 0, .2); }
        .grid { display: grid; gap: 12px; }
        .grid > * { min-width: 0; }
        .grid-4 { grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); }
        .btn { border: 0; border-radius: 10px; padding: 10px 14px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #4f46e5; color: #fff; }
        .btn-primary:hover { background: #4338ca; }
        .btn-green { background: #059669; color: #fff; }
        .btn-green:hover { background: #047857; }
        .btn-orange { background: #ea580c; color: #fff; }
        .btn-orange:hover { background: #c2410c; }
        .btn-gray { background: #374151; color: #fff; }
        .btn-light { background: #e5e7eb; color: #111827; }
        .btn-light:hover { background: #d1d5db; }
        .field { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 10px; background: #fff; color: #111827; }
        textarea.field { resize: vertical; }
        .field:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, .15); }
        select.field option { color: #111827; }
        input::placeholder, textarea::placeholder { color: #9ca3af; opacity: 1; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: left; font-size: 14px; }
        th { background: #f9fafb; color: #374151; font-weight: 700; }
        .muted { color: #6b7280; font-size: 12px; }
        .success { background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 10px; border-radius: 8px; }
        .error { background: #fef2f2; border: 1px solid #ef4444; color: #991b1b; padding: 10px; border-radius: 8px; }
        .progress { height: 8px; background: #e5e7eb; border-radius: 999px; overflow: hidden; }
        .progress > div { height: 100%; background: #4f46e5; }
        .flex { display:flex; gap:8px; align-items:center; flex-wrap: wrap; }
        .only-mobile { display: none; }
        .only-desktop { display: block; }
        .auth-wrap { min-height: calc(100vh - 40px); display: flex; align-items: center; justify-content: center; padding: 20px 16px; }
        .auth-card { width: 100%; max-width: 420px; }
        @media (max-width: 900px) {
            .wrap { padding: 10px 8px 74px; }
            .nav-toggle { display: inline-flex; }
            .nav-menu { display: none; grid-template-columns: 1fr; gap: 10px; border-top: 1px solid #e5e7eb; padding-top: 10px; }
            .nav-menu.open { display: grid; }
            .nav-links { display: grid; gap: 6px; }
            .link { display: block; padding: 10px 12px; border: 1px solid #e2e8f0; background: #f8fafc; }
            .nav-right { display: grid; grid-template-columns: 1fr auto; gap: 8px; align-items: center; }
            .profile-pill { justify-content: center; }
            .btn { padding: 9px 10px; font-size: 13px; }
            th, td { padding: 8px; font-size: 12px; }
            .only-mobile { display: block; }
            .only-desktop { display: none; }
        }
    </style>
</head>
<body>
    @if(!request()->routeIs('login') && !request()->routeIs('register'))
        <div class="nav">
            <div class="nav-inner">
                <div class="nav-top">
                    <a href="{{ route('home') }}" class="brand" aria-label="ФинНавигатор">
                        <img src="{{ asset('icon.png') }}" alt="ФинНавигатор" class="brand-logo">
                    </a>
                    <button id="navToggle" class="nav-toggle" type="button" aria-expanded="false" aria-controls="navMenu">☰</button>
                </div>

                <div id="navMenu" class="nav-menu">
                    <div class="nav-links">
                        @auth
                            <a class="link" href="{{ route('dashboard') }}">Главная</a>
                            <a class="link" href="{{ route('loans.create') }}">Добавить кредит</a>
                            <a class="link" href="{{ route('scenarios.index') }}">Стратегия</a>
                            <a class="link" href="{{ route('payments.index') }}">Платежи</a>
                            <a class="link" href="{{ route('payments.schedule') }}">График оплат</a>
                            <a class="link" href="{{ route('settings.index') }}">Настройки</a>
                        @endauth
                    </div>
                    <div class="nav-right">
                        @auth
                            <span class="profile-pill">{{ auth()->user()->name ?: auth()->user()->email }}</span>
                            <form method="post" action="{{ route('logout') }}" style="display:inline;">
                                @csrf
                                <button class="btn btn-light" type="submit">Выйти</button>
                            </form>
                        @else
                            <a class="link" href="{{ route('login') }}">Вход</a>
                            <a class="btn btn-primary" href="{{ route('register') }}">Регистрация</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    @endif

    <main class="{{ request()->routeIs('login') || request()->routeIs('register') ? '' : 'wrap' }}">
        @if (session('success'))
            <div class="success {{ request()->routeIs('login') || request()->routeIs('register') ? 'auth-card' : '' }}" style="margin:{{ request()->routeIs('login') || request()->routeIs('register') ? '12px auto' : '0 0 12px 0' }};">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="error {{ request()->routeIs('login') || request()->routeIs('register') ? 'auth-card' : '' }}" style="margin:{{ request()->routeIs('login') || request()->routeIs('register') ? '12px auto' : '0 0 12px 0' }};">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        @yield('content')
    </main>
    <script>
        (() => {
            const toggle = document.getElementById('navToggle');
            const menu = document.getElementById('navMenu');
            if (!toggle || !menu) return;
            toggle.addEventListener('click', () => {
                const opened = menu.classList.toggle('open');
                toggle.setAttribute('aria-expanded', opened ? 'true' : 'false');
                toggle.textContent = opened ? '✕' : '☰';
            });
        })();
    </script>
</body>
</html>

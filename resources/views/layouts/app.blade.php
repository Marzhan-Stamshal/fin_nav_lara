<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Fin Navigation (Laravel)' }}</title>
    <style>
        :root { color-scheme: light; }
        html { scroll-behavior: smooth; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; color: #111827; background: linear-gradient(135deg, #eff6ff, #e0e7ff); min-height: 100vh; }
        a { color: inherit; text-decoration: none; }
        .wrap { max-width: 1280px; margin: 0 auto; padding: 16px; }
        .nav { background: #ffffff; box-shadow: 0 1px 2px rgba(0, 0, 0, .08); border-bottom: 1px solid #e5e7eb; }
        .nav-inner { max-width: 1280px; margin: 0 auto; padding: 14px 16px; display: flex; gap: 12px; align-items: center; justify-content: space-between; }
        .nav-links { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .brand { font-size: 24px; font-weight: 700; color: #1f2937; margin-right: 8px; }
        .link { color: #374151; font-weight: 600; }
        .link:hover { color: #4f46e5; }
        .card { background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 10px 25px -10px rgba(0, 0, 0, .2); }
        .grid { display: grid; gap: 12px; }
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
            .nav-inner { flex-direction: column; align-items: flex-start; }
            .wrap { padding: 10px; }
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
                <div class="nav-links">
                    <a href="{{ route('dashboard') }}" class="brand">ФинНавигатор</a>
                    @auth
                        <a class="link" href="{{ route('dashboard') }}">Главная</a>
                        <a class="link" href="{{ route('loans.create') }}">Добавить кредит</a>
                        <a class="link" href="{{ route('scenarios.index') }}">Стратегия</a>
                        <a class="link" href="{{ route('payments.index') }}">Платежи</a>
                        <a class="link" href="{{ route('payments.schedule') }}">График оплат</a>
                        <a class="link" href="{{ route('settings.index') }}">Настройки</a>
                    @endauth
                </div>
                <div>
                    @auth
                        <span style="margin-right:8px;color:#374151;">{{ auth()->user()->name }}</span>
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
</body>
</html>

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
        body { margin: 0; font-family: 'Segoe UI', Tahoma, sans-serif; background: #f3f4f6; color: #111827; }
        .wrap { max-width: 1200px; margin: 0 auto; padding: 16px; }
        .nav { background: #111827; color: #fff; }
        .nav-inner { max-width: 1200px; margin: 0 auto; padding: 12px 16px; display: flex; gap: 12px; align-items: center; justify-content: space-between; }
        .nav a { color: #fff; text-decoration: none; font-weight: 600; margin-right: 12px; }
        .card { background: #fff; border-radius: 10px; padding: 16px; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
        .grid { display: grid; gap: 12px; }
        .grid-4 { grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); }
        .btn { border: 0; border-radius: 8px; padding: 10px 14px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #4f46e5; color: #fff; }
        .btn-green { background: #059669; color: #fff; }
        .btn-orange { background: #ea580c; color: #fff; }
        .btn-gray { background: #374151; color: #fff; }
        .btn-light { background: #e5e7eb; color: #111827; }
        .field { width: 100%; padding: 9px 10px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; color: #111827; }
        select.field option { color: #111827; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: left; font-size: 14px; }
        th { background: #f9fafb; }
        .muted { color: #6b7280; font-size: 12px; }
        .success { background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 10px; border-radius: 8px; }
        .error { background: #fef2f2; border: 1px solid #ef4444; color: #991b1b; padding: 10px; border-radius: 8px; }
        .progress { height: 8px; background: #e5e7eb; border-radius: 999px; overflow: hidden; }
        .progress > div { height: 100%; background: #4f46e5; }
        .flex { display:flex; gap:8px; align-items:center; flex-wrap: wrap; }
        .only-mobile { display: none; }
        .only-desktop { display: block; }
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
    <div class="nav">
        <div class="nav-inner">
            <div>
                <a href="{{ route('dashboard') }}">Fin Navigation</a>
                @auth
                    <a href="{{ route('dashboard') }}">Главная</a>
                    <a href="{{ route('loans.create') }}">Добавить кредит</a>
                    <a href="{{ route('scenarios.index') }}">Стратегия</a>
                    <a href="{{ route('payments.index') }}">Платежи</a>
                    <a href="{{ route('payments.schedule') }}">График оплат</a>
                    <a href="{{ route('settings.index') }}">Настройки</a>
                @endauth
            </div>
            <div>
                @auth
                    <span style="margin-right:8px;">{{ auth()->user()->name }}</span>
                    <form method="post" action="{{ route('logout') }}" style="display:inline;">
                        @csrf
                        <button class="btn btn-light" type="submit">Выйти</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Вход</a>
                    <a href="{{ route('register') }}">Регистрация</a>
                @endauth
            </div>
        </div>
    </div>

    <main class="wrap">
        @if (session('success'))
            <div class="success" style="margin-bottom: 12px;">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="error" style="margin-bottom: 12px;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>

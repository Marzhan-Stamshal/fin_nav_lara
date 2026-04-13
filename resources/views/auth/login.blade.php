@extends('layouts.app')

@section('content')
<div class="auth-wrap">
    <div class="card auth-card" style="padding:32px;">
        <h1 style="font-size:32px;font-weight:700;text-align:center;color:#1f2937;margin:0 0 8px;">ФинНавигатор</h1>
        <p style="text-align:center;color:#6b7280;margin:0 0 20px;">Управление кредитами и долгами</p>
        <form method="post" action="{{ route('login.submit') }}" class="grid" style="gap:12px;">
            @csrf
            <label style="font-size:14px;font-weight:600;color:#374151;">Email
                <input class="field" type="email" name="email" value="{{ old('email') }}" placeholder="your@email.com" required>
            </label>
            <label style="font-size:14px;font-weight:600;color:#374151;">Пароль
                <input class="field" type="password" name="password" placeholder="Ваш пароль" required>
            </label>
            <button class="btn btn-primary" type="submit" style="width:100%;">Войти</button>
        </form>
        <p style="text-align:center;color:#6b7280;margin:16px 0 0;">
            Нет аккаунта?
            <a href="{{ route('register') }}" style="color:#4f46e5;font-weight:600;">Зарегистрироваться</a>
        </p>
    </div>
</div>
@endsection

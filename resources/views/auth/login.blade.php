@extends('layouts.app')

@section('content')
<div class="card" style="max-width:480px;margin:40px auto;">
    <h1>Вход</h1>
    <form method="post" action="{{ route('login.submit') }}" class="grid" style="gap:10px;">
        @csrf
        <label>Email
            <input class="field" type="email" name="email" value="{{ old('email') }}" required>
        </label>
        <label>Пароль
            <input class="field" type="password" name="password" required>
        </label>
        <button class="btn btn-primary" type="submit">Войти</button>
    </form>
    <p class="muted" style="margin-top:10px;">Нет аккаунта? <a href="{{ route('register') }}">Регистрация</a></p>
</div>
@endsection

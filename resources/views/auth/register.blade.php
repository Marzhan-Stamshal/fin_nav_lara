@extends('layouts.app')

@section('content')
<div class="card" style="max-width:520px;margin:40px auto;">
    <h1>Регистрация</h1>
    <form method="post" action="{{ route('register.submit') }}" class="grid" style="gap:10px;">
        @csrf
        <label>Имя
            <input class="field" type="text" name="name" value="{{ old('name') }}">
        </label>
        <label>Email
            <input class="field" type="email" name="email" value="{{ old('email') }}" required>
        </label>
        <label>Пароль
            <input class="field" type="password" name="password" required>
        </label>
        <label>Повтор пароля
            <input class="field" type="password" name="password_confirmation" required>
        </label>
        <button class="btn btn-primary" type="submit">Создать аккаунт</button>
    </form>
</div>
@endsection

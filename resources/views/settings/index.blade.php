@extends('layouts.app')

@section('content')
<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:12px;">
    <div class="card">
        <h2 style="margin-top:0;">Профиль</h2>
        <form method="post" action="{{ route('settings.profile') }}" class="grid" style="gap:10px;">
            @csrf
            <label>Имя
                <input class="field" type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </label>
            <label>Email
                <input class="field" type="email" name="email" value="{{ old('email', $user->email) }}" required>
            </label>
            <button class="btn btn-primary" type="submit">Сохранить профиль</button>
        </form>
    </div>

    <div class="card">
        <h2 style="margin-top:0;">Пароль</h2>
        <form method="post" action="{{ route('settings.password') }}" class="grid" style="gap:10px;">
            @csrf
            <label>Текущий пароль
                <input class="field" type="password" name="current_password" required>
            </label>
            <label>Новый пароль
                <input class="field" type="password" name="password" required>
            </label>
            <label>Повтор нового пароля
                <input class="field" type="password" name="password_confirmation" required>
            </label>
            <button class="btn btn-primary" type="submit">Обновить пароль</button>
        </form>
    </div>

    <div class="card" style="grid-column:1/-1;">
        <h2 style="margin-top:0;">Личные параметры</h2>
        <form method="post" action="{{ route('settings.preferences') }}" class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:10px; align-items:end;">
            @csrf
            <label>Доход в месяц
                <input class="field" type="number" step="0.01" min="0" name="monthly_income" value="{{ old('monthly_income', $settings->monthly_income) }}">
            </label>
            <label>Валюта
                <select class="field" name="currency">
                    @foreach (['KZT', 'RUB', 'USD', 'EUR'] as $currency)
                        <option value="{{ $currency }}" {{ old('currency', $settings->currency) === $currency ? 'selected' : '' }}>{{ $currency }}</option>
                    @endforeach
                </select>
            </label>
            <label>Локаль
                <select class="field" name="locale">
                    @foreach (['ru-RU', 'kk-KZ', 'en-US'] as $locale)
                        <option value="{{ $locale }}" {{ old('locale', $settings->locale) === $locale ? 'selected' : '' }}>{{ $locale }}</option>
                    @endforeach
                </select>
            </label>
            <button class="btn btn-primary" type="submit">Сохранить параметры</button>
        </form>
    </div>
</div>
@endsection

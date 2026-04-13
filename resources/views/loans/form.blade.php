@extends('layouts.app')

@section('content')
<style>
    .loan-form-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }
    .loan-field-label {
        display: block;
        min-width: 0;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
    }
    .loan-field-label .field {
        margin-top: 4px;
    }
    @media (max-width: 980px) {
        .loan-form-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 640px) {
        .loan-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<div style="max-width:900px;margin:0 auto;">
    <div class="card" style="margin-bottom:12px;">
        <a href="{{ route('dashboard') }}" style="color:#4f46e5;font-weight:600;">← Назад</a>
        <h1 style="margin:12px 0 0;font-size:30px;color:#1f2937;">{{ $mode === 'create' ? 'Добавить новый кредит' : 'Редактировать кредит' }}</h1>
    </div>
    <div class="card">
        <form method="post" action="{{ $mode === 'create' ? route('loans.store') : route('loans.update', $loan) }}" class="grid" style="gap:16px;">
            @csrf
            @if ($mode === 'edit') @method('PUT') @endif

            <div>
                <h2 style="margin:0 0 10px;color:#1f2937;">Основная информация</h2>
                @if($mode === 'create')
                    <div style="margin-bottom:10px;">
                        <div class="muted" style="margin-bottom:6px;font-size:13px;">Быстрые шаблоны</div>
                        <div class="flex">
                            <button type="button" class="btn btn-light" onclick="applyTemplate('phone')">Телефон</button>
                            <button type="button" class="btn btn-light" onclick="applyTemplate('headphones')">Наушники</button>
                            <button type="button" class="btn btn-light" onclick="applyTemplate('cash')">Наличные</button>
                        </div>
                    </div>
                @endif
                <div class="loan-form-grid">
                    <label class="loan-field-label">Имя кредита (необязательно)
                        <input class="field" type="text" name="title" value="{{ old('title', $loan->title) }}" placeholder="Наушники / Телефон мамы">
                    </label>
                    <label class="loan-field-label">Название банка
                        <input class="field" type="text" name="bank_name" value="{{ old('bank_name', $loan->bank_name) }}" required>
                    </label>
                    <label class="loan-field-label">Тип кредита
                        <select class="field" name="loan_type" required>
                            @php($type = old('loan_type', $loan->loan_type))
                            <option value="наличный" {{ $type === 'наличный' ? 'selected' : '' }}>Наличный кредит</option>
                            <option value="рассрочка" {{ $type === 'рассрочка' ? 'selected' : '' }}>Рассрочка</option>
                            <option value="кредитная_карта" {{ $type === 'кредитная_карта' ? 'selected' : '' }}>Кредитная карта</option>
                            <option value="автокредит" {{ $type === 'автокредит' ? 'selected' : '' }}>Автокредит</option>
                            <option value="ипотека" {{ $type === 'ипотека' ? 'selected' : '' }}>Ипотека</option>
                        </select>
                    </label>
                    <label class="loan-field-label">Группа (необязательно)
                        <input class="field" type="text" name="group_name" value="{{ old('group_name', $loan->group_name) }}" placeholder="Плачу не я">
                    </label>
                    <label class="loan-field-label">Статус
                        <select class="field" name="status">
                            @php($st = old('status', $loan->status))
                            <option value="активный" {{ $st === 'активный' ? 'selected' : '' }}>Активный</option>
                            <option value="закрыт" {{ $st === 'закрыт' ? 'selected' : '' }}>Закрыт</option>
                        </select>
                    </label>
                </div>
            </div>

            <div>
                <h2 style="margin:0 0 10px;color:#1f2937;">Суммы</h2>
                <div class="loan-form-grid">
                    <label class="loan-field-label">Первоначальная сумма (тг)
                        <input class="field" type="number" step="0.01" min="0" name="principal_initial" value="{{ old('principal_initial', $loan->principal_initial) }}" required>
                    </label>
                    <label class="loan-field-label">Сумма досрочного погашения сейчас (тг)
                        <input class="field" type="number" step="0.01" min="0" name="early_payoff_amount" value="{{ old('early_payoff_amount', $loan->early_payoff_amount) }}" required>
                    </label>
                    <label class="loan-field-label">Ежемесячный платёж (тг)
                        <input class="field" type="number" step="0.01" min="0" name="monthly_payment" value="{{ old('monthly_payment', $loan->monthly_payment) }}" required>
                    </label>
                    <label class="loan-field-label">Годовая ставка (%)
                        <input class="field" type="number" step="0.001" min="0" name="interest_rate_annual" value="{{ old('interest_rate_annual', $loan->interest_rate_annual) }}">
                    </label>
                    <label class="loan-field-label">Месяцев всего (если знаете)
                        <input class="field" type="number" min="1" name="months_total" value="{{ old('months_total', $loan->months_total) }}">
                    </label>
                    <label class="loan-field-label">Месяцев оплачено
                        <input class="field" type="number" min="0" name="months_paid" value="{{ old('months_paid', $loan->months_paid ?? 0) }}" required>
                    </label>
                </div>
            </div>

            <div>
                <h2 style="margin:0 0 10px;color:#1f2937;">Даты</h2>
                <div class="loan-form-grid">
                    <label class="loan-field-label">Дата начала
                        <input class="field" type="date" name="start_date" value="{{ old('start_date', optional($loan->start_date)->toDateString()) }}" required>
                    </label>
                    <label class="loan-field-label">Дата конца
                        <input class="field" type="date" name="end_date" value="{{ old('end_date', optional($loan->end_date)->toDateString()) }}" required>
                    </label>
                    <label class="loan-field-label">Дата следующего платежа
                        <input class="field" type="date" name="next_payment_date" value="{{ old('next_payment_date', optional($loan->next_payment_date)->toDateString()) }}">
                    </label>
                </div>
            </div>

            <div>
                <h2 style="margin:0 0 10px;color:#1f2937;">Дополнительно</h2>
                <label class="loan-field-label">Заметки
                    <textarea class="field" name="notes" rows="3">{{ old('notes', $loan->notes) }}</textarea>
                </label>
            </div>

            <div class="flex" style="justify-content:flex-end;">
                <a href="{{ $mode === 'edit' ? route('loans.show', $loan) : route('dashboard') }}" class="btn btn-light">Отмена</a>
                <button class="btn btn-primary" type="submit">{{ $mode === 'create' ? 'Добавить кредит' : 'Сохранить изменения' }}</button>
            </div>
        </form>
    </div>
</div>
@if($mode === 'create')
<script>
function applyTemplate(type) {
    const fields = {
        title: document.querySelector('[name=\"title\"]'),
        bank: document.querySelector('[name=\"bank_name\"]'),
        loanType: document.querySelector('[name=\"loan_type\"]'),
        rate: document.querySelector('[name=\"interest_rate_annual\"]'),
        monthsTotal: document.querySelector('[name=\"months_total\"]'),
    };
    const templates = {
        phone: { title: 'Телефон', bank: 'Kaspi', loanType: 'рассрочка', rate: '0', monthsTotal: '24' },
        headphones: { title: 'Наушники', bank: 'Kaspi', loanType: 'рассрочка', rate: '0', monthsTotal: '12' },
        cash: { title: 'Наличные', bank: 'Jusan', loanType: 'наличный', rate: '30', monthsTotal: '36' },
    };
    const t = templates[type];
    if (!t) return;
    fields.title.value = t.title;
    fields.bank.value = t.bank;
    fields.loanType.value = t.loanType;
    fields.rate.value = t.rate;
    fields.monthsTotal.value = t.monthsTotal;
}
</script>
@endif
@endsection

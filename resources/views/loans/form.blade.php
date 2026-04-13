@extends('layouts.app')

@section('content')
<div class="card" style="max-width:900px;margin:0 auto;">
    <h1>{{ $mode === 'create' ? 'Добавить кредит' : 'Редактировать кредит' }}</h1>
    @if($mode === 'create')
        <div style="margin-bottom:10px;">
            <div class="muted" style="margin-bottom:6px;">Быстрые шаблоны</div>
            <div class="flex">
                <button type="button" class="btn btn-light" onclick="applyTemplate('phone')">Телефон</button>
                <button type="button" class="btn btn-light" onclick="applyTemplate('headphones')">Наушники</button>
                <button type="button" class="btn btn-light" onclick="applyTemplate('cash')">Наличные</button>
            </div>
        </div>
    @endif
    <form method="post" action="{{ $mode === 'create' ? route('loans.store') : route('loans.update', $loan) }}" class="grid" style="gap:12px;">
        @csrf
        @if ($mode === 'edit') @method('PUT') @endif

        <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:10px;">
            <label>Название (необязательно)
                <input class="field" type="text" name="title" value="{{ old('title', $loan->title) }}" placeholder="Наушники, Телефон мамы">
            </label>
            <label>Банк
                <input class="field" type="text" name="bank_name" value="{{ old('bank_name', $loan->bank_name) }}" required>
            </label>
            <label>Тип кредита
                <input class="field" type="text" name="loan_type" value="{{ old('loan_type', $loan->loan_type) }}" required>
            </label>
            <label>Группа
                <input class="field" type="text" name="group_name" value="{{ old('group_name', $loan->group_name) }}" placeholder="Например: Плачу не я">
            </label>
            <label>Статус
                <select class="field" name="status">
                    @php($st = old('status', $loan->status))
                    <option value="активный" {{ $st === 'активный' ? 'selected' : '' }}>активный</option>
                    <option value="закрыт" {{ $st === 'закрыт' ? 'selected' : '' }}>закрыт</option>
                </select>
            </label>
            <label>Ставка годовая (%)
                <input class="field" type="number" step="0.001" min="0" name="interest_rate_annual" value="{{ old('interest_rate_annual', $loan->interest_rate_annual) }}">
            </label>
            <label>Сумма кредита
                <input class="field" type="number" step="0.01" min="0" name="principal_initial" value="{{ old('principal_initial', $loan->principal_initial) }}" required>
            </label>
            <label>Сумма досрочного погашения (сейчас)
                <input class="field" type="number" step="0.01" min="0" name="early_payoff_amount" value="{{ old('early_payoff_amount', $loan->early_payoff_amount) }}" required>
            </label>
            <label>Ежемесячный платеж
                <input class="field" type="number" step="0.01" min="0" name="monthly_payment" value="{{ old('monthly_payment', $loan->monthly_payment) }}" required>
            </label>
            <label>Месяцев всего (если знаете)
                <input class="field" type="number" min="1" name="months_total" value="{{ old('months_total', $loan->months_total) }}">
            </label>
            <label>Месяцев оплачено
                <input class="field" type="number" min="0" name="months_paid" value="{{ old('months_paid', $loan->months_paid ?? 0) }}" required>
            </label>
            <label>Дата начала
                <input class="field" type="date" name="start_date" value="{{ old('start_date', optional($loan->start_date)->toDateString()) }}" required>
            </label>
            <label>Дата завершения
                <input class="field" type="date" name="end_date" value="{{ old('end_date', optional($loan->end_date)->toDateString()) }}" required>
            </label>
            <label>Следующий платеж
                <input class="field" type="date" name="next_payment_date" value="{{ old('next_payment_date', optional($loan->next_payment_date)->toDateString()) }}">
            </label>
        </div>

        <label>Заметки
            <textarea class="field" name="notes" rows="3">{{ old('notes', $loan->notes) }}</textarea>
        </label>

        <div class="flex">
            <button class="btn btn-primary" type="submit">{{ $mode === 'create' ? 'Сохранить кредит' : 'Обновить кредит' }}</button>
            <a href="{{ route('dashboard') }}" class="btn btn-light">Отмена</a>
        </div>
    </form>
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
        cash: { title: 'Наличные', bank: 'Jusan', loanType: 'наличные', rate: '30', monthsTotal: '36' },
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

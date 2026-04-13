@extends('layouts.app')

@section('content')
<div class="card" style="margin-bottom:14px;">
    <h1 style="margin-top:0;">Стратегия погашения</h1>
    <form method="get" class="grid" style="gap:12px;">
        <div>
            <strong>Выберите кредиты для расчета</strong>
            <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); margin-top:8px;">
                @foreach ($summaries as $item)
                    @php($loan = $item['loan'])
                    <label style="border:1px solid #e5e7eb; border-radius:8px; padding:8px;">
                        <input type="checkbox" name="loan_ids[]" value="{{ $loan->id }}" {{ $selectedIds->contains($loan->id) ? 'checked' : '' }}>
                        <strong>{{ $loan->title ?: $loan->bank_name }}</strong>
                        <div class="muted">{{ $loan->bank_name }} • {{ number_format($loan->monthly_payment, 0, ',', ' ') }} ₸/мес</div>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
            <label>Доп. платеж (+X)
                <input class="field" type="number" name="extra_budget" min="0" step="0.01" value="{{ request('extra_budget', 0) }}">
            </label>
            <label><input type="checkbox" name="refinance_enabled" value="1" {{ request()->boolean('refinance_enabled') ? 'checked' : '' }}> Смоделировать рефинансирование</label>
            <label>Сумма рефинансирования
                <input class="field" type="number" name="refinance_amount" min="0" step="0.01" value="{{ request('refinance_amount', $selectedEarly) }}">
            </label>
            <label>Ставка рефинансирования (%)
                <input class="field" type="number" name="refinance_rate" min="0" step="0.01" value="{{ request('refinance_rate') }}">
            </label>
            <label>Срок рефинансирования (мес.)
                <input class="field" type="number" name="refinance_term_months" min="1" value="{{ request('refinance_term_months') }}">
            </label>
        </div>
        <button class="btn btn-primary" type="submit">Рассчитать</button>
    </form>
</div>

<div class="grid grid-4" style="margin-bottom:14px;">
    <div class="card"><div class="muted">Выбрано кредитов</div><div style="font-size:24px;font-weight:700;">{{ $selected->count() }}</div></div>
    <div class="card"><div class="muted">Досрочно сейчас</div><div style="font-size:24px;font-weight:700;">{{ number_format($selectedEarly, 0, ',', ' ') }} ₸</div></div>
    <div class="card"><div class="muted">Если платить до конца</div><div style="font-size:24px;font-weight:700;">{{ number_format($selectedFull, 0, ',', ' ') }} ₸</div></div>
    <div class="card"><div class="muted">Экономия</div><div style="font-size:24px;font-weight:700;color:#047857;">{{ number_format($selectedSavings, 0, ',', ' ') }} ₸</div></div>
</div>

@if ($selected->count() > 0)
<div class="card" style="margin-bottom:14px;">
    <h2 style="margin-top:0;">Сохранить сценарий</h2>
    <form method="post" action="{{ route('scenarios.store') }}" class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;align-items:end;">
        @csrf
        <label>Название сценария
            <input class="field" type="text" name="name" required placeholder="Например: План на май">
        </label>
        <label>Описание
            <input class="field" type="text" name="description" placeholder="Необязательно">
        </label>
        <input type="hidden" name="extra_budget" value="{{ request('extra_budget', 0) }}">
        <input type="hidden" name="selected_savings" value="{{ $selectedSavings }}">
        <input type="hidden" name="monthly_benefit" value="{{ $refinance['monthlyBenefit'] ?? 0 }}">
        @foreach ($selected as $item)
            <input type="hidden" name="loan_ids[]" value="{{ $item['loan']->id }}">
        @endforeach
        <button class="btn btn-primary" type="submit">Сохранить</button>
    </form>
</div>
@endif

@if ($extraImpact)
<div class="card" style="margin-bottom:14px;">
    <h2 style="margin-top:0;">Сценарий +X в этом месяце</h2>
    <p><strong>Целевой кредит:</strong> {{ $extraImpact['loan']->title ?: $extraImpact['loan']->bank_name }}</p>
    <p>Было досрочно: {{ number_format($extraImpact['beforeEarly'], 0, ',', ' ') }} ₸</p>
    <p>Станет досрочно: {{ number_format($extraImpact['afterEarly'], 0, ',', ' ') }} ₸</p>
    <p>Доп. экономия: <strong style="color:#047857;">{{ number_format($extraImpact['extraSavings'], 0, ',', ' ') }} ₸</strong></p>
</div>
@endif

@if ($refinance)
<div class="card">
    <h2 style="margin-top:0;">Сценарий рефинансирования</h2>
    <p>Новый кредит: {{ number_format($refinance['amount'], 0, ',', ' ') }} ₸ на {{ $refinance['term'] }} мес. под {{ $refinance['rate'] }}%</p>
    <p>Новый платеж/мес: <strong>{{ number_format($refinance['newMonthly'], 0, ',', ' ') }} ₸</strong></p>
    <p>Текущий платеж/мес (выбранные): {{ number_format($refinance['currentMonthly'], 0, ',', ' ') }} ₸</p>
    <p>Польза в месяц: <strong style="color:{{ $refinance['monthlyBenefit'] >= 0 ? '#047857' : '#991b1b' }};">{{ number_format($refinance['monthlyBenefit'], 0, ',', ' ') }} ₸</strong></p>
</div>
@endif

<div class="card">
    <h2 style="margin-top:0;">Сохраненные сценарии</h2>
    @forelse ($savedScenarios as $saved)
        <div style="border:1px solid #e5e7eb; border-radius:8px; padding:10px; margin-bottom:8px;">
            <div class="flex" style="justify-content:space-between;">
                <strong>{{ $saved->name }}</strong>
                <form method="post" action="{{ route('scenarios.destroy', $saved) }}" onsubmit="return confirm('Удалить сценарий?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-light" type="submit">Удалить</button>
                </form>
            </div>
            <div class="muted">{{ $saved->description }}</div>
            <div>Экономия: {{ number_format($saved->result_total_savings, 0, ',', ' ') }} ₸</div>
            <div>Польза/мес: {{ number_format($saved->result_monthly_benefit, 0, ',', ' ') }} ₸</div>
            <div class="muted">
                Кредиты:
                {{ $saved->scenarioLoans->map(fn($s) => ($s->loan?->title ?: $s->loan?->bank_name))->filter()->implode(', ') }}
            </div>
        </div>
    @empty
        <div class="muted">Сценарии пока не сохранены.</div>
    @endforelse
</div>
@endsection

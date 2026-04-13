@extends('layouts.app')

@section('content')
<style>
    .sc-title { margin: 8px 0 0; font-size: 30px; color: #1f2937; }
    .sc-sub { color: #6b7280; margin: 8px 0 0; }
    .sc-grid { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
    .sc-loan-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px; }
    .sc-loan-item { border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px; background: #fff; }
    .sc-result { border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; background: #fff; }
    .sc-result-head { background: linear-gradient(90deg, #4f46e5, #4338ca); color: #fff; padding: 12px; }
    .sc-result-body { padding: 12px; }
    .sc-kpi { border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px; background: #f8fafc; }
    .sc-kpi .label { color: #6b7280; font-size: 12px; }
    .sc-kpi .value { color: #1f2937; font-size: 24px; font-weight: 700; }
    .sc-kpi.green { border-color: #a7f3d0; background: #ecfdf5; }
    .sc-kpi.green .label, .sc-kpi.green .value { color: #047857; }
    .sc-two { display: grid; gap: 12px; grid-template-columns: 1.3fr 1fr; }
    @media (max-width: 900px) {
        .sc-two { grid-template-columns: 1fr; }
        .sc-title { font-size: 26px; }
    }
</style>

<div class="card" style="margin-bottom:14px;">
    <a href="{{ route('dashboard') }}" style="color:#4f46e5;font-weight:600;">← Назад</a>
    <h1 class="sc-title">Стратегии досрочного погашения</h1>
    <p class="sc-sub">Сравните варианты закрытия долгов и объединения нескольких кредитов в один выгодный.</p>
</div>

<form method="get" class="grid" style="gap:14px;">
    <div class="card">
        <h2 style="margin-top:0;">Параметры расчёта</h2>
        <div class="sc-grid">
            <label>Дополнительный платёж в месяц
                <input class="field" type="number" name="extra_monthly" min="0" step="0.01" value="{{ $extraMonthly }}">
            </label>
            <label>Разовый платёж сейчас
                <input class="field" type="number" name="extra_one_time" min="0" step="0.01" value="{{ $extraOneTime }}">
            </label>
            <div style="display:flex;align-items:end;">
                <button class="btn btn-primary" type="submit">Рассчитать</button>
            </div>
        </div>
    </div>

    <div class="card">
        <h2 style="margin-top:0;">Выборочное закрытие кредитов</h2>
        <p class="muted" style="font-size:13px;">Отметьте нужные кредиты для расчёта сумм и пользы.</p>
        <div class="sc-two">
            <div class="sc-loan-list">
                @foreach ($summaries as $item)
                    @php($loan = $item['loan'])
                    <label class="sc-loan-item">
                        <div style="display:flex;gap:8px;align-items:flex-start;">
                            <input type="checkbox" name="loan_ids[]" value="{{ $loan->id }}" {{ $selectedIds->contains((int)$loan->id) ? 'checked' : '' }}>
                            <div>
                                <div style="font-weight:700;color:#1f2937;">{{ $loan->title ?: $loan->bank_name }}</div>
                                <div class="muted">{{ $loan->loan_type }} • {{ number_format($item['earlyPayoffNow'], 0, ',', ' ') }} ₸</div>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
            <div class="grid" style="gap:10px; align-content:start;">
                <div class="sc-kpi"><div class="label">Выбрано кредитов</div><div class="value">{{ $selected->count() }}</div></div>
                <div class="sc-kpi"><div class="label">Сумма закрытия сейчас</div><div class="value" style="font-size:20px;">{{ number_format($selectedEarly, 0, ',', ' ') }} ₸</div></div>
                <div class="sc-kpi"><div class="label">Если платить полностью</div><div class="value" style="font-size:20px;">{{ number_format($selectedFull, 0, ',', ' ') }} ₸</div></div>
                <div class="sc-kpi green"><div class="label">Польза закрытия сейчас</div><div class="value" style="font-size:20px;">{{ number_format($selectedSavings, 0, ',', ' ') }} ₸</div></div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2 style="margin-top:0;">Объединить кредиты в один</h2>
        <div class="sc-grid">
            <label>Ставка нового кредита (%)
                <input class="field" type="number" name="refinance_rate" min="0.1" step="0.1" value="{{ $refiAnnualRate }}">
            </label>
            <label>Срок нового кредита (мес.)
                <input class="field" type="number" name="refinance_term_months" min="1" step="1" value="{{ $refiTermMonths }}">
            </label>
        </div>
        <div style="margin-top:10px;" class="sc-loan-list">
            @foreach ($summaries as $item)
                @php($loan = $item['loan'])
                <label class="sc-loan-item">
                    <div style="display:flex;gap:8px;align-items:flex-start;">
                        <input type="checkbox" name="refi_loan_ids[]" value="{{ $loan->id }}" {{ $refiLoanIds->contains((int)$loan->id) ? 'checked' : '' }}>
                        <div>
                            <div style="font-weight:700;color:#1f2937;">{{ $loan->title ?: $loan->bank_name }}</div>
                            <div class="muted">{{ number_format($item['earlyPayoffNow'], 0, ',', ' ') }} ₸ • {{ $loan->interest_rate_annual ?? 0 }}%</div>
                        </div>
                    </div>
                </label>
            @endforeach
        </div>
    </div>
</form>

@if($refinanceResult)
<div class="card" style="margin-top:14px;">
    <h2 style="margin-top:0;">Сценарий рефинансирования</h2>
    <div class="sc-grid">
        <div class="sc-kpi"><div class="label">Сейчас по выбранным</div><div class="value" style="font-size:20px;">{{ number_format($refinanceResult['currentTotalIfPayAsIs'], 0, ',', ' ') }} ₸</div></div>
        <div class="sc-kpi"><div class="label">Сумма закрытия</div><div class="value" style="font-size:20px;">{{ number_format($refinanceResult['refinancePrincipal'], 0, ',', ' ') }} ₸</div></div>
        <div class="sc-kpi"><div class="label">Новый платёж/мес</div><div class="value" style="font-size:20px;">{{ number_format($refinanceResult['refinanceMonthlyPayment'], 0, ',', ' ') }} ₸</div></div>
        <div class="sc-kpi"><div class="label">С новым кредитом всего</div><div class="value" style="font-size:20px;">{{ number_format($refinanceResult['refinanceTotalPayment'], 0, ',', ' ') }} ₸</div></div>
        <div class="sc-kpi {{ $refinanceResult['refinanceSavings'] >= 0 ? 'green' : '' }}"><div class="label">Польза рефинансирования</div><div class="value" style="font-size:20px;color:{{ $refinanceResult['refinanceSavings'] >= 0 ? '#047857' : '#991b1b' }};">{{ number_format($refinanceResult['refinanceSavings'], 0, ',', ' ') }} ₸</div></div>
    </div>
</div>
@endif

@if(!empty($strategyResults))
<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:12px; margin-top:14px;">
    @foreach($strategyResults as $result)
        <div class="sc-result">
            <div class="sc-result-head">
                <div style="font-weight:700;">{{ $result['name'] }}</div>
                <div style="font-size:12px; opacity:.9;">{{ $result['description'] }}</div>
            </div>
            <div class="sc-result-body">
                <div class="sc-grid">
                    <div class="sc-kpi"><div class="label">Платёж/месяц</div><div class="value" style="font-size:18px;">{{ number_format($result['monthlyPayment'], 0, ',', ' ') }} ₸</div></div>
                    <div class="sc-kpi"><div class="label">Ориентир закрытия</div><div class="value" style="font-size:18px;">{{ $result['closeDate']->format('m.Y') }}</div></div>
                </div>
                <div style="margin-top:8px; border-top:1px solid #e5e7eb; padding-top:8px;">
                    <div style="font-weight:700; color:#047857;">Экономия по сроку: {{ $result['monthsSaved'] }} мес.</div>
                    <div class="muted" style="font-size:13px;">Оценка пользы: {{ number_format($result['estimatedBenefit'], 0, ',', ' ') }} ₸</div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif

@if ($extraImpact)
<div class="card" style="margin-top:14px;">
    <h2 style="margin-top:0;">Сценарий: если внесу +X в этом месяце</h2>
    <p><strong>Целевой кредит:</strong> {{ $extraImpact['loan']->title ?: $extraImpact['loan']->bank_name }}</p>
    <p>Было досрочно: {{ number_format($extraImpact['beforeEarly'], 0, ',', ' ') }} ₸</p>
    <p>Станет досрочно: {{ number_format($extraImpact['afterEarly'], 0, ',', ' ') }} ₸</p>
    <p>Доп. экономия: <strong style="color:#047857;">{{ number_format($extraImpact['extraSavings'], 0, ',', ' ') }} ₸</strong></p>
</div>
@endif

@if ($selected->count() > 0)
<div class="card" style="margin-top:14px;">
    <h2 style="margin-top:0;">Сохранить сценарий</h2>
    <form method="post" action="{{ route('scenarios.store') }}" class="sc-grid" style="align-items:end;">
        @csrf
        <label>Название сценария
            <input class="field" type="text" name="name" required placeholder="Например: План на май">
        </label>
        <label>Описание
            <input class="field" type="text" name="description" placeholder="Необязательно">
        </label>
        <input type="hidden" name="extra_monthly" value="{{ $extraMonthly }}">
        <input type="hidden" name="extra_one_time" value="{{ $extraOneTime }}">
        <input type="hidden" name="selected_savings" value="{{ $selectedSavings }}">
        <input type="hidden" name="monthly_benefit" value="{{ $refinanceResult['refinanceSavings'] ?? 0 }}">
        <input type="hidden" name="strategy_type" value="custom">
        @foreach ($selected as $item)
            <input type="hidden" name="loan_ids[]" value="{{ $item['loan']->id }}">
        @endforeach
        <div><button class="btn btn-primary" type="submit">Сохранить</button></div>
    </form>
</div>
@endif

<div class="card" style="margin-top:14px;">
    <h2 style="margin-top:0;">Сохраненные сценарии</h2>
    @forelse ($savedScenarios as $saved)
        <div style="border:1px solid #e5e7eb; border-radius:10px; padding:10px; margin-bottom:8px;">
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
            <div class="muted">Кредиты: {{ $saved->scenarioLoans->map(fn($s) => ($s->loan?->title ?: $s->loan?->bank_name))->filter()->implode(', ') }}</div>
        </div>
    @empty
        <div class="muted">Сценарии пока не сохранены.</div>
    @endforelse
</div>
@endsection

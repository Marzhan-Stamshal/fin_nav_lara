@extends('layouts.app')

@section('content')
@php($sortOptions = ['end' => 'По сроку', 'bank' => 'По банку', 'group' => 'По группе', 'term' => 'По мес. осталось', 'early' => 'По досрочно', 'full' => 'По полностью', 'savings' => 'По экономии', 'monthly' => 'По платежу', 'rate' => 'По ставке'])
<style>
    .db-wrap { display: grid; gap: 14px; }
    .db-title { margin: 0; font-size: 30px; color: #1f2937; }
    .db-sub { margin: 6px 0 0; color: #6b7280; font-size: 14px; }
    .db-kpi { border: 1px solid #dbeafe; border-radius: 12px; padding: 14px; background: linear-gradient(140deg, #ffffff, #f8fbff); box-shadow: 0 10px 24px -18px rgba(30, 64, 175, .55); }
    .db-kpi-label { color: #6b7280; font-size: 12px; margin-bottom: 6px; }
    .db-kpi-value { color: #1f2937; font-size: 28px; font-weight: 800; letter-spacing: -0.02em; }
    .db-kpi-value.green { color: #047857; }
    .db-card { background: #fff; border-radius: 12px; padding: 14px; box-shadow: 0 10px 24px -18px rgba(15, 23, 42, .45); }
    .db-card-loading { opacity: .55; pointer-events: none; transition: opacity .2s ease; }
    .db-section { margin: 0 0 10px; font-size: 24px; color: #1f2937; letter-spacing: -0.02em; }
    .db-filters { display: grid; gap: 10px; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); align-items: end; }
    .db-chip { border: 1px solid #d1d5db; border-radius: 999px; padding: 4px 10px; font-size: 12px; background: #f8fafc; color: #334155; }
    .db-table-wrap { overflow: auto; border: 1px solid #e5e7eb; border-radius: 10px; }
    .db-table { width: 100%; border-collapse: collapse; min-width: 1160px; }
    .db-table th, .db-table td { padding: 10px; border-bottom: 1px solid #e5e7eb; font-size: 14px; text-align: left; vertical-align: top; }
    .db-table th { background: #f9fafb; color: #374151; font-weight: 700; }
    .db-table tbody tr:hover { background: #f9fafb; }
    .db-progress { height: 8px; background: #e5e7eb; border-radius: 999px; overflow: hidden; }
    .db-progress > div { height: 100%; background: #4f46e5; }
    .db-grid-4 { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(220px,1fr)); }
    .db-grid-2 { display: grid; gap: 12px; grid-template-columns: 1.6fr 1fr; }
    .db-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .db-tools-toggle {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 800;
        color: #1e293b;
        cursor: pointer;
        user-select: none;
    }
    .db-tools-toggle::marker,
    .db-tools-toggle::-webkit-details-marker {
        display: none;
    }
    .db-tools-panel {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #e2e8f0;
    }
    .db-mass-actions .field { width: 220px; max-width: 100%; }
    .db-action-btn {
        padding: 11px 14px;
        font-size: 14px;
        font-weight: 800;
        border-radius: 11px;
    }
    .db-mobile-card { border: 1px solid #dbe4ff; border-radius: 12px; padding: 10px; margin-bottom: 8px; background: #ffffff; box-shadow: 0 8px 16px -14px rgba(30, 64, 175, .5); }
    .db-mobile-title { display:flex; align-items:flex-start; justify-content:space-between; gap:8px; margin-bottom:8px; }
    .db-mobile-name { font-weight: 800; color: #1f2937; line-height: 1.2; }
    .db-mobile-bank { font-size: 12px; color: #64748b; }
    .db-mobile-grid { display:grid; grid-template-columns: 1fr 1fr; gap:6px; margin-top:8px; }
    .db-mobile-pill { border-radius: 8px; padding: 6px 8px; background: #f8fafc; font-size: 12px; }
    .db-mobile-links { display:flex; gap:10px; flex-wrap:wrap; margin-top:8px; }
    .db-mobile-links a, .db-mobile-links button { font-size: 13px; font-weight: 700; border: 0; background: transparent; padding: 0; cursor: pointer; }
    .db-mobile-links .danger { color: #dc2626; }
    .db-right { margin-left: auto; }
    .db-mobile-cards { display: none; }
    .db-sticky { position: fixed; left: 0; right: 0; bottom: 0; z-index: 60; border-top: 1px solid #e5e7eb; background: rgba(255,255,255,.95); backdrop-filter: blur(4px); padding: 10px 14px; display: none; }
    .db-sticky-inner { max-width: 1200px; margin: 0 auto; display: flex; gap: 8px; align-items: center; justify-content: space-between; flex-wrap: wrap; }
    @media (max-width: 1200px) { .db-grid-2 { grid-template-columns: 1fr; } }
    @media (max-width: 900px) {
        .db-wrap { gap: 10px; }
        .db-title { font-size: 26px; }
        .db-section { font-size: 22px; }
        .db-card { padding: 12px; }
        .db-kpi { padding: 12px; }
        .db-kpi-value { font-size: 24px; }
        .db-grid-4 { grid-template-columns: 1fr; }
        .db-filters { grid-template-columns: 1fr; }
        .db-actions { gap: 6px; }
        .db-mass-actions .btn, .db-mass-actions .field { width: 100%; }
        .db-right { margin-left: 0; width: 100%; }
        .db-table-wrap { display: none; }
        .db-mobile-cards { display: block; }
    }
</style>

<div class="db-wrap">
    <div class="db-card">
        <h1 class="db-title">ФинНавигатор</h1>
        <p class="db-sub">Управление кредитами и долгами в одном месте</p>
    </div>

    <div class="db-grid-4">
        <div class="db-kpi"><div class="db-kpi-label">Досрочно сейчас</div><div class="db-kpi-value">{{ number_format($totalEarly, 0, ',', ' ') }} ₸</div></div>
        <div class="db-kpi"><div class="db-kpi-label">Полностью до конца</div><div class="db-kpi-value">{{ number_format($totalFull, 0, ',', ' ') }} ₸</div></div>
        <div class="db-kpi"><div class="db-kpi-label">Экономия при закрытии сейчас</div><div class="db-kpi-value green">{{ number_format($totalSavings, 0, ',', ' ') }} ₸</div></div>
        <div class="db-kpi"><div class="db-kpi-label">Платеж в месяц (активные)</div><div class="db-kpi-value">{{ number_format($allActiveMonthly, 0, ',', ' ') }} ₸</div></div>
    </div>

    @if($upcomingByBankDate->count())
    <div class="db-card">
        <h2 class="db-section">Платежи за 30 дней</h2>
        @foreach($upcomingByBankDate as $item)
            <div class="db-actions" style="justify-content:space-between; border:1px solid #e5e7eb; border-radius:8px; padding:8px; margin-bottom:6px;">
                <span>{{ $item['bankName'] }} • {{ $item['dateLabel'] }}</span>
                <strong>{{ number_format($item['total'], 0, ',', ' ') }} ₸{{ $item['count'] > 1 ? ' ('.$item['count'].')' : '' }}</strong>
            </div>
        @endforeach
    </div>
    @endif

    <div class="db-card" id="calendarCard">
        <h2 class="db-section">Календарь</h2>
        @php($prevMonth = $calendarBase->copy()->subMonth()->format('Y-m'))
        @php($nextMonth = $calendarBase->copy()->addMonth()->format('Y-m'))
        <div class="db-actions" style="justify-content:space-between; margin-bottom:8px;">
            <a class="btn btn-light" data-calendar-link="1" href="{{ route('dashboard', array_merge(request()->query(), ['month' => $prevMonth, 'calendar_date' => null])) }}">←</a>
            <strong>{{ $calendarBase->translatedFormat('F Y') }}</strong>
            <a class="btn btn-light" data-calendar-link="1" href="{{ route('dashboard', array_merge(request()->query(), ['month' => $nextMonth, 'calendar_date' => null])) }}">→</a>
        </div>
        <div class="db-grid-4" style="grid-template-columns:repeat(7,minmax(0,1fr)); gap:6px; margin-bottom:8px; font-size:12px; color:#6b7280;">
            <div>Пн</div><div>Вт</div><div>Ср</div><div>Чт</div><div>Пт</div><div>Сб</div><div>Вс</div>
        </div>
        <div class="db-grid-4" style="grid-template-columns:repeat(7,minmax(0,1fr)); gap:6px;">
            @php($firstDay = $calendarBase->copy()->startOfMonth())
            @php($pad = $firstDay->dayOfWeekIso - 1)
            @for($i=0; $i < $pad; $i++)
                <div style="height:62px; background:#f9fafb; border-radius:8px;"></div>
            @endfor
            @for($d=1; $d <= $calendarBase->daysInMonth; $d++)
                @php($dateKey = $calendarBase->copy()->day($d)->toDateString())
                @php($entry = $calendarEntries->get($dateKey))
                @php($selected = $selectedCalendarDate === $dateKey)
                <a data-calendar-link="1" href="{{ route('dashboard', array_merge(request()->query(), ['calendar_date' => $selected ? null : $dateKey])) }}" style="height:62px; border:1px solid {{ $selected ? '#4f46e5' : ($entry ? '#86efac' : '#e5e7eb') }}; background: {{ $selected ? '#eef2ff' : ($entry ? '#f0fdf4' : '#fff') }}; border-radius:8px; text-decoration:none; color:#111827; padding:4px; font-size:11px;">
                    <div style="font-weight:600;">{{ $d }}</div>
                    @if($entry)<div style="font-size:10px;">{{ number_format($entry['total'], 0, ',', ' ') }} ₸</div>@endif
                </a>
            @endfor
        </div>
    </div>

    @if($recommendedLoan)
    <div class="db-card" style="border:1px solid #a7f3d0;">
        <h2 class="db-section">Рекомендация месяца</h2>
        <div class="db-grid-4">
            <div>
                <strong>{{ $recommendedLoan['loan']->title ?: $recommendedLoan['loan']->bank_name }}</strong>
                <div class="muted">{{ $recommendedLoan['loan']->bank_name }} • {{ $recommendedLoan['loan']->loan_type }}</div>
                <div style="margin-top:8px;">Досрочно сейчас: <strong>{{ number_format($recommendedLoan['earlyPayoffNow'], 0, ',', ' ') }} ₸</strong></div>
                <div>Если платить до конца: <strong>{{ number_format($recommendedLoan['fullPaymentToEnd'], 0, ',', ' ') }} ₸</strong></div>
            </div>
            <div>
                <div class="muted">Потенциальная экономия</div>
                <div style="font-size:26px;font-weight:700;color:#047857;">{{ number_format($recommendedLoan['savingsIfCloseNow'], 0, ',', ' ') }} ₸</div>
                <div>Ставка: {{ $recommendedLoan['loan']->interest_rate_annual ?? 0 }}%</div>
                <div>Платеж/мес: {{ number_format($recommendedLoan['loan']->monthly_payment, 0, ',', ' ') }} ₸</div>
            </div>
        </div>
    </div>
    @endif

    @if($paymentReminders->count())
    <div class="db-card">
        <h2 class="db-section">Напоминания 3/1 день</h2>
        @foreach($paymentReminders as $item)
            <div class="db-actions" style="justify-content:space-between; border:1px solid #e5e7eb; border-radius:8px; padding:8px; margin-bottom:6px;">
                <div>
                    <strong>{{ $item['loan']->title ?: $item['loan']->bank_name }}</strong>
                    <div class="muted">{{ $item['days'] === 1 ? 'Платеж завтра' : 'Платеж через 3 дня' }} • {{ optional($item['loan']->next_payment_date)->format('d.m.Y') }}</div>
                </div>
                <strong>{{ number_format($item['loan']->monthly_payment, 0, ',', ' ') }} ₸</strong>
            </div>
        @endforeach
    </div>
    @endif

    <div class="db-card">
        <div class="db-actions" style="justify-content:space-between; margin-bottom:8px;">
            <h2 class="db-section" style="margin:0;">Ваши кредиты ({{ $visible->count() }})</h2>
            <a href="{{ route('loans.create') }}" class="btn btn-primary">+ Добавить кредит</a>
        </div>

        <details style="margin-bottom:10px;">
            <summary class="db-tools-toggle">Фильтры и сортировка</summary>
            <div class="db-tools-panel">
                <form method="get" class="db-filters">
                    <label>Банк
                        <select class="field" name="bank"><option value="all">Все банки</option>@foreach ($banks as $bank)<option value="{{ $bank }}" {{ $filterBank === $bank ? 'selected' : '' }}>{{ $bank }}</option>@endforeach</select>
                    </label>
                    <label>Группа
                        <select class="field" name="group"><option value="all">Все группы</option>@foreach ($groups as $group)<option value="{{ $group }}" {{ $filterGroup === $group ? 'selected' : '' }}>{{ $group }}</option>@endforeach</select>
                    </label>
                    <label>Срок
                        <select class="field" name="term"><option value="all" {{ $filterTerm === 'all' ? 'selected' : '' }}>Любой</option><option value="overdue" {{ $filterTerm === 'overdue' ? 'selected' : '' }}>Срок вышел</option><option value="upTo12" {{ $filterTerm === 'upTo12' ? 'selected' : '' }}>До 12 мес.</option><option value="from12To24" {{ $filterTerm === 'from12To24' ? 'selected' : '' }}>12-24 мес.</option><option value="over24" {{ $filterTerm === 'over24' ? 'selected' : '' }}>Более 24 мес.</option></select>
                    </label>
                    <label>Сортировать<select class="field" name="sort">@foreach ($sortOptions as $key => $label)<option value="{{ $key }}" {{ $sort === $key ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></label>
                    <label>Порядок<select class="field" name="dir"><option value="asc" {{ $direction === 'asc' ? 'selected' : '' }}>По возрастанию</option><option value="desc" {{ $direction === 'desc' ? 'selected' : '' }}>По убыванию</option></select></label>
                    <div class="db-actions"><button class="btn btn-primary db-action-btn" type="submit">Применить</button><a class="btn btn-light db-action-btn" href="{{ route('dashboard') }}">Сбросить</a></div>
                </form>
            </div>
        </details>

        <div class="db-actions" style="margin-bottom:10px;">
            @if($filterBank !== 'all') <span class="db-chip">Банк: {{ $filterBank }}</span> @endif
            @if($filterGroup !== 'all') <span class="db-chip">Группа: {{ $filterGroup }}</span> @endif
            @if($filterTerm !== 'all') <span class="db-chip">Срок: {{ $filterTerm }}</span> @endif
            <span class="db-chip">Сорт: {{ $sortOptions[$sort] ?? 'По сроку' }} ({{ $direction === 'asc' ? '↑' : '↓' }})</span>
        </div>

        <form id="mass-paid-form" method="post" action="{{ route('dashboard.mark-paid') }}">@csrf</form>
        <form id="mass-close-form" method="post" action="{{ route('dashboard.close-early') }}">@csrf</form>
        <form id="mass-group-form" method="post" action="{{ route('dashboard.assign-group') }}">@csrf<input type="hidden" name="group_name" id="mass-group-name"></form>
        <form id="mass-clear-group-form" method="post" action="{{ route('dashboard.clear-group') }}">@csrf</form>

        <details style="margin-bottom:10px;">
            <summary class="db-tools-toggle">Групповые действия</summary>
            <div class="db-tools-panel">
                <div class="db-actions db-mass-actions" style="margin-bottom:10px;">
                    <button type="button" class="btn btn-green db-action-btn" onclick="submitMass('paid')">Оплачено за месяц</button>
                    <button type="button" class="btn btn-orange db-action-btn" onclick="submitMass('close')">Закрыла досрочно</button>
                    <input class="field" type="text" id="groupNameInput" placeholder="Название группы">
                    <button type="button" class="btn btn-gray db-action-btn" onclick="submitMass('group')">Добавить в группу</button>
                    <button type="button" class="btn btn-light db-action-btn" onclick="submitMass('group-clear')">Убрать из группы</button>
                    <button type="button" class="btn btn-light db-action-btn" onclick="toggleAll(true)">Выбрать все</button>
                    <button type="button" class="btn btn-light db-action-btn" onclick="toggleAll(false)">Снять выбор</button>
                    <span class="muted db-right">Платеж/мес (выборочно): <strong id="selectedMonthly">0 ₸</strong></span>
                </div>
            </div>
        </details>

        <div class="db-table-wrap">
            <table class="db-table">
                <thead><tr><th></th><th>Название</th><th>Группа</th><th>Прогресс</th><th>Досрочно</th><th>Полностью</th><th>Экономия</th><th>Платеж/мес</th><th>Ставка</th><th>Срок</th><th>Действия</th></tr></thead>
                <tbody>
                    @forelse ($visible as $item)
                        @php($loan = $item['loan'])
                        @php($timeline = $item['timeline'])
                        <tr>
                            <td><input type="checkbox" class="loan-check" data-loan-id="{{ $loan->id }}" value="{{ $loan->id }}" data-monthly="{{ $loan->monthly_payment }}"></td>
                            <td><div style="font-weight:700;color:#1f2937;">{{ $loan->title ?: $loan->bank_name }}</div>@if($loan->title)<div class="muted">{{ $loan->bank_name }}</div>@endif<div class="muted">{{ $loan->loan_type }}</div></td>
                            <td>{{ $loan->group_name ?: 'Без группы' }}</td>
                            <td style="min-width:170px;"><div style="font-size:13px;color:#374151;">{{ $timeline['progressPercent'] }}%</div><div class="db-progress"><div style="width:{{ $timeline['progressPercent'] }}%"></div></div><div class="muted" style="margin-top:4px;">{{ $timeline['monthsPaid'] }} из {{ $timeline['monthsTotal'] }} мес.</div></td>
                            <td style="font-weight:700;">{{ number_format($item['earlyPayoffNow'], 0, ',', ' ') }} ₸</td>
                            <td style="font-weight:700;">{{ number_format($item['fullPaymentToEnd'], 0, ',', ' ') }} ₸</td>
                            <td style="font-weight:700;color:#047857;">{{ number_format($item['savingsIfCloseNow'], 0, ',', ' ') }} ₸</td>
                            <td>{{ number_format($loan->monthly_payment, 0, ',', ' ') }} ₸</td>
                            <td>{{ $loan->interest_rate_annual ?? 0 }}%</td>
                            <td>{{ optional($loan->end_date)->format('d.m.Y') }}</td>
                            <td><div class="db-actions"><a href="{{ route('loans.show', $loan) }}" style="color:#4f46e5;font-weight:700;font-size:13px;">Подробно</a><a href="{{ route('loans.edit', $loan) }}" style="color:#4f46e5;font-weight:700;font-size:13px;">Изменить</a><form method="post" action="{{ route('loans.destroy', $loan) }}" onsubmit="return confirm('Удалить кредит?')">@csrf @method('DELETE')<button type="submit" style="border:0;background:transparent;color:#dc2626;font-weight:700;cursor:pointer;font-size:13px;">Удалить</button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="muted">Кредитов пока нет.</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr style="background:#f9fafb; font-weight:700;"><td>—</td><td>ИТОГО</td><td>{{ $visible->count() }} кредитов</td><td>—</td><td>{{ number_format($totalEarly, 0, ',', ' ') }} ₸</td><td>{{ number_format($totalFull, 0, ',', ' ') }} ₸</td><td style="color:#047857;">{{ number_format($totalSavings, 0, ',', ' ') }} ₸</td><td>{{ number_format($totalMonthly, 0, ',', ' ') }} ₸</td><td>—</td><td>—</td><td>—</td></tr></tfoot>
            </table>
        </div>

        <div class="db-mobile-cards">
            @forelse ($visible as $item)
                @php($loan = $item['loan'])
                @php($timeline = $item['timeline'])
                <div class="db-mobile-card">
                    <div class="db-mobile-title">
                        <div>
                            <div class="db-mobile-name">{{ $loan->title ?: $loan->bank_name }}</div>
                            <div class="db-mobile-bank">{{ $loan->bank_name }} • {{ $loan->loan_type }}</div>
                        </div>
                        <input type="checkbox" class="loan-check" data-loan-id="{{ $loan->id }}" value="{{ $loan->id }}" data-monthly="{{ $loan->monthly_payment }}">
                    </div>

                    <div style="margin-bottom:6px; font-size:12px; color:#334155;">Прогресс: {{ $timeline['monthsPaid'] }} из {{ $timeline['monthsTotal'] }} мес. ({{ $timeline['progressPercent'] }}%)</div>
                    <div class="db-progress"><div style="width:{{ $timeline['progressPercent'] }}%"></div></div>

                    <div class="db-mobile-grid">
                        <div class="db-mobile-pill">Досрочно: <strong>{{ number_format($item['earlyPayoffNow'], 0, ',', ' ') }} ₸</strong></div>
                        <div class="db-mobile-pill">Полностью: <strong>{{ number_format($item['fullPaymentToEnd'], 0, ',', ' ') }} ₸</strong></div>
                        <div class="db-mobile-pill" style="background:#ecfdf5;color:#065f46;">Экономия: <strong>{{ number_format($item['savingsIfCloseNow'], 0, ',', ' ') }} ₸</strong></div>
                        <div class="db-mobile-pill">Платеж/мес: <strong>{{ number_format($loan->monthly_payment, 0, ',', ' ') }} ₸</strong></div>
                    </div>

                    <div class="muted" style="margin-top:6px;">Ставка: {{ $loan->interest_rate_annual ?? 0 }}% • До: {{ optional($loan->end_date)->format('d.m.Y') }} • {{ $loan->group_name ?: 'Без группы' }}</div>

                    <div class="db-mobile-links">
                        <a href="{{ route('loans.show', $loan) }}" style="color:#2563eb;">Подробно</a>
                        <a href="{{ route('loans.edit', $loan) }}" style="color:#2563eb;">Изменить</a>
                        <form method="post" action="{{ route('loans.destroy', $loan) }}" onsubmit="return confirm('Удалить кредит?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="danger">Удалить</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="muted">Кредитов пока нет.</div>
            @endforelse

            <div class="db-mobile-card" style="background:#f8fafc;">
                <div style="font-weight:800; margin-bottom:6px;">ИТОГО</div>
                <div class="db-mobile-grid">
                    <div class="db-mobile-pill">Кредитов: <strong>{{ $visible->count() }}</strong></div>
                    <div class="db-mobile-pill">Платеж/мес: <strong>{{ number_format($totalMonthly, 0, ',', ' ') }} ₸</strong></div>
                    <div class="db-mobile-pill">Досрочно: <strong>{{ number_format($totalEarly, 0, ',', ' ') }} ₸</strong></div>
                    <div class="db-mobile-pill">Полностью: <strong>{{ number_format($totalFull, 0, ',', ' ') }} ₸</strong></div>
                    <div class="db-mobile-pill" style="background:#ecfdf5;color:#065f46;">Экономия: <strong>{{ number_format($totalSavings, 0, ',', ' ') }} ₸</strong></div>
                </div>
            </div>
        </div>

        <details style="margin-top:10px;">
            <summary style="cursor:pointer; font-weight:700; color:#374151;">Импорт и экспорт данных</summary>
            <div class="db-actions" style="margin-top:8px;">
                <a href="{{ route('loans.export.csv') }}" class="btn btn-light">Экспорт CSV</a>
                <a href="{{ route('loans.export.sample-csv') }}" class="btn btn-light">Шаблон CSV</a>
                <form method="post" action="{{ route('loans.import.csv') }}" enctype="multipart/form-data" class="db-actions">@csrf<input class="field" type="file" name="csv_file" accept=".csv,.txt" style="max-width:180px;"><button type="submit" class="btn btn-light">Импорт CSV</button></form>
                <form method="post" action="{{ route('backup.import.json') }}" enctype="multipart/form-data" class="db-actions">@csrf<input class="field" type="file" name="json_file" accept=".json,.txt" style="max-width:180px;"><label style="display:flex;align-items:center;gap:4px;font-size:12px;"><input type="checkbox" name="replace_existing" value="1"> Заменить</label><button type="submit" class="btn btn-light">Импорт JSON</button></form>
            </div>
        </details>
    </div>

    <div class="db-grid-2">
        <div>
            <div class="db-card" style="margin-bottom:12px;">
                <h2 class="db-section">Аналитика долгов</h2>
                <div class="db-card" style="border:1px solid #e5e7eb; box-shadow:none; padding:12px; margin-bottom:10px;">
                    <h3 style="margin:0 0 8px; color:#1f2937;">Сценарий: если внесу +X в этом месяце</h3>
                    <form method="get" class="db-grid-4" style="grid-template-columns:1fr 1fr; gap:8px;">
                        <input type="hidden" name="bank" value="{{ $filterBank }}"><input type="hidden" name="group" value="{{ $filterGroup }}"><input type="hidden" name="term" value="{{ $filterTerm }}"><input type="hidden" name="sort" value="{{ $sort }}"><input type="hidden" name="dir" value="{{ $direction }}">
                        <select class="field" name="what_if_loan_id">@foreach($visible as $item)<option value="{{ $item['loan']->id }}" {{ (int)$whatIfLoanId === (int)$item['loan']->id ? 'selected' : '' }}>{{ $item['loan']->title ?: $item['loan']->bank_name }} — {{ $item['loan']->loan_type }}</option>@endforeach</select>
                        <input class="field" type="number" min="0" step="0.01" name="what_if_extra" value="{{ $whatIfExtra > 0 ? $whatIfExtra : '' }}" placeholder="Доп. платеж (тг)">
                        <div><button class="btn btn-primary" type="submit">Рассчитать</button></div>
                    </form>
                    @if($whatIfResult && $selectedWhatIf)
                        <div class="db-grid-4" style="margin-top:8px; grid-template-columns:1fr 1fr;"><div style="border:1px solid #e5e7eb; border-radius:8px; padding:8px;"><div class="muted">Досрочно сейчас</div><div>Было: <strong>{{ number_format($whatIfResult['originalEarly'], 0, ',', ' ') }} ₸</strong></div><div>Станет: <strong>{{ number_format($whatIfResult['updatedEarly'], 0, ',', ' ') }} ₸</strong></div></div><div style="border:1px solid #a7f3d0; border-radius:8px; padding:8px; background:#ecfdf5;"><div class="muted">Доп. польза от +X</div><div style="font-size:22px; font-weight:700; color:#047857;">{{ number_format($whatIfResult['extraSavings'], 0, ',', ' ') }} ₸</div><div class="muted">Ускорит закрытие на {{ $whatIfResult['estimatedMonthsSaved'] }} мес.</div></div></div>
                    @endif
                </div>
                <div class="db-card" style="border:1px solid #e5e7eb; box-shadow:none; padding:12px; margin-bottom:10px;"><h3 style="margin:0 0 8px; color:#1f2937;">Прогноз закрытия кредитов</h3>@foreach ($forecastByBank as $bank)<details style="border:1px solid #e5e7eb; border-radius:8px; padding:8px; margin-bottom:8px;"><summary><strong>{{ $bank['bank'] }}</strong> • {{ $bank['items']->count() }} кредитов • {{ number_format($bank['totalMonthly'], 0, ',', ' ') }} ₸/мес</summary><div style="margin-top:8px;">@foreach ($bank['items'] as $item) @php($w = max(8, ($item['timeline']['monthsLeft'] / $bank['maxMonths']) * 100)) <div style="margin-bottom:8px;"><div class="db-actions" style="justify-content:space-between;"><span>{{ $item['loan']->title ?: $item['loan']->bank_name }}</span><span class="muted">{{ $item['timeline']['monthsLeft'] }} мес.</span></div><div class="db-progress"><div style="width:{{ $w }}%"></div></div></div> @endforeach</div></details>@endforeach</div>
                <div class="db-card" style="border:1px solid #e5e7eb; box-shadow:none; padding:12px;">
                    <h3 style="margin:0 0 8px; color:#1f2937;">Разбивка по банкам и типам</h3>
                    <div class="db-grid-4" style="grid-template-columns:1fr 1fr;">
                        <div>
                            <div class="muted" style="margin-bottom:6px;">По банкам</div>
                            @foreach($analyticsByBank as $row)
                                <div style="border:1px solid #e5e7eb; border-radius:8px; padding:8px; margin-bottom:6px;">
                                    <strong>{{ $row['label'] }}</strong>
                                    <div class="muted">{{ $row['count'] }} кредитов</div>
                                    <div>Досрочно: {{ number_format($row['early'], 0, ',', ' ') }} ₸</div>
                                    <div>Платеж/мес: {{ number_format($row['monthly'], 0, ',', ' ') }} ₸</div>
                                </div>
                            @endforeach
                        </div>
                        <div>
                            <div class="muted" style="margin-bottom:6px;">По типам</div>
                            @foreach($analyticsByType as $row)
                                <div style="border:1px solid #e5e7eb; border-radius:8px; padding:8px; margin-bottom:6px;">
                                    <strong>{{ $row['label'] }}</strong>
                                    <div class="muted">{{ $row['count'] }} кредитов</div>
                                    <div>Досрочно: {{ number_format($row['early'], 0, ',', ' ') }} ₸</div>
                                    <div>Платеж/мес: {{ number_format($row['monthly'], 0, ',', ' ') }} ₸</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="db-card" style="margin-bottom:12px;"><h2 class="db-section">Последние платежи</h2><div style="overflow:auto;"><table style="width:100%; border-collapse:collapse;"><thead><tr><th style="padding:8px;border-bottom:1px solid #e5e7eb;">Дата</th><th style="padding:8px;border-bottom:1px solid #e5e7eb;">Кредит</th><th style="padding:8px;border-bottom:1px solid #e5e7eb;">Факт</th></tr></thead><tbody>@forelse ($recentPayments as $payment)<tr><td style="padding:8px;border-bottom:1px solid #e5e7eb;">{{ optional($payment->payment_date)->format('d.m.Y') }}</td><td style="padding:8px;border-bottom:1px solid #e5e7eb;">{{ $payment->loan?->title ?: $payment->loan?->bank_name }}</td><td style="padding:8px;border-bottom:1px solid #e5e7eb;">{{ number_format($payment->actual_amount ?? 0, 0, ',', ' ') }} ₸</td></tr>@empty<tr><td colspan="3" class="muted" style="padding:8px;">Платежей пока нет.</td></tr>@endforelse</tbody></table></div></div>
            <div class="db-card" style="margin-bottom:12px;">
                <h2 class="db-section">Суммы по группам</h2>
                @foreach ($totalsByGroup as $group)
                    <div style="border:1px solid #e5e7eb; border-radius:8px; padding:8px; margin-bottom:6px;">
                        <strong>{{ $group['group'] }}</strong>
                        <div class="muted">{{ $group['count'] }} кредитов</div>
                        <div>Досрочно: {{ number_format($group['early'], 0, ',', ' ') }} ₸</div>
                        <div>Платеж/мес: {{ number_format($group['monthly'], 0, ',', ' ') }} ₸</div>
                    </div>
                @endforeach
            </div>
            <div class="db-card"><h2 class="db-section">Доход в месяц для оценки риска</h2><form method="get" class="db-grid-4" style="grid-template-columns:1fr auto; align-items:end;"><input type="hidden" name="bank" value="{{ $filterBank }}"><input type="hidden" name="group" value="{{ $filterGroup }}"><input type="hidden" name="term" value="{{ $filterTerm }}"><input type="hidden" name="sort" value="{{ $sort }}"><input type="hidden" name="dir" value="{{ $direction }}"><label>Доход в месяц<input class="field" type="number" min="0" step="0.01" name="monthly_income" value="{{ $monthlyIncome > 0 ? $monthlyIncome : '' }}" placeholder="Например 450000"></label><button class="btn btn-primary" type="submit">Оценить</button></form><div style="margin-top:10px; display:inline-block; border:1px solid {{ $riskColor }}; color:{{ $riskColor }}; border-radius:8px; padding:8px 12px; font-weight:700;">{{ $riskLabel }}@if(!is_null($burdenPercent)) ({{ number_format($burdenPercent, 1, ',', ' ') }}%)@endif</div><p class="muted" style="margin-top:8px;">Текущая нагрузка: {{ number_format($allActiveMonthly, 0, ',', ' ') }} ₸/мес</p></div>
        </div>
    </div>
</div>

<div id="stickyBar" class="db-sticky"><div class="db-sticky-inner"><div><strong id="selectedCount">0</strong> выбрано • Платеж/мес: <strong id="selectedMonthlySticky">0 ₸</strong></div><div class="db-actions"><button type="button" class="btn btn-green" onclick="submitMass('paid')">Оплачено за месяц</button><button type="button" class="btn btn-orange" onclick="submitMass('close')">Закрыла досрочно</button><button type="button" class="btn btn-light" onclick="toggleAll(false)">Снять выбор</button></div></div></div>

<script>
function formatMoney(n) { return new Intl.NumberFormat('ru-RU', { maximumFractionDigits: 0 }).format(n) + ' ₸'; }
function selectedCheckboxes() { return Array.from(document.querySelectorAll('.loan-check:checked')); }
function selectedIds() { return [...new Set(selectedCheckboxes().map((el) => el.value))]; }
function selectedMonthlyAmount() { const map = new Map(); document.querySelectorAll('.loan-check:checked').forEach((el) => { const id = el.dataset.loanId || el.value; if (!map.has(id)) map.set(id, Number(el.dataset.monthly || 0)); }); return Array.from(map.values()).reduce((a, b) => a + b, 0); }
function renderSelectionState() { const ids = selectedIds(); const monthly = selectedMonthlyAmount(); const elCount = document.getElementById('selectedCount'); const elMonth = document.getElementById('selectedMonthly'); const elMonthSticky = document.getElementById('selectedMonthlySticky'); const sticky = document.getElementById('stickyBar'); if (elCount) elCount.textContent = ids.length; if (elMonth) elMonth.textContent = formatMoney(monthly); if (elMonthSticky) elMonthSticky.textContent = formatMoney(monthly); if (sticky) sticky.style.display = ids.length ? 'block' : 'none'; }
function toggleAll(value) { document.querySelectorAll('.loan-check').forEach((el) => el.checked = value); renderSelectionState(); }
function pushIdsToForm(form, ids) { form.querySelectorAll('input[name="loan_ids[]"]').forEach((el) => el.remove()); ids.forEach((id) => { const hidden = document.createElement('input'); hidden.type = 'hidden'; hidden.name = 'loan_ids[]'; hidden.value = id; form.appendChild(hidden); }); }
function submitMass(type) { const ids = selectedIds(); if (!ids.length) { alert('Сначала выберите кредиты.'); return; } if (type === 'group') { const name = document.getElementById('groupNameInput').value.trim(); if (!name) { alert('Введите название группы.'); return; } const form = document.getElementById('mass-group-form'); document.getElementById('mass-group-name').value = name; pushIdsToForm(form, ids); form.submit(); return; } if (type === 'group-clear') { const form = document.getElementById('mass-clear-group-form'); pushIdsToForm(form, ids); form.submit(); return; } const form = document.getElementById(type === 'paid' ? 'mass-paid-form' : 'mass-close-form'); pushIdsToForm(form, ids); form.submit(); }
let calendarLoading = false;
async function updateCalendarPart(url) {
    if (calendarLoading) return;
    const current = document.getElementById('calendarCard');
    if (!current) { window.location.href = url; return; }
    calendarLoading = true;
    current.classList.add('db-card-loading');
    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });
        if (!response.ok) throw new Error('Bad response');
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const fresh = doc.getElementById('calendarCard');
        if (!fresh) throw new Error('Calendar not found');
        current.replaceWith(fresh);
        window.history.pushState({}, '', url);
    } catch (e) {
        window.location.href = url;
    } finally {
        calendarLoading = false;
    }
}
document.addEventListener('click', (event) => {
    const link = event.target.closest('a[data-calendar-link="1"]');
    if (!link) return;
    event.preventDefault();
    updateCalendarPart(link.href);
});
window.addEventListener('popstate', () => {
    window.location.reload();
});
document.querySelectorAll('.loan-check').forEach((el) => { el.addEventListener('change', (e) => { const target = e.currentTarget; const loanId = target.dataset.loanId || target.value; const checked = target.checked; document.querySelectorAll('.loan-check').forEach((peer) => { const peerLoanId = peer.dataset.loanId || peer.value; if (peerLoanId === loanId) peer.checked = checked; }); renderSelectionState(); }); });
renderSelectionState();
</script>
@endsection

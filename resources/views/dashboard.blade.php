@extends('layouts.app')

@section('content')
<style>
    .dash-head { margin-bottom: 14px; }
    .dash-head h1 { margin: 0; font-size: 32px; color: #1f2937; }
    .dash-head p { margin: 6px 0 0; color: #6b7280; }
    .metric-card { border: 1px solid #e5e7eb; }
    .metric-title { color: #6b7280; font-size: 12px; margin-bottom: 6px; }
    .metric-value { font-size: 30px; font-weight: 700; color: #1f2937; }
    .metric-value-green { color: #047857; }
    .section-title { margin-top: 0; font-size: 24px; color: #1f2937; }
    .table-soft tbody tr:hover { background: #f9fafb; }
</style>

<div class="card dash-head">
    <h1>Обзор кредитов</h1>
    <p>Контролируйте выплаты, экономию и план закрытия в одном месте.</p>
</div>

<div class="grid grid-4" style="margin-bottom:14px;">
    <div class="card metric-card"><div class="metric-title">Досрочно сейчас</div><div class="metric-value">{{ number_format($totalEarly, 0, ',', ' ') }} ₸</div></div>
    <div class="card metric-card"><div class="metric-title">Полностью до конца</div><div class="metric-value">{{ number_format($totalFull, 0, ',', ' ') }} ₸</div></div>
    <div class="card metric-card"><div class="metric-title">Экономия при закрытии сейчас</div><div class="metric-value metric-value-green">{{ number_format($totalSavings, 0, ',', ' ') }} ₸</div></div>
    <div class="card metric-card"><div class="metric-title">Платёж в месяц (активные)</div><div class="metric-value">{{ number_format($allActiveMonthly, 0, ',', ' ') }} ₸</div></div>
</div>

<div class="card" style="margin-bottom:14px;">
    <div class="flex" style="gap:6px;">
        <a class="btn btn-light" href="#loans-section">Кредиты</a>
        <a class="btn btn-light" href="#analytics-section">Аналитика</a>
        <a class="btn btn-light" href="#calendar-section">Календарь</a>
        <a class="btn btn-light" href="#groups-section">Группы</a>
        <a class="btn btn-light" href="{{ route('payments.schedule') }}">График оплат</a>
        <a class="btn btn-light" href="{{ route('backup.export.json') }}">Экспорт JSON</a>
    </div>
</div>

@if($visible->count() === 0)
<div class="card" style="margin-bottom:14px; border:1px dashed #cbd5e1;">
    <h2 style="margin-top:0;">Пока нет кредитов</h2>
    <p class="muted" style="font-size:14px;">
        Добавьте первый кредит вручную или загрузите CSV. После этого появятся прогнозы, экономия и календарь оплат.
    </p>
    <div class="flex">
        <a href="{{ route('loans.create') }}" class="btn btn-primary">+ Добавить кредит</a>
        <a href="{{ route('loans.export.sample-csv') }}" class="btn btn-light">Скачать шаблон CSV</a>
    </div>
    <div style="margin-top:10px; font-size:13px; color:#374151;">
        1. Заполните шаблон CSV.<br>
        2. Загрузите через “Импорт CSV” в блоке “Ваши кредиты”.<br>
        3. Проверьте итоги и отметьте оплаченные кредиты.
    </div>
</div>
@endif

@if($upcomingByBankDate->count())
<div class="card" style="margin-bottom:14px;">
    <h2 class="section-title">Платежи за 30 дней</h2>
    @foreach($upcomingByBankDate as $item)
        <div class="flex" style="justify-content:space-between; border:1px solid #e5e7eb; border-radius:8px; padding:8px; margin-bottom:6px;">
            <span>{{ $item['bankName'] }} • {{ $item['dateLabel'] }}</span>
            <strong>{{ number_format($item['total'], 0, ',', ' ') }} ₸{{ $item['count'] > 1 ? ' ('.$item['count'].')' : '' }}</strong>
        </div>
    @endforeach
</div>
@endif

@if($paymentReminders->count())
<div class="card" style="margin-bottom:14px;">
    <h2 class="section-title">Напоминания 3/1 день</h2>
    @foreach($paymentReminders as $item)
        <div class="flex" style="justify-content:space-between; border:1px solid #e5e7eb; border-radius:8px; padding:8px; margin-bottom:6px;">
            <div>
                <strong>{{ $item['loan']->title ?: $item['loan']->bank_name }}</strong>
                <div class="muted">{{ $item['days'] === 1 ? 'Платеж завтра' : 'Платеж через 3 дня' }} • {{ optional($item['loan']->next_payment_date)->format('d.m.Y') }}</div>
            </div>
            <strong>{{ number_format($item['loan']->monthly_payment, 0, ',', ' ') }} ₸</strong>
        </div>
    @endforeach
</div>
@endif

@if($recommendedLoan)
<div class="card" style="margin-bottom:14px; border:1px solid #a7f3d0;">
    <h2 class="section-title">Рекомендация месяца</h2>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
        <div>
            <strong>{{ $recommendedLoan['loan']->title ?: $recommendedLoan['loan']->bank_name }}</strong>
            <div class="muted">{{ $recommendedLoan['loan']->bank_name }} • {{ $recommendedLoan['loan']->loan_type }}</div>
            <div style="margin-top:6px;">Досрочно сейчас: <strong>{{ number_format($recommendedLoan['earlyPayoffNow'], 0, ',', ' ') }} ₸</strong></div>
            <div>Если платить до конца: <strong>{{ number_format($recommendedLoan['fullPaymentToEnd'], 0, ',', ' ') }} ₸</strong></div>
        </div>
        <div>
            <div class="muted">Потенциальная экономия</div>
            <div style="font-size:26px;font-weight:700;color:#047857;">{{ number_format($recommendedLoan['savingsIfCloseNow'], 0, ',', ' ') }} ₸</div>
            <div>Ставка: {{ $recommendedLoan['loan']->interest_rate_annual ?? 0 }}%</div>
            <div>Платеж/мес: {{ number_format($recommendedLoan['loan']->monthly_payment, 0, ',', ' ') }} ₸</div>
            <a href="{{ route('loans.show', $recommendedLoan['loan']) }}" class="btn btn-light" style="margin-top:6px;">Открыть кредит</a>
        </div>
    </div>
</div>
@endif

<div class="card" id="analytics-section" style="margin-bottom:14px;">
    <h2 class="section-title">Аналитика долгов</h2>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
        <div>
            <h3 style="margin-top:0;">По банкам</h3>
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
            <h3 style="margin-top:0;">По типам</h3>
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

<div class="card" style="margin-bottom:14px;">
    <h2 class="section-title">Сценарий: если внесу +X в этом месяце</h2>
    <form method="get" class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:10px; align-items:end;">
        <input type="hidden" name="bank" value="{{ $filterBank }}">
        <input type="hidden" name="group" value="{{ $filterGroup }}">
        <input type="hidden" name="status" value="{{ $filterStatus }}">
        <input type="hidden" name="term" value="{{ $filterTerm }}">
        <input type="hidden" name="min_amount" value="{{ $minAmount }}">
        <input type="hidden" name="max_amount" value="{{ $maxAmount }}">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="dir" value="{{ $direction }}">
        <input type="hidden" name="month" value="{{ $calendarBase->format('Y-m') }}">
        <label>Кредит
            <select class="field" name="what_if_loan_id">
                @foreach($visible as $item)
                    <option value="{{ $item['loan']->id }}" {{ (int)$whatIfLoanId === (int)$item['loan']->id ? 'selected' : '' }}>
                        {{ $item['loan']->title ?: $item['loan']->bank_name }} — {{ $item['loan']->loan_type }}
                    </option>
                @endforeach
            </select>
        </label>
        <label>Доп. платеж
            <input class="field" type="number" min="0" step="0.01" name="what_if_extra" value="{{ $whatIfExtra > 0 ? $whatIfExtra : '' }}" placeholder="Например 50000">
        </label>
        <button class="btn btn-primary" type="submit">Рассчитать</button>
    </form>
    @if($whatIfResult && $selectedWhatIf)
        <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:10px; margin-top:10px;">
            <div style="border:1px solid #e5e7eb; border-radius:8px; padding:8px;">
                <div class="muted">Досрочно сейчас</div>
                <div>Было: <strong>{{ number_format($whatIfResult['originalEarly'], 0, ',', ' ') }} ₸</strong></div>
                <div>Станет: <strong>{{ number_format($whatIfResult['updatedEarly'], 0, ',', ' ') }} ₸</strong></div>
            </div>
            <div style="border:1px solid #a7f3d0; border-radius:8px; padding:8px; background:#ecfdf5;">
                <div class="muted">Доп. польза от +X</div>
                <div style="font-size:24px; font-weight:700; color:#047857;">{{ number_format($whatIfResult['extraSavings'], 0, ',', ' ') }} ₸</div>
                <div class="muted">Ускорит закрытие примерно на {{ $whatIfResult['estimatedMonthsSaved'] }} мес.</div>
            </div>
        </div>
    @endif
</div>

<div class="card" id="loans-section" style="margin-bottom:14px;">
    <style>
        .filter-chip { border:1px solid #d1d5db; border-radius:999px; padding:4px 10px; font-size:12px; background:#f8fafc; color:#334155; }
        #loanFilters summary { list-style:none; cursor:pointer; font-weight:700; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; background:#f8fafc; }
        #loanFilters summary::-webkit-details-marker { display:none; }
        #loanFilters[open] summary { margin-bottom:10px; }
    </style>

    <div class="flex" style="justify-content:space-between; margin-bottom:6px;">
        <h2 style="margin:0;font-size:24px;color:#1f2937;">Ваши кредиты ({{ $visible->count() }})</h2>
        <a href="{{ route('loans.create') }}" class="btn btn-primary">+ Добавить кредит</a>
    </div>

    @php($hasFilter = $filterBank !== 'all' || $filterGroup !== 'all' || $filterStatus !== 'active' || $filterTerm !== 'all' || ($minAmount !== null && $minAmount !== '') || ($maxAmount !== null && $maxAmount !== ''))
    <div style="margin-bottom:10px;">
        <details id="loanFilters" class="card" style="margin:0;" open>
            <summary>Фильтры и сортировка</summary>
            <form method="get" class="grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:10px; align-items:end;">
                <label>Банк
                    <select class="field" name="bank">
                        <option value="all">Все банки</option>
                        @foreach ($banks as $bank)
                            <option value="{{ $bank }}" {{ $filterBank === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Группа
                    <select class="field" name="group">
                        <option value="all">Все группы</option>
                        @foreach ($groups as $group)
                            <option value="{{ $group }}" {{ $filterGroup === $group ? 'selected' : '' }}>{{ $group }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Статус
                    <select class="field" name="status">
                        <option value="active" {{ $filterStatus === 'active' ? 'selected' : '' }}>Только активные</option>
                        <option value="closed" {{ $filterStatus === 'closed' ? 'selected' : '' }}>Только закрытые</option>
                        <option value="all" {{ $filterStatus === 'all' ? 'selected' : '' }}>Все</option>
                    </select>
                </label>
                <label>Срок
                    <select class="field" name="term">
                        <option value="all" {{ $filterTerm === 'all' ? 'selected' : '' }}>Любой</option>
                        <option value="overdue" {{ $filterTerm === 'overdue' ? 'selected' : '' }}>Срок вышел</option>
                        <option value="upTo12" {{ $filterTerm === 'upTo12' ? 'selected' : '' }}>До 12 мес.</option>
                        <option value="from12To24" {{ $filterTerm === 'from12To24' ? 'selected' : '' }}>12-24 мес.</option>
                        <option value="over24" {{ $filterTerm === 'over24' ? 'selected' : '' }}>Более 24 мес.</option>
                    </select>
                </label>
                <label>Мин. досрочно
                    <input class="field" type="number" step="0.01" min="0" name="min_amount" value="{{ $minAmount }}">
                </label>
                <label>Макс. досрочно
                    <input class="field" type="number" step="0.01" min="0" name="max_amount" value="{{ $maxAmount }}">
                </label>
                <label>Сортировать
                    <select class="field" name="sort">
                        @php($sortOptions = ['end' => 'По сроку', 'bank' => 'По банку', 'group' => 'По группе', 'term' => 'По мес. осталось', 'early' => 'По досрочно', 'full' => 'По полностью', 'savings' => 'По экономии', 'monthly' => 'По платежу', 'rate' => 'По ставке'])
                        @foreach ($sortOptions as $key => $label)
                            <option value="{{ $key }}" {{ $sort === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Порядок
                    <select class="field" name="dir">
                        <option value="asc" {{ $direction === 'asc' ? 'selected' : '' }}>По возрастанию</option>
                        <option value="desc" {{ $direction === 'desc' ? 'selected' : '' }}>По убыванию</option>
                    </select>
                </label>
                <div class="flex" style="gap:8px;">
                    <button class="btn btn-primary" type="submit">Применить</button>
                    @if($hasFilter)
                        <a class="btn btn-light" href="{{ route('dashboard', array_merge(request()->query(), ['bank' => 'all', 'group' => 'all', 'status' => 'active', 'term' => 'all', 'min_amount' => null, 'max_amount' => null])) }}">Сбросить</a>
                    @endif
                </div>
            </form>
        </details>
    </div>

    <div class="flex" style="margin-bottom:10px;">
        @if($filterBank !== 'all') <span class="filter-chip">Банк: {{ $filterBank }}</span> @endif
        @if($filterGroup !== 'all') <span class="filter-chip">Группа: {{ $filterGroup }}</span> @endif
        @if($filterStatus !== 'active') <span class="filter-chip">Статус: {{ $filterStatus === 'all' ? 'Все' : 'Закрытые' }}</span> @endif
        @if($filterTerm !== 'all') <span class="filter-chip">Срок: {{ $filterTerm }}</span> @endif
        @if($minAmount !== null && $minAmount !== '') <span class="filter-chip">Мин: {{ number_format((float)$minAmount, 0, ',', ' ') }} ₸</span> @endif
        @if($maxAmount !== null && $maxAmount !== '') <span class="filter-chip">Макс: {{ number_format((float)$maxAmount, 0, ',', ' ') }} ₸</span> @endif
        <span class="filter-chip">Сорт: {{ $sortOptions[$sort] ?? 'По сроку' }} ({{ $direction === 'asc' ? '↑' : '↓' }})</span>
    </div>

    <div class="flex" style="margin-bottom:10px;">
        <a href="{{ route('loans.export.csv') }}" class="btn btn-light">Экспорт CSV</a>
        <a href="{{ route('loans.export.sample-csv') }}" class="btn btn-light">Шаблон CSV</a>
        <form method="post" action="{{ route('loans.import.csv') }}" enctype="multipart/form-data" class="flex">
            @csrf
            <input class="field" type="file" name="csv_file" accept=".csv,.txt" style="max-width:180px;">
            <button type="submit" class="btn btn-light">Импорт CSV</button>
        </form>
        <form method="post" action="{{ route('backup.import.json') }}" enctype="multipart/form-data" class="flex">
            @csrf
            <input class="field" type="file" name="json_file" accept=".json,.txt" style="max-width:180px;">
            <label style="display:flex;align-items:center;gap:4px;font-size:12px;">
                <input type="checkbox" name="replace_existing" value="1"> Заменить
            </label>
            <button type="submit" class="btn btn-light">Импорт JSON</button>
        </form>
    </div>

    <form id="mass-paid-form" method="post" action="{{ route('dashboard.mark-paid') }}">@csrf</form>
    <form id="mass-close-form" method="post" action="{{ route('dashboard.close-early') }}">@csrf</form>
    <form id="mass-group-form" method="post" action="{{ route('dashboard.assign-group') }}">@csrf<input type="hidden" name="group_name" id="mass-group-name"></form>
    <form id="mass-clear-group-form" method="post" action="{{ route('dashboard.clear-group') }}">@csrf</form>

    <div class="flex" style="margin-bottom:8px;">
        <button type="button" class="btn btn-green" onclick="submitMass('paid')">Оплачено за месяц</button>
        <button type="button" class="btn btn-orange" onclick="submitMass('close')">Закрыла досрочно</button>
        <input class="field" type="text" id="groupNameInput" placeholder="Название группы" style="max-width:220px;">
        <button type="button" class="btn btn-gray" onclick="submitMass('group')">В группу</button>
        <button type="button" class="btn btn-light" onclick="submitMass('group-clear')">Убрать группу</button>
        <button type="button" class="btn btn-light" onclick="toggleAll(true)">Выбрать все</button>
        <button type="button" class="btn btn-light" onclick="toggleAll(false)">Снять выбор</button>
        <span class="muted" style="margin-left:auto;">Платеж/мес (выборочно): <strong id="selectedMonthly">0 ₸</strong></span>
    </div>

    <div class="only-desktop table-soft" style="overflow:auto;">
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th>Название</th>
                    <th>Банк</th>
                    <th>Тип</th>
                    <th>Группа</th>
                    <th>Прогресс</th>
                    <th>Досрочно</th>
                    <th>Полностью</th>
                    <th>Экономия</th>
                    <th>Платеж/мес</th>
                    <th>Ставка</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($visible as $item)
                    @php($loan = $item['loan'])
                    @php($timeline = $item['timeline'])
                    <tr>
                        <td><input type="checkbox" class="loan-check" data-loan-id="{{ $loan->id }}" value="{{ $loan->id }}" data-monthly="{{ $loan->monthly_payment }}"></td>
                        <td><strong>{{ $loan->title ?: '—' }}</strong><div class="muted">{{ $loan->status }}</div></td>
                        <td>{{ $loan->bank_name }}</td>
                        <td>{{ $loan->loan_type }}</td>
                        <td>{{ $loan->group_name ?: 'Без группы' }}</td>
                        <td style="min-width:170px;">
                            <div>{{ $timeline['monthsPaid'] }} / {{ $timeline['monthsTotal'] }} мес.</div>
                            <div class="progress"><div style="width:{{ $timeline['progressPercent'] }}%"></div></div>
                            <div class="muted">Осталось: {{ $timeline['monthsLeft'] }} мес.</div>
                        </td>
                        <td>{{ number_format($item['earlyPayoffNow'], 0, ',', ' ') }} ₸</td>
                        <td>{{ number_format($item['fullPaymentToEnd'], 0, ',', ' ') }} ₸</td>
                        <td style="color:#047857;font-weight:600;">{{ number_format($item['savingsIfCloseNow'], 0, ',', ' ') }} ₸</td>
                        <td>{{ number_format($loan->monthly_payment, 0, ',', ' ') }} ₸</td>
                        <td>{{ $loan->interest_rate_annual ?? 0 }}%</td>
                        <td>
                            <div class="flex">
                                <a class="btn btn-light" href="{{ route('loans.show', $loan) }}">Подробно</a>
                                <a class="btn btn-light" href="{{ route('loans.edit', $loan) }}">Изменить</a>
                                <form method="post" action="{{ route('loans.destroy', $loan) }}" onsubmit="return confirm('Удалить кредит?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-light" type="submit">Удалить</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="muted">Кредитов пока нет.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6">ИТОГО</th>
                    <th>{{ number_format($totalEarly, 0, ',', ' ') }} ₸</th>
                    <th>{{ number_format($totalFull, 0, ',', ' ') }} ₸</th>
                    <th style="color:#047857;">{{ number_format($totalSavings, 0, ',', ' ') }} ₸</th>
                    <th>{{ number_format($totalMonthly, 0, ',', ' ') }} ₸</th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="only-mobile">
        @forelse ($visible as $item)
            @php($loan = $item['loan'])
            @php($timeline = $item['timeline'])
            <div style="border:1px solid #e5e7eb; border-radius:10px; padding:10px; margin-bottom:8px;">
                <div class="flex" style="justify-content:space-between;">
                    <label style="display:flex;align-items:center;gap:6px;">
                        <input type="checkbox" class="loan-check" data-loan-id="{{ $loan->id }}" value="{{ $loan->id }}" data-monthly="{{ $loan->monthly_payment }}">
                        <strong>{{ $loan->title ?: ($loan->bank_name.' • '.$loan->loan_type) }}</strong>
                    </label>
                    <span class="muted">{{ $loan->status }}</span>
                </div>
                <div class="muted">{{ $loan->bank_name }} • {{ $loan->loan_type }} • {{ $loan->group_name ?: 'Без группы' }}</div>
                <div style="margin-top:8px;">
                    <div>{{ $timeline['monthsPaid'] }} / {{ $timeline['monthsTotal'] }} мес. (осталось {{ $timeline['monthsLeft'] }})</div>
                    <div class="progress"><div style="width:{{ $timeline['progressPercent'] }}%"></div></div>
                </div>
                <div class="grid" style="grid-template-columns:1fr 1fr; gap:8px; margin-top:8px;">
                    <div><div class="muted">Досрочно</div><strong>{{ number_format($item['earlyPayoffNow'], 0, ',', ' ') }} ₸</strong></div>
                    <div><div class="muted">Полностью</div><strong>{{ number_format($item['fullPaymentToEnd'], 0, ',', ' ') }} ₸</strong></div>
                    <div><div class="muted">Экономия</div><strong style="color:#047857;">{{ number_format($item['savingsIfCloseNow'], 0, ',', ' ') }} ₸</strong></div>
                    <div><div class="muted">Платеж/мес</div><strong>{{ number_format($loan->monthly_payment, 0, ',', ' ') }} ₸</strong></div>
                </div>
                <div class="flex" style="margin-top:8px;">
                    <a class="btn btn-light" href="{{ route('loans.show', $loan) }}">Подробно</a>
                    <a class="btn btn-light" href="{{ route('loans.edit', $loan) }}">Изменить</a>
                </div>
            </div>
        @empty
            <div class="muted">Кредитов пока нет.</div>
        @endforelse
        <div style="border-top:1px solid #e5e7eb; margin-top:8px; padding-top:8px;">
            <div><strong>Итого досрочно:</strong> {{ number_format($totalEarly, 0, ',', ' ') }} ₸</div>
            <div><strong>Итого полностью:</strong> {{ number_format($totalFull, 0, ',', ' ') }} ₸</div>
            <div><strong>Итого экономия:</strong> <span style="color:#047857;">{{ number_format($totalSavings, 0, ',', ' ') }} ₸</span></div>
            <div><strong>Итого платеж/мес:</strong> {{ number_format($totalMonthly, 0, ',', ' ') }} ₸</div>
        </div>
    </div>
</div>

<div class="card" id="calendar-section" style="margin-bottom:14px;">
    <h2 class="section-title">Календарь оплат</h2>
    @php($prevMonth = $calendarBase->copy()->subMonth()->format('Y-m'))
    @php($nextMonth = $calendarBase->copy()->addMonth()->format('Y-m'))
    <div class="flex" style="justify-content:space-between; margin-bottom:8px;">
        <a class="btn btn-light" href="{{ route('dashboard', array_merge(request()->query(), ['month' => $prevMonth, 'calendar_date' => null])) }}">←</a>
        <strong>{{ $calendarBase->translatedFormat('F Y') }}</strong>
        <a class="btn btn-light" href="{{ route('dashboard', array_merge(request()->query(), ['month' => $nextMonth, 'calendar_date' => null])) }}">→</a>
    </div>
    <div class="grid" style="grid-template-columns:repeat(7, minmax(0,1fr)); gap:6px; margin-bottom:8px; font-size:12px; color:#6b7280;">
        <div>Пн</div><div>Вт</div><div>Ср</div><div>Чт</div><div>Пт</div><div>Сб</div><div>Вс</div>
    </div>
    <div class="grid" style="grid-template-columns:repeat(7, minmax(0,1fr)); gap:6px;">
        @php($firstDay = $calendarBase->copy()->startOfMonth())
        @php($pad = $firstDay->dayOfWeekIso - 1)
        @for($i=0; $i < $pad; $i++)
            <div style="height:66px; background:#f9fafb; border-radius:8px;"></div>
        @endfor
        @for($d=1; $d <= $calendarBase->daysInMonth; $d++)
            @php($dateKey = $calendarBase->copy()->day($d)->toDateString())
            @php($entry = $calendarEntries->get($dateKey))
            @php($selected = $selectedCalendarDate === $dateKey)
            <a href="{{ route('dashboard', array_merge(request()->query(), ['calendar_date' => $selected ? null : $dateKey])) }}" style="height:66px; border:1px solid {{ $selected ? '#4f46e5' : ($entry ? '#86efac' : '#e5e7eb') }}; background: {{ $selected ? '#eef2ff' : ($entry ? '#f0fdf4' : '#fff') }}; border-radius:8px; text-decoration:none; color:#111827; padding:4px; font-size:11px;">
                <div style="font-weight:600;">{{ $d }}</div>
                @if($entry)
                    <div style="font-size:10px;">{{ number_format($entry['total'], 0, ',', ' ') }} ₸</div>
                @endif
            </a>
        @endfor
    </div>

    @if($selectedCalendarDate)
        <div style="margin-top:10px; border-top:1px solid #e5e7eb; padding-top:10px;">
            <strong>Платежи на {{ \Illuminate\Support\Carbon::parse($selectedCalendarDate)->format('d.m.Y') }}</strong>
            @if($selectedCalendarLoans->count())
                @foreach($selectedCalendarLoans as $loan)
                    <div class="flex" style="justify-content:space-between; border:1px solid #e5e7eb; border-radius:8px; padding:8px; margin-top:6px;">
                        <span>{{ $loan->title ?: $loan->bank_name }} <span class="muted">({{ $loan->loan_type }})</span></span>
                        <strong>{{ number_format($loan->monthly_payment, 0, ',', ' ') }} ₸</strong>
                    </div>
                @endforeach
            @else
                <div class="muted" style="margin-top:6px;">На выбранную дату платежей нет.</div>
            @endif
        </div>
    @endif
</div>

<div class="card" style="margin-bottom:14px;">
    <h2 class="section-title">Прогноз закрытия кредитов по банкам</h2>
    @foreach ($forecastByBank as $bank)
        <details style="border:1px solid #e5e7eb; border-radius:8px; padding:8px; margin-bottom:8px;">
            <summary><strong>{{ $bank['bank'] }}</strong> • {{ $bank['items']->count() }} кредитов • {{ number_format($bank['totalMonthly'], 0, ',', ' ') }} ₸/мес</summary>
            <div style="margin-top:8px;">
                @foreach ($bank['items'] as $item)
                    @php($w = max(8, ($item['timeline']['monthsLeft'] / $bank['maxMonths']) * 100))
                    <div style="margin-bottom:8px;">
                        <div class="flex" style="justify-content:space-between;">
                            <span>{{ $item['loan']->title ?: $item['loan']->loan_type }}</span>
                            <span class="muted">{{ $item['timeline']['monthsLeft'] }} мес.</span>
                        </div>
                        <div class="progress"><div style="width:{{ $w }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </details>
    @endforeach
</div>

<div class="card" style="margin-bottom:14px;">
    <h2 class="section-title">Последние платежи</h2>
    <div style="overflow:auto;">
        <table>
            <thead><tr><th>Дата</th><th>Кредит</th><th>План</th><th>Факт</th><th>Досрочно</th><th>Комментарий</th></tr></thead>
            <tbody>
            @forelse ($recentPayments as $payment)
                <tr>
                    <td>{{ optional($payment->payment_date)->format('d.m.Y') }}</td>
                    <td>{{ $payment->loan?->title ?: $payment->loan?->bank_name }}</td>
                    <td>{{ number_format($payment->planned_amount, 0, ',', ' ') }} ₸</td>
                    <td>{{ number_format($payment->actual_amount ?? 0, 0, ',', ' ') }} ₸</td>
                    <td>{{ number_format($payment->extra_payment ?? 0, 0, ',', ' ') }} ₸</td>
                    <td>{{ $payment->note }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">Платежей пока нет.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-bottom:14px;">
    <h2 class="section-title">Доход в месяц для оценки риска</h2>
    <form method="get" class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:10px; align-items:end;">
        <input type="hidden" name="bank" value="{{ $filterBank }}">
        <input type="hidden" name="group" value="{{ $filterGroup }}">
        <input type="hidden" name="status" value="{{ $filterStatus }}">
        <input type="hidden" name="term" value="{{ $filterTerm }}">
        <input type="hidden" name="min_amount" value="{{ $minAmount }}">
        <input type="hidden" name="max_amount" value="{{ $maxAmount }}">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="dir" value="{{ $direction }}">
        <input type="hidden" name="month" value="{{ $calendarBase->format('Y-m') }}">
        <label>Доход в месяц
            <input class="field" type="number" min="0" step="0.01" name="monthly_income" value="{{ $monthlyIncome > 0 ? $monthlyIncome : '' }}" placeholder="Например 450000">
        </label>
        <button class="btn btn-primary" type="submit">Оценить</button>
    </form>
    <div style="margin-top:10px; color:{{ $riskColor }}; font-weight:700;">{{ $riskLabel }}@if(!is_null($burdenPercent)) ({{ number_format($burdenPercent, 1, ',', ' ') }}%)@endif</div>
    <div class="muted">Текущая нагрузка: {{ number_format($allActiveMonthly, 0, ',', ' ') }} ₸/мес</div>
</div>

<div class="card" id="groups-section" style="margin-bottom:90px;">
    <h2 class="section-title">Суммы по группам</h2>
    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">
        @foreach ($totalsByGroup as $group)
            <div style="border:1px solid #e5e7eb; border-radius:8px; padding:10px;">
                <strong>{{ $group['group'] }}</strong>
                <div class="muted">{{ $group['count'] }} кредитов</div>
                <div>Досрочно: {{ number_format($group['early'], 0, ',', ' ') }} ₸</div>
                <div>Платеж/мес: {{ number_format($group['monthly'], 0, ',', ' ') }} ₸</div>
            </div>
        @endforeach
    </div>
</div>

<div id="stickyBar" style="position:fixed;left:0;right:0;bottom:0;background:rgba(255,255,255,.97);backdrop-filter: blur(4px);border-top:1px solid #e5e7eb;padding:10px 14px;display:none;z-index:50;">
    <div class="wrap" style="padding:0;display:flex;justify-content:space-between;align-items:center;gap:10px;">
        <div><strong id="selectedCount">0</strong> выбрано • Платеж/мес: <strong id="selectedMonthlySticky">0 ₸</strong></div>
        <div class="flex">
            <button type="button" class="btn btn-green" onclick="submitMass('paid')">Оплачено</button>
            <button type="button" class="btn btn-orange" onclick="submitMass('close')">Закрыла досрочно</button>
            <button type="button" class="btn btn-light" onclick="toggleAll(false)">Снять</button>
        </div>
    </div>
</div>

<script>
function formatMoney(n) { return new Intl.NumberFormat('ru-RU', { maximumFractionDigits: 0 }).format(n) + ' ₸'; }
function selectedCheckboxes() { return Array.from(document.querySelectorAll('.loan-check:checked')); }
function selectedIds() {
    const ids = selectedCheckboxes().map((el) => el.value);
    return [...new Set(ids)];
}
function selectedMonthlyAmount() {
    const map = new Map();
    document.querySelectorAll('.loan-check:checked').forEach((el) => {
        const id = el.dataset.loanId || el.value;
        if (!map.has(id)) map.set(id, Number(el.dataset.monthly || 0));
    });
    return Array.from(map.values()).reduce((a, b) => a + b, 0);
}
function renderSelectionState() {
    const ids = selectedIds();
    const monthly = selectedMonthlyAmount();
    document.getElementById('selectedCount').textContent = ids.length;
    document.getElementById('selectedMonthly').textContent = formatMoney(monthly);
    document.getElementById('selectedMonthlySticky').textContent = formatMoney(monthly);
    document.getElementById('stickyBar').style.display = ids.length ? 'block' : 'none';
}
function toggleAll(value) {
    document.querySelectorAll('.loan-check').forEach((el) => el.checked = value);
    renderSelectionState();
}
function pushIdsToForm(form, ids) {
    form.querySelectorAll('input[name="loan_ids[]"]').forEach((el) => el.remove());
    ids.forEach((id) => {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'loan_ids[]';
        hidden.value = id;
        form.appendChild(hidden);
    });
}
function submitMass(type) {
    const ids = selectedIds();
    if (!ids.length) {
        alert('Сначала выберите кредиты.');
        return;
    }

    if (type === 'group') {
        const groupName = document.getElementById('groupNameInput').value.trim();
        if (!groupName) {
            alert('Введите название группы.');
            return;
        }
        const form = document.getElementById('mass-group-form');
        document.getElementById('mass-group-name').value = groupName;
        pushIdsToForm(form, ids);
        form.submit();
        return;
    }

    if (type === 'group-clear') {
        const form = document.getElementById('mass-clear-group-form');
        pushIdsToForm(form, ids);
        form.submit();
        return;
    }

    const form = document.getElementById(type === 'paid' ? 'mass-paid-form' : 'mass-close-form');
    pushIdsToForm(form, ids);
    form.submit();
}

document.querySelectorAll('.loan-check').forEach((el) => {
    el.addEventListener('change', (e) => {
        const target = e.currentTarget;
        const loanId = target.dataset.loanId || target.value;
        const checked = target.checked;
        document.querySelectorAll('.loan-check').forEach((peer) => {
            const peerLoanId = peer.dataset.loanId || peer.value;
            if (peerLoanId === loanId) {
                peer.checked = checked;
            }
        });
        renderSelectionState();
    });
});
renderSelectionState();
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="card" style="margin-bottom:14px;">
    @php($prevMonth = $baseMonth->copy()->subMonth()->format('Y-m'))
    @php($nextMonth = $baseMonth->copy()->addMonth()->format('Y-m'))
    <div class="flex" style="justify-content:space-between; margin-bottom:10px;">
        <div>
            <a href="{{ route('payments.index') }}" style="color:#4f46e5;font-weight:600;">← Назад к платежам</a>
            <h1 style="margin:8px 0 0;font-size:30px;color:#1f2937;">График оплат</h1>
        </div>
        <div class="flex">
            <a class="btn btn-light" href="{{ route('payments.schedule', ['month' => $prevMonth]) }}">←</a>
            <strong style="padding:8px 4px;">{{ $baseMonth->translatedFormat('F Y') }}</strong>
            <a class="btn btn-light" href="{{ route('payments.schedule', ['month' => $nextMonth]) }}">→</a>
        </div>
    </div>

    <div class="muted" style="margin-bottom:8px;">План платежей за месяц: <strong style="color:#111827;">{{ number_format($monthTotal, 0, ',', ' ') }} ₸</strong></div>

    <form id="schedule-form" method="post" action="{{ route('payments.schedule.mark-paid') }}">
        @csrf
        @foreach($groups as $group)
            @php($date = \Illuminate\Support\Carbon::parse($group['date']))
            <div style="border:1px solid #e5e7eb; border-radius:10px; padding:10px; margin-bottom:10px;">
                <div class="flex" style="justify-content:space-between; margin-bottom:8px;">
                    <div>
                        <strong>{{ $date->format('d.m.Y') }}</strong>
                        <div class="muted">{{ $group['items']->count() }} кредитов</div>
                    </div>
                    <strong>{{ number_format($group['total'], 0, ',', ' ') }} ₸</strong>
                </div>

                @foreach($group['items'] as $loan)
                    <label class="flex" style="justify-content:space-between; border:1px solid #f1f5f9; border-radius:8px; padding:8px; margin-bottom:6px;">
                        <span>
                            <input type="checkbox" class="schedule-check" name="loan_ids[]" value="{{ $loan->id }}" data-monthly="{{ $loan->monthly_payment }}">
                            <strong>{{ $loan->title ?: $loan->bank_name }}</strong>
                            <span class="muted"> • {{ $loan->loan_type }}</span>
                        </span>
                        <strong>{{ number_format($loan->monthly_payment, 0, ',', ' ') }} ₸</strong>
                    </label>
                @endforeach
            </div>
        @endforeach

        @if($groups->isEmpty())
            <div class="muted">На этот месяц в графике платежей ничего нет.</div>
        @endif

        <div class="flex" style="margin-top:10px;">
            <button type="button" class="btn btn-light" onclick="toggleSchedule(true)">Выбрать все</button>
            <button type="button" class="btn btn-light" onclick="toggleSchedule(false)">Снять выбор</button>
            <button type="submit" class="btn btn-green">Оплачено за этот месяц (выбранные)</button>
            <span class="muted" style="margin-left:auto;">Выбрано: <strong id="selectedCount">0</strong> • Сумма: <strong id="selectedTotal">0 ₸</strong></span>
        </div>
    </form>
</div>

<script>
function fmt(n) { return new Intl.NumberFormat('ru-RU', { maximumFractionDigits: 0 }).format(n) + ' ₸'; }
function refreshScheduleSummary() {
    const checks = Array.from(document.querySelectorAll('.schedule-check:checked'));
    const total = checks.reduce((sum, el) => sum + Number(el.dataset.monthly || 0), 0);
    document.getElementById('selectedCount').textContent = checks.length;
    document.getElementById('selectedTotal').textContent = fmt(total);
}
function toggleSchedule(value) {
    document.querySelectorAll('.schedule-check').forEach((el) => el.checked = value);
    refreshScheduleSummary();
}
document.querySelectorAll('.schedule-check').forEach((el) => el.addEventListener('change', refreshScheduleSummary));
refreshScheduleSummary();
</script>
@endsection

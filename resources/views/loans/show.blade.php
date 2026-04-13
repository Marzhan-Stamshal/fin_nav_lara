@extends('layouts.app')

@section('content')
@php($timeline = $summary['timeline'])
<div class="card" style="margin-bottom:14px;">
    <div class="flex" style="justify-content:space-between;">
        <h1 style="margin:0;">{{ $loan->title ?: $loan->bank_name }}</h1>
        <div class="flex">
            @if($loan->status !== 'закрыт')
                <form method="post" action="{{ route('loans.mark-paid', $loan) }}">@csrf<button class="btn btn-green" type="submit">Оплачено за месяц</button></form>
                <form method="post" action="{{ route('loans.close-early', $loan) }}" onsubmit="return confirm('Закрыть кредит досрочно?')">@csrf<button class="btn btn-orange" type="submit">Закрыла досрочно</button></form>
            @endif
            <a class="btn btn-light" href="{{ route('loans.edit', $loan) }}">Редактировать</a>
            <a class="btn btn-primary" href="{{ route('dashboard') }}">Назад</a>
        </div>
    </div>
    <p class="muted">{{ $loan->bank_name }} • {{ $loan->loan_type }} • {{ $loan->status }}</p>

    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
        <div><div class="muted">Досрочно сейчас</div><strong>{{ number_format($summary['earlyPayoffNow'], 0, ',', ' ') }} ₸</strong></div>
        <div><div class="muted">Полностью до конца</div><strong>{{ number_format($summary['fullPaymentToEnd'], 0, ',', ' ') }} ₸</strong></div>
        <div><div class="muted">Экономия</div><strong style="color:#047857;">{{ number_format($summary['savingsIfCloseNow'], 0, ',', ' ') }} ₸</strong></div>
        <div><div class="muted">Платеж/мес</div><strong>{{ number_format($loan->monthly_payment, 0, ',', ' ') }} ₸</strong></div>
    </div>

    <div style="margin-top:10px;">
        <div>{{ $timeline['monthsPaid'] }} / {{ $timeline['monthsTotal'] }} месяцев оплачено</div>
        <div class="progress"><div style="width:{{ $timeline['progressPercent'] }}%"></div></div>
        <div class="muted">Осталось: {{ $timeline['monthsLeft'] }} мес.</div>
    </div>
</div>

<div class="card">
    <h2 style="margin-top:0;">Последние платежи по кредиту</h2>
    <table>
        <thead><tr><th>Дата</th><th>План</th><th>Факт</th><th>Досрочно</th><th>Статус</th><th>Комментарий</th></tr></thead>
        <tbody>
        @forelse ($payments as $payment)
            <tr>
                <td>{{ optional($payment->payment_date)->format('d.m.Y') }}</td>
                <td>{{ number_format($payment->planned_amount, 0, ',', ' ') }} ₸</td>
                <td>{{ number_format($payment->actual_amount ?? 0, 0, ',', ' ') }} ₸</td>
                <td>{{ number_format($payment->extra_payment ?? 0, 0, ',', ' ') }} ₸</td>
                <td>{{ $payment->status }}</td>
                <td>{{ $payment->note }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">Платежей пока нет.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection

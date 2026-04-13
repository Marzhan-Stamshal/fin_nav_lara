@extends('layouts.app')

@section('content')
<div class="card">
    <div class="flex" style="justify-content:space-between;">
        <h1 style="margin-top:0;">История платежей</h1>
        <a class="btn btn-primary" href="{{ route('payments.schedule') }}">График оплат</a>
    </div>
    <div style="overflow:auto;">
        <table>
            <thead><tr><th>Дата</th><th>Кредит</th><th>Банк</th><th>План</th><th>Факт</th><th>Досрочно</th><th>Статус</th><th>Комментарий</th></tr></thead>
            <tbody>
            @forelse ($payments as $payment)
                <tr>
                    <td>{{ optional($payment->payment_date)->format('d.m.Y') }}</td>
                    <td>{{ $payment->loan?->title ?: ($payment->loan?->loan_type ?? '—') }}</td>
                    <td>{{ $payment->loan?->bank_name ?? '—' }}</td>
                    <td>{{ number_format($payment->planned_amount, 0, ',', ' ') }} ₸</td>
                    <td>{{ number_format($payment->actual_amount ?? 0, 0, ',', ' ') }} ₸</td>
                    <td>{{ number_format($payment->extra_payment ?? 0, 0, ',', ' ') }} ₸</td>
                    <td>{{ $payment->status }}</td>
                    <td>{{ $payment->note }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted">Записей нет.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:12px;">{{ $payments->links() }}</div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="card" style="margin-bottom:14px;">
    <div class="flex" style="justify-content:space-between;">
        <div>
            <a href="{{ route('dashboard') }}" style="color:#4f46e5;font-weight:600;">← Назад на дашборд</a>
            <h1 style="margin:8px 0 0;font-size:30px;color:#1f2937;">История платежей</h1>
        </div>
        <div class="flex">
            <button type="button" class="btn btn-gray" onclick="exportPaymentsCsv()">Экспорт в CSV</button>
            <a class="btn btn-primary" href="{{ route('payments.schedule') }}">График оплат</a>
        </div>
    </div>
    <p style="color:#6b7280;margin:10px 0 0;">Здесь отображаются все платежи по вашим кредитам.</p>
</div>

<div class="card">
    <div style="overflow:auto;">
        <table>
            <thead><tr><th>Дата</th><th>Кредит</th><th>Банк</th><th>План</th><th>Факт</th><th>Досрочно</th><th>Статус</th><th>Комментарий</th></tr></thead>
            <tbody>
            @forelse ($payments as $payment)
                <tr
                    data-payment-date="{{ optional($payment->payment_date)->format('Y-m-d') }}"
                    data-loan="{{ $payment->loan?->title ?: ($payment->loan?->loan_type ?? '—') }}"
                    data-bank="{{ $payment->loan?->bank_name ?? '—' }}"
                    data-planned="{{ (float) $payment->planned_amount }}"
                    data-actual="{{ (float) ($payment->actual_amount ?? 0) }}"
                    data-extra="{{ (float) ($payment->extra_payment ?? 0) }}"
                    data-status="{{ $payment->status }}"
                    data-note="{{ $payment->note }}"
                >
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

<script>
function exportPaymentsCsv() {
    const rows = Array.from(document.querySelectorAll('tr[data-payment-date]'));
    if (!rows.length) {
        alert('Нет данных для экспорта.');
        return;
    }
    const headers = ['Дата', 'Кредит', 'Банк', 'План', 'Факт', 'Досрочно', 'Статус', 'Комментарий'];
    const csvRows = [headers];
    rows.forEach((row) => {
        csvRows.push([
            row.dataset.paymentDate || '',
            row.dataset.loan || '',
            row.dataset.bank || '',
            row.dataset.planned || '',
            row.dataset.actual || '',
            row.dataset.extra || '',
            row.dataset.status || '',
            row.dataset.note || '',
        ]);
    });
    const quote = (v) => {
        const s = String(v ?? '');
        return /[,"\n]/.test(s) ? `"${s.replace(/"/g, '""')}"` : s;
    };
    const csv = csvRows.map((r) => r.map(quote).join(',')).join('\n');
    const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'payments-export.csv';
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
}
</script>
@endsection

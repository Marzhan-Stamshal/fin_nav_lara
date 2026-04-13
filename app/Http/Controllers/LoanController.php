<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Payment;
use App\Support\LoanCalculator;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function create(): View
    {
        return view('loans.form', [
            'loan' => new Loan([
                'status' => 'активный',
                'loan_type' => 'наличные',
                'interest_rate_annual' => 0,
                'months_paid' => 0,
            ]),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $data['user_id'] = Auth::id();
        $data['months_total'] = $data['months_total'] ?: LoanCalculator::monthSpan($data['start_date'], $data['end_date']);
        $data['status'] = $data['status'] ?: 'активный';

        Loan::query()->create($data);

        return redirect()->route('dashboard')->with('success', 'Кредит добавлен.');
    }

    public function edit(Loan $loan): View
    {
        $this->authorizeLoan($loan);

        return view('loans.form', [
            'loan' => $loan,
            'mode' => 'edit',
        ]);
    }

    public function show(Loan $loan): View
    {
        $this->authorizeLoan($loan);

        $summary = LoanCalculator::summary($loan);
        $payments = Payment::query()
            ->where('loan_id', $loan->id)
            ->latest('payment_date')
            ->limit(50)
            ->get();

        return view('loans.show', compact('loan', 'summary', 'payments'));
    }

    public function update(Request $request, Loan $loan): RedirectResponse
    {
        $this->authorizeLoan($loan);

        $data = $this->validated($request);
        $data['months_total'] = $data['months_total'] ?: LoanCalculator::monthSpan($data['start_date'], $data['end_date']);

        $loan->update($data);

        return redirect()->route('dashboard')->with('success', 'Кредит обновлен.');
    }

    public function destroy(Loan $loan): RedirectResponse
    {
        $this->authorizeLoan($loan);
        $loan->delete();

        return redirect()->route('dashboard')->with('success', 'Кредит удален.');
    }

    public function markPaid(Loan $loan): RedirectResponse
    {
        $this->authorizeLoan($loan);

        if ($loan->status === 'закрыт') {
            return back()->with('success', 'Кредит уже закрыт.');
        }

        $summary = LoanCalculator::summary($loan);
        if ($summary['timeline']['monthsLeft'] <= 0) {
            return back()->with('success', 'Кредит уже завершен.');
        }

        $loan->months_paid = min($summary['timeline']['monthsPaid'] + 1, $summary['timeline']['monthsTotal']);
        $loan->early_payoff_amount = max((float) $loan->early_payoff_amount - (float) $loan->monthly_payment, 0);
        $loan->next_payment_date = $loan->next_payment_date
            ? Carbon::parse($loan->next_payment_date)->addMonth()->toDateString()
            : now()->addMonth()->toDateString();

        if ($loan->months_paid >= $summary['timeline']['monthsTotal'] || $loan->early_payoff_amount <= 0) {
            $loan->status = 'закрыт';
            $loan->early_payoff_amount = 0;
        }

        $loan->save();

        Payment::query()->create([
            'user_id' => $loan->user_id,
            'loan_id' => $loan->id,
            'payment_date' => now()->toDateString(),
            'planned_amount' => $loan->monthly_payment,
            'actual_amount' => $loan->monthly_payment,
            'extra_payment' => 0,
            'status' => 'оплачен',
            'is_mass_action' => false,
            'note' => 'Оплачено за месяц (из карточки кредита)',
        ]);

        return back()->with('success', 'Платеж за месяц отмечен.');
    }

    public function closeEarly(Loan $loan): RedirectResponse
    {
        $this->authorizeLoan($loan);

        if ($loan->status === 'закрыт') {
            return back()->with('success', 'Кредит уже закрыт.');
        }

        $summary = LoanCalculator::summary($loan);
        $extra = (float) $loan->early_payoff_amount;

        $loan->update([
            'status' => 'закрыт',
            'early_payoff_amount' => 0,
            'months_paid' => $summary['timeline']['monthsTotal'],
        ]);

        Payment::query()->create([
            'user_id' => $loan->user_id,
            'loan_id' => $loan->id,
            'payment_date' => now()->toDateString(),
            'planned_amount' => $loan->monthly_payment,
            'actual_amount' => $loan->monthly_payment,
            'extra_payment' => $extra,
            'status' => 'оплачен',
            'is_mass_action' => false,
            'note' => 'Закрыт досрочно (из карточки кредита)',
        ]);

        return back()->with('success', 'Кредит закрыт досрочно.');
    }

    public function exportCsv(): StreamedResponse
    {
        $loans = Auth::user()->loans()->orderBy('created_at')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="loans-export.csv"',
        ];

        return response()->stream(function () use ($loans) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'title',
                'bank_name',
                'loan_type',
                'status',
                'principal_initial',
                'early_payoff_amount',
                'monthly_payment',
                'interest_rate_annual',
                'start_date',
                'end_date',
                'next_payment_date',
                'months_total',
                'months_paid',
                'group_name',
                'notes',
            ]);

            foreach ($loans as $loan) {
                fputcsv($out, [
                    $loan->title,
                    $loan->bank_name,
                    $loan->loan_type,
                    $loan->status,
                    $loan->principal_initial,
                    $loan->early_payoff_amount,
                    $loan->monthly_payment,
                    $loan->interest_rate_annual,
                    optional($loan->start_date)->toDateString(),
                    optional($loan->end_date)->toDateString(),
                    optional($loan->next_payment_date)->toDateString(),
                    $loan->months_total,
                    $loan->months_paid,
                    $loan->group_name,
                    $loan->notes,
                ]);
            }
            fclose($out);
        }, Response::HTTP_OK, $headers);
    }

    public function exportSampleCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="loans-sample.csv"',
        ];

        return response()->stream(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'title',
                'bank_name',
                'loan_type',
                'status',
                'principal_initial',
                'early_payoff_amount',
                'monthly_payment',
                'interest_rate_annual',
                'start_date',
                'end_date',
                'next_payment_date',
                'months_total',
                'months_paid',
                'group_name',
                'notes',
            ]);
            fputcsv($out, [
                'Телефон',
                'Kaspi',
                'рассрочка',
                'активный',
                '500000',
                '335377',
                '22670',
                '0',
                now()->subMonths(21)->toDateString(),
                now()->addMonths(27)->toDateString(),
                now()->addDays(3)->toDateString(),
                '48',
                '21',
                'Плачу я',
                'Пример строки для импорта',
            ]);
            fclose($out);
        }, Response::HTTP_OK, $headers);
    }

    public function importCsv(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt,text/plain'],
        ]);

        $file = $request->file('csv_file');
        $content = file_get_contents($file->getRealPath());
        if ($content === false) {
            return back()->withErrors(['csv_file' => 'Не удалось прочитать CSV файл.']);
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $lines = preg_split("/\r\n|\n|\r/", trim($content)) ?: [];
        if (count($lines) < 2) {
            return back()->withErrors(['csv_file' => 'CSV пустой или без данных.']);
        }

        $header = str_getcsv(array_shift($lines));
        $required = ['bank_name', 'loan_type', 'status', 'principal_initial', 'early_payoff_amount', 'monthly_payment', 'start_date', 'end_date', 'months_paid'];
        foreach ($required as $column) {
            if (!in_array($column, $header, true)) {
                return back()->withErrors(['csv_file' => "В CSV нет обязательной колонки: {$column}"]);
            }
        }

        $imported = 0;
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $row = str_getcsv($line);
            if (count($row) !== count($header)) {
                continue;
            }
            $data = array_combine($header, $row);
            if (!$data) {
                continue;
            }

            $payload = [
                'user_id' => Auth::id(),
                'title' => $this->nullableString($data['title'] ?? null),
                'bank_name' => $this->nullableString($data['bank_name']) ?? '',
                'loan_type' => $this->nullableString($data['loan_type']) ?? '',
                'status' => $this->nullableString($data['status']) ?? 'активный',
                'principal_initial' => (float) ($data['principal_initial'] ?? 0),
                'early_payoff_amount' => (float) ($data['early_payoff_amount'] ?? 0),
                'monthly_payment' => (float) ($data['monthly_payment'] ?? 0),
                'interest_rate_annual' => isset($data['interest_rate_annual']) && $data['interest_rate_annual'] !== '' ? (float) $data['interest_rate_annual'] : null,
                'start_date' => $data['start_date'] ?? now()->toDateString(),
                'end_date' => $data['end_date'] ?? now()->toDateString(),
                'next_payment_date' => $this->nullableString($data['next_payment_date'] ?? null),
                'months_total' => isset($data['months_total']) && $data['months_total'] !== '' ? (int) $data['months_total'] : null,
                'months_paid' => (int) ($data['months_paid'] ?? 0),
                'group_name' => $this->nullableString($data['group_name'] ?? null),
                'notes' => $this->nullableString($data['notes'] ?? null),
            ];

            if ($payload['months_total'] === null) {
                $payload['months_total'] = LoanCalculator::monthSpan($payload['start_date'], $payload['end_date']);
            }

            if ($payload['bank_name'] === '' || $payload['loan_type'] === '') {
                continue;
            }

            Loan::query()->create($payload);
            $imported++;
        }

        return back()->with('success', "Импорт завершен. Добавлено кредитов: {$imported}");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'bank_name' => ['required', 'string', 'max:120'],
            'loan_type' => ['required', 'string', 'max:120'],
            'status' => ['required', 'string', 'max:20'],
            'principal_initial' => ['required', 'numeric', 'min:0'],
            'early_payoff_amount' => ['required', 'numeric', 'min:0'],
            'monthly_payment' => ['required', 'numeric', 'min:0'],
            'interest_rate_annual' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'next_payment_date' => ['nullable', 'date'],
            'months_total' => ['nullable', 'integer', 'min:1'],
            'months_paid' => ['required', 'integer', 'min:0'],
            'group_name' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function authorizeLoan(Loan $loan): void
    {
        abort_unless($loan->user_id === Auth::id(), 403);
    }

    private function nullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        return $trimmed === '' ? null : $trimmed;
    }
}

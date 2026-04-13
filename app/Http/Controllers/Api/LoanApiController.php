<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Payment;
use App\Support\LoanCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LoanApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $loans = $request->user()->loans()->latest()->get();

        $items = $loans->map(function (Loan $loan) {
            $summary = LoanCalculator::summary($loan);
            return [
                'loan' => $loan,
                'timeline' => $summary['timeline'],
                'earlyPayoffNow' => $summary['earlyPayoffNow'],
                'fullPaymentToEnd' => $summary['fullPaymentToEnd'],
                'savingsIfCloseNow' => $summary['savingsIfCloseNow'],
            ];
        });

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;
        $data['months_total'] = $data['months_total'] ?: LoanCalculator::monthSpan($data['start_date'], $data['end_date']);

        $loan = Loan::query()->create($data);

        return response()->json($loan, 201);
    }

    public function show(Request $request, Loan $loan): JsonResponse
    {
        abort_unless((string) $loan->user_id === (string) $request->user()->id, 403);
        $summary = LoanCalculator::summary($loan);

        return response()->json([
            'loan' => $loan,
            'summary' => $summary,
        ]);
    }

    public function update(Request $request, Loan $loan): JsonResponse
    {
        abort_unless((string) $loan->user_id === (string) $request->user()->id, 403);

        $data = $this->validated($request);
        $data['months_total'] = $data['months_total'] ?: LoanCalculator::monthSpan($data['start_date'], $data['end_date']);

        $loan->update($data);

        return response()->json($loan);
    }

    public function destroy(Request $request, Loan $loan): JsonResponse
    {
        abort_unless((string) $loan->user_id === (string) $request->user()->id, 403);
        $loan->delete();

        return response()->json(['ok' => true]);
    }

    public function payments(Request $request, Loan $loan): JsonResponse
    {
        abort_unless((string) $loan->user_id === (string) $request->user()->id, 403);

        $items = Payment::query()
            ->where('loan_id', $loan->id)
            ->latest('payment_date')
            ->get();

        return response()->json($items);
    }

    public function addPayment(Request $request, Loan $loan): JsonResponse
    {
        abort_unless((string) $loan->user_id === (string) $request->user()->id, 403);

        $data = $request->validate([
            'payment_date' => ['nullable', 'date'],
            'planned_amount' => ['nullable', 'numeric', 'min:0'],
            'actual_amount' => ['nullable', 'numeric', 'min:0'],
            'extra_payment' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:30'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $planned = (float) ($data['planned_amount'] ?? $loan->monthly_payment);
        $actual = (float) ($data['actual_amount'] ?? $planned);
        $extra = (float) ($data['extra_payment'] ?? 0);
        $status = $data['status'] ?? 'оплачен';

        $payment = Payment::query()->create([
            'user_id' => $loan->user_id,
            'loan_id' => $loan->id,
            'payment_date' => $data['payment_date'] ?? now()->toDateString(),
            'planned_amount' => $planned,
            'actual_amount' => $actual,
            'extra_payment' => $extra,
            'status' => $status,
            'is_mass_action' => false,
            'note' => $data['note'] ?? null,
        ]);

        if ($status === 'оплачен' && $loan->status !== 'закрыт') {
            $summary = LoanCalculator::summary($loan);
            $loan->months_paid = min($summary['timeline']['monthsPaid'] + 1, $summary['timeline']['monthsTotal']);
            $loan->early_payoff_amount = max((float) $loan->early_payoff_amount - ($actual + $extra), 0);
            $loan->next_payment_date = $loan->next_payment_date
                ? Carbon::parse($loan->next_payment_date)->addMonth()->toDateString()
                : now()->addMonth()->toDateString();

            if ($loan->months_paid >= $summary['timeline']['monthsTotal'] || $loan->early_payoff_amount <= 0) {
                $loan->status = 'закрыт';
                $loan->early_payoff_amount = 0;
            }

            $loan->save();
        }

        return response()->json($payment, 201);
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
}

<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Payment;
use App\Support\LoanCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $payments = Payment::query()
            ->where('user_id', Auth::id())
            ->with('loan')
            ->latest('payment_date')
            ->paginate(30);

        return view('payments.index', compact('payments'));
    }

    public function schedule(Request $request): View
    {
        $month = $request->string('month')->toString();
        $baseMonth = $month && preg_match('/^\d{4}-\d{2}$/', $month)
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : now()->startOfMonth();

        $loans = Auth::user()
            ->loans()
            ->where('status', '!=', 'закрыт')
            ->whereNotNull('next_payment_date')
            ->get()
            ->filter(fn (Loan $loan) => Carbon::parse($loan->next_payment_date)->isSameMonth($baseMonth))
            ->sortBy('next_payment_date')
            ->values();

        $groups = $loans
            ->groupBy(fn (Loan $loan) => Carbon::parse($loan->next_payment_date)->format('Y-m-d'))
            ->map(function ($items, $date) {
                $collection = collect($items)->sortBy('bank_name')->values();
                return [
                    'date' => $date,
                    'total' => $collection->sum('monthly_payment'),
                    'items' => $collection,
                ];
            })
            ->sortBy('date')
            ->values();

        $monthTotal = $loans->sum('monthly_payment');

        return view('payments.schedule', compact('baseMonth', 'groups', 'monthTotal'));
    }

    public function scheduleMarkPaid(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'loan_ids' => ['required', 'array'],
            'loan_ids.*' => ['integer'],
        ]);

        $loans = Auth::user()->loans()->whereIn('id', $data['loan_ids'])->get();

        foreach ($loans as $loan) {
            if ($loan->status === 'закрыт') {
                continue;
            }

            $summary = LoanCalculator::summary($loan);
            if ($summary['timeline']['monthsLeft'] <= 0) {
                continue;
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
                'is_mass_action' => true,
                'note' => 'Оплачено со страницы графика',
            ]);
        }

        return back()->with('success', 'Выбранные кредиты отмечены как оплаченные за месяц.');
    }
}

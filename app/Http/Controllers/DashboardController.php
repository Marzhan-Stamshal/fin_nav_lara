<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Payment;
use App\Models\UserSetting;
use App\Support\LoanCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $loans = $user->loans()->orderByDesc('created_at')->get();

        $summaries = $loans->map(fn (Loan $loan) => LoanCalculator::summary($loan));

        $filterBank = $request->string('bank', 'all')->toString();
        $filterGroup = $request->string('group', 'all')->toString();
        $filterStatus = $request->string('status', 'active')->toString();
        $filterTerm = $request->string('term', 'all')->toString();
        $minAmount = $request->input('min_amount');
        $maxAmount = $request->input('max_amount');
        $minAmountValue = is_numeric($minAmount) ? (float) $minAmount : null;
        $maxAmountValue = is_numeric($maxAmount) ? (float) $maxAmount : null;
        $sort = $request->string('sort', 'end')->toString();
        $direction = $request->string('dir', 'asc')->toString() === 'desc' ? 'desc' : 'asc';

        $visible = $summaries->filter(function (array $item) use ($filterBank, $filterGroup, $filterStatus, $filterTerm, $minAmountValue, $maxAmountValue) {
            $loan = $item['loan'];
            if ($filterBank !== 'all' && $loan->bank_name !== $filterBank) {
                return false;
            }

            if ($filterGroup !== 'all') {
                $group = $loan->group_name ?: 'Без группы';
                if ($group !== $filterGroup) {
                    return false;
                }
            }

            if ($filterStatus === 'active' && $loan->status === 'закрыт') {
                return false;
            }

            if ($filterStatus === 'closed' && $loan->status !== 'закрыт') {
                return false;
            }

            $monthsLeft = (int) $item['timeline']['monthsLeft'];
            if ($filterTerm === 'overdue' && $monthsLeft !== 0) return false;
            if ($filterTerm === 'upTo12' && ($monthsLeft < 1 || $monthsLeft > 12)) return false;
            if ($filterTerm === 'from12To24' && ($monthsLeft < 12 || $monthsLeft > 24)) return false;
            if ($filterTerm === 'over24' && $monthsLeft <= 24) return false;

            if (!is_null($minAmountValue) && $item['earlyPayoffNow'] < $minAmountValue) return false;
            if (!is_null($maxAmountValue) && $item['earlyPayoffNow'] > $maxAmountValue) return false;

            return true;
        });

        $visible = $this->sortSummaries($visible, $sort, $direction)->values();

        $totalEarly = $visible->sum('earlyPayoffNow');
        $totalFull = $visible->sum('fullPaymentToEnd');
        $totalSavings = $visible->sum('savingsIfCloseNow');
        $totalMonthly = $visible->sum(fn (array $item) => (float) $item['loan']->monthly_payment);
        $allActiveMonthly = $summaries
            ->filter(fn (array $item) => $item['loan']->status !== 'закрыт')
            ->sum(fn (array $item) => (float) $item['loan']->monthly_payment);

        $forecastByBank = $summaries
            ->filter(fn (array $item) => $item['loan']->status !== 'закрыт')
            ->groupBy(fn (array $item) => $item['loan']->bank_name)
            ->map(function (Collection $items, string $bankName) {
                $maxMonths = max($items->max(fn (array $it) => $it['timeline']['monthsLeft']), 1);
                return [
                    'bank' => $bankName,
                    'items' => $items->sortBy(fn (array $it) => $it['timeline']['monthsLeft'])->values(),
                    'totalMonthly' => $items->sum(fn (array $it) => (float) $it['loan']->monthly_payment),
                    'maxMonths' => $maxMonths,
                ];
            })
            ->sortBy('bank')
            ->values();

        $totalsByGroup = $summaries
            ->groupBy(fn (array $item) => $item['loan']->group_name ?: 'Без группы')
            ->map(function (Collection $items, string $group) {
                return [
                    'group' => $group,
                    'count' => $items->count(),
                    'early' => $items->sum('earlyPayoffNow'),
                    'monthly' => $items->sum(fn (array $it) => (float) $it['loan']->monthly_payment),
                ];
            })
            ->sortBy('group')
            ->values();

        $recommendedLoan = $summaries
            ->filter(fn (array $item) => $item['loan']->status !== 'закрыт' && $item['earlyPayoffNow'] > 0)
            ->sort(function (array $a, array $b) {
                if ($b['savingsIfCloseNow'] !== $a['savingsIfCloseNow']) {
                    return $b['savingsIfCloseNow'] <=> $a['savingsIfCloseNow'];
                }
                if (($b['loan']->interest_rate_annual ?? 0) !== ($a['loan']->interest_rate_annual ?? 0)) {
                    return ($b['loan']->interest_rate_annual ?? 0) <=> ($a['loan']->interest_rate_annual ?? 0);
                }
                return $b['loan']->monthly_payment <=> $a['loan']->monthly_payment;
            })
            ->first();

        $whatIfLoanId = (int) $request->input('what_if_loan_id', 0);
        $whatIfExtra = (float) $request->input('what_if_extra', 0);
        $selectedWhatIf = $summaries->first(fn (array $item) => $item['loan']->id === $whatIfLoanId) ?? $recommendedLoan;
        $whatIfResult = null;
        if ($selectedWhatIf && $whatIfExtra > 0) {
            $updatedEarly = max($selectedWhatIf['earlyPayoffNow'] - $whatIfExtra, 0);
            $updatedSavings = max($selectedWhatIf['fullPaymentToEnd'] - $updatedEarly, 0);
            $estimatedMonthsSaved = min(
                $selectedWhatIf['timeline']['monthsLeft'],
                (int) floor($whatIfExtra / max((float) $selectedWhatIf['loan']->monthly_payment, 1))
            );
            $whatIfResult = [
                'originalEarly' => $selectedWhatIf['earlyPayoffNow'],
                'updatedEarly' => $updatedEarly,
                'extraSavings' => max($updatedSavings - $selectedWhatIf['savingsIfCloseNow'], 0),
                'estimatedMonthsSaved' => $estimatedMonthsSaved,
            ];
        }

        $analyticsByBank = $summaries
            ->groupBy(fn (array $item) => $item['loan']->bank_name)
            ->map(fn (Collection $items, string $bank) => [
                'label' => $bank,
                'early' => $items->sum('earlyPayoffNow'),
                'monthly' => $items->sum(fn (array $it) => (float) $it['loan']->monthly_payment),
                'count' => $items->count(),
            ])
            ->sortByDesc('early')
            ->values();

        $analyticsByType = $summaries
            ->groupBy(fn (array $item) => $item['loan']->loan_type)
            ->map(fn (Collection $items, string $type) => [
                'label' => $type,
                'early' => $items->sum('earlyPayoffNow'),
                'monthly' => $items->sum(fn (array $it) => (float) $it['loan']->monthly_payment),
                'count' => $items->count(),
            ])
            ->sortByDesc('early')
            ->values();

        $banks = $loans->pluck('bank_name')->unique()->sort()->values();
        $groups = $loans->pluck('group_name')->filter()->unique()->sort()->values();

        $recentPayments = Payment::query()
            ->where('user_id', $user->id)
            ->latest('payment_date')
            ->with('loan')
            ->limit(20)
            ->get();

        $paymentReminders = $loans
            ->filter(fn (Loan $loan) => $loan->status !== 'закрыт' && $loan->next_payment_date)
            ->map(function (Loan $loan) {
                $days = now()->startOfDay()->diffInDays(Carbon::parse($loan->next_payment_date)->startOfDay(), false);
                return ['loan' => $loan, 'days' => $days];
            })
            ->filter(fn (array $item) => in_array($item['days'], [1, 3], true))
            ->sortBy('days')
            ->values();

        $upcomingByBankDate = $loans
            ->filter(function (Loan $loan) {
                if ($loan->status === 'закрыт' || !$loan->next_payment_date) {
                    return false;
                }
                $days = now()->startOfDay()->diffInDays(Carbon::parse($loan->next_payment_date)->startOfDay(), false);
                return $days >= 0 && $days <= 30;
            })
            ->groupBy(function (Loan $loan) {
                return $loan->bank_name.'|'.Carbon::parse($loan->next_payment_date)->format('d.m.Y');
            })
            ->map(function (Collection $items, string $key) {
                [$bankName, $dateLabel] = explode('|', $key, 2);
                return [
                    'bankName' => $bankName,
                    'dateLabel' => $dateLabel,
                    'total' => $items->sum('monthly_payment'),
                    'count' => $items->count(),
                ];
            })
            ->sortBy(function (array $item) {
                return Carbon::createFromFormat('d.m.Y', $item['dateLabel'])->timestamp;
            })
            ->values();

        $calendarMonth = $request->string('month')->toString();
        $calendarBase = $calendarMonth && preg_match('/^\d{4}-\d{2}$/', $calendarMonth)
            ? Carbon::createFromFormat('Y-m', $calendarMonth)->startOfMonth()
            : now()->startOfMonth();
        $selectedCalendarDate = $request->string('calendar_date')->toString();

        $calendarEntries = $loans
            ->filter(fn (Loan $loan) => $loan->status !== 'закрыт' && $loan->next_payment_date)
            ->filter(fn (Loan $loan) => Carbon::parse($loan->next_payment_date)->isSameMonth($calendarBase))
            ->groupBy(fn (Loan $loan) => Carbon::parse($loan->next_payment_date)->toDateString())
            ->map(function (Collection $items) {
                return [
                    'total' => $items->sum('monthly_payment'),
                    'loans' => $items->values(),
                ];
            });

        $selectedCalendarLoans = $selectedCalendarDate && $calendarEntries->has($selectedCalendarDate)
            ? $calendarEntries->get($selectedCalendarDate)['loans']
            : collect();

        $setting = UserSetting::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['monthly_income' => 0, 'currency' => 'KZT', 'locale' => 'ru-RU']
        );
        if ($request->has('monthly_income')) {
            $setting->monthly_income = (float) $request->input('monthly_income', 0);
            $setting->save();
        }
        $monthlyIncome = (float) $setting->monthly_income;
        $burdenPercent = $monthlyIncome > 0 ? ($allActiveMonthly / $monthlyIncome) * 100 : null;
        $riskLabel = $burdenPercent === null
            ? 'Нет данных'
            : ($burdenPercent <= 30 ? 'Низкий риск' : ($burdenPercent <= 50 ? 'Средний риск' : 'Высокий риск'));
        $riskColor = $burdenPercent === null
            ? '#374151'
            : ($burdenPercent <= 30 ? '#047857' : ($burdenPercent <= 50 ? '#b45309' : '#b91c1c'));

        return view('dashboard', compact(
            'user',
            'visible',
            'banks',
            'groups',
            'filterBank',
            'filterGroup',
            'filterStatus',
            'filterTerm',
            'minAmount',
            'maxAmount',
            'sort',
            'direction',
            'totalEarly',
            'totalFull',
            'totalSavings',
            'totalMonthly',
            'allActiveMonthly',
            'forecastByBank',
            'totalsByGroup',
            'recommendedLoan',
            'selectedWhatIf',
            'whatIfLoanId',
            'whatIfExtra',
            'whatIfResult',
            'analyticsByBank',
            'analyticsByType',
            'recentPayments',
            'paymentReminders',
            'upcomingByBankDate',
            'calendarBase',
            'selectedCalendarDate',
            'calendarEntries',
            'selectedCalendarLoans',
            'monthlyIncome',
            'burdenPercent',
            'riskLabel',
            'riskColor'
        ));
    }

    public function markPaid(Request $request): RedirectResponse
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
            if ($summary['timeline']['monthsLeft'] === 0) {
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
                'note' => 'Оплачено за месяц',
            ]);
        }

        return back()->with('success', 'Отмечено как оплачено за месяц.');
    }

    public function closeEarly(Request $request): RedirectResponse
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
                'is_mass_action' => true,
                'note' => 'Закрыт досрочно',
            ]);
        }

        return back()->with('success', 'Выбранные кредиты закрыты досрочно.');
    }

    public function assignGroup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'loan_ids' => ['required', 'array'],
            'loan_ids.*' => ['integer'],
            'group_name' => ['required', 'string', 'max:120'],
        ]);

        Auth::user()
            ->loans()
            ->whereIn('id', $data['loan_ids'])
            ->update(['group_name' => Str::of($data['group_name'])->trim()->toString()]);

        return back()->with('success', 'Группа назначена выбранным кредитам.');
    }

    public function clearGroup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'loan_ids' => ['required', 'array'],
            'loan_ids.*' => ['integer'],
        ]);

        Auth::user()
            ->loans()
            ->whereIn('id', $data['loan_ids'])
            ->update(['group_name' => null]);

        return back()->with('success', 'Группа очищена.');
    }

    private function sortSummaries(Collection $items, string $sort, string $direction): Collection
    {
        $sorted = match ($sort) {
            'bank' => $items->sortBy(fn (array $item) => $item['loan']->bank_name),
            'group' => $items->sortBy(fn (array $item) => $item['loan']->group_name ?: 'Без группы'),
            'term' => $items->sortBy(fn (array $item) => $item['timeline']['monthsLeft']),
            'early' => $items->sortBy('earlyPayoffNow'),
            'full' => $items->sortBy('fullPaymentToEnd'),
            'savings' => $items->sortBy('savingsIfCloseNow'),
            'monthly' => $items->sortBy(fn (array $item) => $item['loan']->monthly_payment),
            'rate' => $items->sortBy(fn (array $item) => $item['loan']->interest_rate_annual ?? 0),
            default => $items->sortBy(fn (array $item) => optional($item['loan']->end_date)->toDateString() ?? ''),
        };

        return $direction === 'desc' ? $sorted->reverse() : $sorted;
    }
}

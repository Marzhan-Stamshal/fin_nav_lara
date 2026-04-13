<?php

namespace App\Support;

use App\Models\Loan;
use Carbon\Carbon;

class LoanCalculator
{
    public static function summary(Loan $loan): array
    {
        $totalMonths = $loan->months_total ?: self::monthSpan($loan->start_date?->toDateString(), $loan->end_date?->toDateString());
        $totalMonths = max($totalMonths, 1);

        $monthsPaid = (int) ($loan->months_paid ?? 0);
        $monthsPaid = max(0, min($monthsPaid, $totalMonths));

        if ($loan->status === 'закрыт') {
            $monthsPaid = $totalMonths;
        }

        $monthsLeft = $loan->status === 'закрыт' ? 0 : max($totalMonths - $monthsPaid, 0);
        $earlyPayoffNow = (float) $loan->early_payoff_amount;
        $fullPaymentToEnd = $monthsLeft * (float) $loan->monthly_payment;
        $savingsIfCloseNow = max($fullPaymentToEnd - $earlyPayoffNow, 0);
        $progressPercent = round(($monthsPaid / $totalMonths) * 100, 1);

        return [
            'loan' => $loan,
            'timeline' => [
                'monthsTotal' => $totalMonths,
                'monthsPaid' => $monthsPaid,
                'monthsLeft' => $monthsLeft,
                'progressPercent' => $progressPercent,
            ],
            'earlyPayoffNow' => $earlyPayoffNow,
            'fullPaymentToEnd' => $fullPaymentToEnd,
            'savingsIfCloseNow' => $savingsIfCloseNow,
        ];
    }

    public static function monthSpan(?string $startDate, ?string $endDate): int
    {
        if (!$startDate || !$endDate) {
            return 1;
        }

        $start = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate)->startOfMonth();

        return max($start->diffInMonths($end) + 1, 1);
    }
}

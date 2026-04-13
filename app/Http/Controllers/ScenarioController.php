<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Scenario;
use App\Models\ScenarioLoan;
use App\Support\LoanCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ScenarioController extends Controller
{
    public function index(Request $request): View
    {
        $loans = Auth::user()->loans()->where('status', '!=', 'закрыт')->orderBy('bank_name')->get();
        $summaries = $loans->map(fn (Loan $loan) => LoanCalculator::summary($loan));

        $selectedIds = collect($request->input('loan_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();
        if ($selectedIds->isEmpty()) {
            $selectedIds = $summaries->map(fn (array $item) => (int) $item['loan']->id)->values();
        }
        $selected = $summaries->filter(fn (array $item) => $selectedIds->contains($item['loan']->id))->values();

        $selectedEarly = $selected->sum('earlyPayoffNow');
        $selectedFull = $selected->sum('fullPaymentToEnd');
        $selectedMonthly = $selected->sum(fn (array $item) => (float) $item['loan']->monthly_payment);
        $selectedSavings = max($selectedFull - $selectedEarly, 0);

        $extraMonthly = (float) $request->input('extra_monthly', $request->input('extra_budget', 0));
        $extraOneTime = (float) $request->input('extra_one_time', 0);

        $strategyResults = [];
        if ($selected->count() > 0) {
            $maxMonthsOriginal = (int) $selected->max(fn (array $item) => $item['timeline']['monthsLeft']);
            $priorityByRate = $selected->sortByDesc(fn (array $item) => (float) ($item['loan']->interest_rate_annual ?? 0))->values();
            $priorityBySmallBalance = $selected->sortBy(fn (array $item) => (float) $item['earlyPayoffNow'])->values();
            $priorityByMonthly = $selected->sortByDesc(fn (array $item) => (float) $item['loan']->monthly_payment)->values();
            $priorityCurrent = $selected->sortBy(fn (array $item) => (float) $item['timeline']['monthsLeft'])->values();

            $simRate = $this->simulateStrategy($selected, $priorityByRate, $selectedMonthly, $extraMonthly, $extraOneTime, $maxMonthsOriginal);
            $simSnow = $this->simulateStrategy($selected, $priorityBySmallBalance, $selectedMonthly, $extraMonthly, $extraOneTime, $maxMonthsOriginal);
            $simMonthly = $this->simulateStrategy($selected, $priorityByMonthly, $selectedMonthly, $extraMonthly, $extraOneTime, $maxMonthsOriginal);
            $simCurrent = $this->simulateStrategy($selected, $priorityCurrent, $selectedMonthly, 0, 0, $maxMonthsOriginal);

            $strategyResults = [
                [
                    'name' => 'Avalanche (по % ставке)',
                    'description' => 'Сначала закрывается кредит с наибольшей ставкой.',
                    ...$simRate,
                ],
                [
                    'name' => 'Snowball (по сумме)',
                    'description' => 'Сначала закрывается самый маленький долг.',
                    ...$simSnow,
                ],
                [
                    'name' => 'Avalanche+ (по платежу)',
                    'description' => 'Сначала закрывается кредит с максимальным платежом/мес.',
                    ...$simMonthly,
                ],
                [
                    'name' => 'Текущий темп',
                    'description' => 'Без доп. платежей и изменений.',
                    ...$simCurrent,
                ],
            ];
        }

        $extraImpact = null;
        if ($extraMonthly > 0 && $selected->count() > 0) {
            $target = $selected->sortByDesc('savingsIfCloseNow')->first();
            $newEarly = max($target['earlyPayoffNow'] - $extraMonthly, 0);
            $newSavings = max($target['fullPaymentToEnd'] - $newEarly, 0);
            $extraImpact = [
                'loan' => $target['loan'],
                'beforeEarly' => $target['earlyPayoffNow'],
                'afterEarly' => $newEarly,
                'extraSavings' => max($newSavings - $target['savingsIfCloseNow'], 0),
            ];
        }

        $refiLoanIds = collect($request->input('refi_loan_ids', []))->map(fn ($id) => (int) $id)->filter()->values();
        if ($refiLoanIds->isEmpty()) {
            $refiLoanIds = $selected
                ->sortByDesc(fn (array $item) => (float) ($item['loan']->interest_rate_annual ?? 0))
                ->take(2)
                ->map(fn (array $item) => (int) $item['loan']->id)
                ->values();
        }
        $refiAnnualRate = (float) $request->input('refinance_rate', 18);
        $refiTermMonths = (int) $request->input('refinance_term_months', 24);
        $selectedForRefi = $selected->filter(fn (array $item) => $refiLoanIds->contains((int) $item['loan']->id))->values();

        $refinanceResult = null;
        if ($selectedForRefi->count() > 0 && $refiAnnualRate > 0 && $refiTermMonths > 0) {
            $refiPrincipal = (float) $selectedForRefi->sum('earlyPayoffNow');
            $currentTotalIfPayAsIs = (float) $selectedForRefi->sum('fullPaymentToEnd');
            $monthlyRate = ($refiAnnualRate / 100) / 12;
            $newMonthly = ($refiPrincipal * $monthlyRate) / (1 - pow(1 + $monthlyRate, -$refiTermMonths));
            $refinanceTotalPayment = $newMonthly * $refiTermMonths;
            $refinanceResult = [
                'currentTotalIfPayAsIs' => $currentTotalIfPayAsIs,
                'refinancePrincipal' => $refiPrincipal,
                'refinanceMonthlyPayment' => $newMonthly,
                'refinanceTotalPayment' => $refinanceTotalPayment,
                'refinanceSavings' => $currentTotalIfPayAsIs - $refinanceTotalPayment,
            ];
        }

        $refinance = null;
        if ($request->boolean('refinance_enabled') && $selected->count() > 0) {
            $amount = (float) $request->input('refinance_amount', $selectedEarly);
            $rate = (float) $request->input('refinance_rate', 0);
            $term = (int) $request->input('refinance_term_months', 0);

            if ($amount > 0 && $rate > 0 && $term > 0) {
                $monthlyRate = ($rate / 100) / 12;
                $newMonthly = ($amount * $monthlyRate) / (1 - pow(1 + $monthlyRate, -$term));
                $refinance = [
                    'amount' => $amount,
                    'rate' => $rate,
                    'term' => $term,
                    'newMonthly' => $newMonthly,
                    'currentMonthly' => $selectedMonthly,
                    'monthlyBenefit' => $selectedMonthly - $newMonthly,
                ];
            }
        }

        $savedScenarios = Auth::user()
            ->scenarios()
            ->with('scenarioLoans.loan')
            ->latest()
            ->limit(20)
            ->get();

        return view('scenarios.index', compact(
            'summaries',
            'selectedIds',
            'selected',
            'selectedEarly',
            'selectedFull',
            'selectedMonthly',
            'selectedSavings',
            'extraMonthly',
            'extraOneTime',
            'strategyResults',
            'extraImpact',
            'refiLoanIds',
            'refiAnnualRate',
            'refiTermMonths',
            'refinanceResult',
            'refinance',
            'savedScenarios'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'loan_ids' => ['required', 'array'],
            'loan_ids.*' => ['integer'],
            'extra_budget' => ['nullable', 'numeric', 'min:0'],
            'extra_monthly' => ['nullable', 'numeric', 'min:0'],
            'extra_one_time' => ['nullable', 'numeric', 'min:0'],
            'selected_savings' => ['nullable', 'numeric', 'min:0'],
            'monthly_benefit' => ['nullable', 'numeric'],
            'strategy_type' => ['nullable', 'string', 'max:80'],
            'payload' => ['nullable', 'array'],
        ]);

        $scenario = Scenario::query()->create([
            'user_id' => Auth::id(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'extra_budget_monthly' => $data['extra_monthly'] ?? ($data['extra_budget'] ?? 0),
            'extra_budget_one_time' => $data['extra_one_time'] ?? 0,
            'strategy_type' => $data['strategy_type'] ?? 'custom',
            'result_total_savings' => $data['selected_savings'] ?? 0,
            'result_monthly_benefit' => $data['monthly_benefit'] ?? 0,
            'result_payload' => $data['payload'] ?? null,
        ]);

        $loanIds = collect($data['loan_ids'])->map(fn ($id) => (int) $id)->filter()->values();
        foreach ($loanIds as $idx => $loanId) {
            ScenarioLoan::query()->create([
                'scenario_id' => $scenario->id,
                'loan_id' => $loanId,
                'priority' => $idx + 1,
            ]);
        }

        return back()->with('success', 'Сценарий сохранен.');
    }

    public function destroy(Scenario $scenario): RedirectResponse
    {
        abort_unless($scenario->user_id === Auth::id(), 403);
        $scenario->delete();

        return back()->with('success', 'Сценарий удален.');
    }

    private function simulateStrategy($selected, $priorityOrder, float $baseMonthly, float $extraMonthly, float $extraOneTime, int $maxMonthsOriginal): array
    {
        $balances = $selected
            ->mapWithKeys(fn (array $item) => [(int) $item['loan']->id => (float) $item['earlyPayoffNow']])
            ->all();
        $priorityIds = $priorityOrder->map(fn (array $item) => (int) $item['loan']->id)->values()->all();

        $month = 0;
        $bonus = $extraOneTime;
        $maxLoop = 480;
        while ($month < $maxLoop) {
            $openIds = array_keys(array_filter($balances, fn ($v) => $v > 0));
            if (count($openIds) === 0) {
                break;
            }

            $month++;
            $available = $baseMonthly + $extraMonthly + $bonus;
            $bonus = 0;

            foreach ($priorityIds as $loanId) {
                if ($available <= 0) {
                    break;
                }
                $balance = $balances[$loanId] ?? 0;
                if ($balance <= 0) {
                    continue;
                }
                $pay = min($balance, $available);
                $balances[$loanId] = $balance - $pay;
                $available -= $pay;
            }
        }

        $closeDate = now()->addMonths($month);
        $monthsSaved = max($maxMonthsOriginal - $month, 0);
        $estimatedBenefit = max(($extraMonthly * $monthsSaved) + $extraOneTime, 0);

        return [
            'closeDate' => $closeDate,
            'monthsSaved' => $monthsSaved,
            'estimatedBenefit' => $estimatedBenefit,
            'monthlyPayment' => $baseMonthly + $extraMonthly,
        ];
    }
}

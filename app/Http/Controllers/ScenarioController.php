<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Scenario;
use App\Models\ScenarioLoan;
use App\Support\LoanCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ScenarioController extends Controller
{
    public function index(Request $request): View
    {
        $loans = Auth::user()->loans()->where('status', '!=', 'закрыт')->orderBy('bank_name')->get();
        $summaries = $loans->map(fn (Loan $loan) => LoanCalculator::summary($loan));

        $selectedIds = collect($request->input('loan_ids', []))->map(fn ($id) => (int) $id)->filter()->values();
        $selected = $summaries->filter(fn (array $item) => $selectedIds->contains($item['loan']->id))->values();

        $selectedEarly = $selected->sum('earlyPayoffNow');
        $selectedFull = $selected->sum('fullPaymentToEnd');
        $selectedMonthly = $selected->sum(fn (array $item) => (float) $item['loan']->monthly_payment);
        $selectedSavings = max($selectedFull - $selectedEarly, 0);

        $extraBudget = (float) $request->input('extra_budget', 0);
        $extraImpact = null;
        if ($extraBudget > 0 && $selected->count() > 0) {
            $target = $selected->sortByDesc('savingsIfCloseNow')->first();
            $newEarly = max($target['earlyPayoffNow'] - $extraBudget, 0);
            $newSavings = max($target['fullPaymentToEnd'] - $newEarly, 0);
            $extraImpact = [
                'loan' => $target['loan'],
                'beforeEarly' => $target['earlyPayoffNow'],
                'afterEarly' => $newEarly,
                'extraSavings' => max($newSavings - $target['savingsIfCloseNow'], 0),
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
            'extraImpact',
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
            'selected_savings' => ['nullable', 'numeric', 'min:0'],
            'monthly_benefit' => ['nullable', 'numeric'],
            'payload' => ['nullable', 'array'],
        ]);

        $scenario = Scenario::query()->create([
            'user_id' => Auth::id(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'extra_budget_monthly' => $data['extra_budget'] ?? 0,
            'strategy_type' => 'custom',
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
}

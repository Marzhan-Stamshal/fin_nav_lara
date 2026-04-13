<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Payment;
use App\Models\Scenario;
use App\Models\ScenarioLoan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataPortController extends Controller
{
    public function exportJson(): StreamedResponse
    {
        $user = Auth::user();

        $payload = [
            'version' => 1,
            'exported_at' => now()->toIso8601String(),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'loans' => $user->loans()->orderBy('id')->get()->map->toArray()->values(),
            'payments' => Payment::query()->where('user_id', $user->id)->orderBy('id')->get()->map->toArray()->values(),
            'scenarios' => Scenario::query()->where('user_id', $user->id)->orderBy('id')->get()->map->toArray()->values(),
            'scenario_loans' => ScenarioLoan::query()
                ->whereIn('scenario_id', Scenario::query()->where('user_id', $user->id)->pluck('id'))
                ->orderBy('id')
                ->get()
                ->map->toArray()
                ->values(),
        ];

        $headers = [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="finnav-backup.json"',
        ];

        return response()->stream(function () use ($payload) {
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }, 200, $headers);
    }

    public function importJson(Request $request): RedirectResponse
    {
        $request->validate([
            'json_file' => ['required', 'file', 'mimes:json,txt,text/plain'],
            'replace_existing' => ['nullable', 'in:0,1'],
        ]);

        $raw = file_get_contents($request->file('json_file')->getRealPath());
        if ($raw === false) {
            return back()->withErrors(['json_file' => 'Не удалось прочитать JSON файл.']);
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return back()->withErrors(['json_file' => 'Некорректный JSON файл.']);
        }

        $loans = collect($data['loans'] ?? []);
        $payments = collect($data['payments'] ?? []);
        $scenarios = collect($data['scenarios'] ?? []);
        $scenarioLoans = collect($data['scenario_loans'] ?? []);

        $userId = Auth::id();
        $replace = $request->boolean('replace_existing');

        DB::transaction(function () use ($replace, $userId, $loans, $payments, $scenarios, $scenarioLoans) {
            if ($replace) {
                Payment::query()->where('user_id', $userId)->delete();
                Scenario::query()->where('user_id', $userId)->delete();
                Loan::query()->where('user_id', $userId)->delete();
            }

            $loanIdMap = [];
            foreach ($loans as $row) {
                $oldId = (int) ($row['id'] ?? 0);
                unset($row['id'], $row['user_id'], $row['created_at'], $row['updated_at']);
                $row['user_id'] = $userId;
                $loan = Loan::query()->create($row);
                if ($oldId > 0) {
                    $loanIdMap[$oldId] = $loan->id;
                }
            }

            $scenarioIdMap = [];
            foreach ($scenarios as $row) {
                $oldId = (int) ($row['id'] ?? 0);
                unset($row['id'], $row['user_id'], $row['created_at'], $row['updated_at']);
                $row['user_id'] = $userId;
                $scenario = Scenario::query()->create($row);
                if ($oldId > 0) {
                    $scenarioIdMap[$oldId] = $scenario->id;
                }
            }

            foreach ($payments as $row) {
                $oldLoanId = (int) ($row['loan_id'] ?? 0);
                if (!isset($loanIdMap[$oldLoanId])) {
                    continue;
                }

                unset($row['id'], $row['user_id'], $row['created_at'], $row['updated_at']);
                $row['user_id'] = $userId;
                $row['loan_id'] = $loanIdMap[$oldLoanId];
                Payment::query()->create($row);
            }

            foreach ($scenarioLoans as $row) {
                $oldScenarioId = (int) ($row['scenario_id'] ?? 0);
                $oldLoanId = (int) ($row['loan_id'] ?? 0);
                if (!isset($scenarioIdMap[$oldScenarioId]) || !isset($loanIdMap[$oldLoanId])) {
                    continue;
                }

                unset($row['id'], $row['created_at'], $row['updated_at']);
                $row['scenario_id'] = $scenarioIdMap[$oldScenarioId];
                $row['loan_id'] = $loanIdMap[$oldLoanId];
                ScenarioLoan::query()->create($row);
            }
        });

        return back()->with('success', 'JSON импорт завершен успешно.');
    }
}

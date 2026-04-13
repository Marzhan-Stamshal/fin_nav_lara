<?php

namespace Database\Seeders;

use App\Models\Loan;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::query()->updateOrCreate([
            'email' => 'demo@finnav.local',
        ], [
            'name' => 'Demo User',
            'password' => Hash::make('demo12345'),
        ]);

        $today = Carbon::today();

        $loanA = Loan::query()->updateOrCreate(
            ['user_id' => $user->id, 'bank_name' => 'Kaspi', 'title' => 'Телефон'],
            [
                'loan_type' => 'рассрочка',
                'status' => 'активный',
                'principal_initial' => 500000,
                'early_payoff_amount' => 335377,
                'monthly_payment' => 22670,
                'interest_rate_annual' => 26,
                'start_date' => $today->copy()->subMonths(21)->toDateString(),
                'end_date' => $today->copy()->addMonths(27)->toDateString(),
                'next_payment_date' => $today->copy()->addDays(3)->toDateString(),
                'months_total' => 48,
                'months_paid' => 21,
                'group_name' => 'Плачу я',
                'notes' => 'Демо-кредит для проверки дашборда',
            ]
        );

        $loanB = Loan::query()->updateOrCreate(
            ['user_id' => $user->id, 'bank_name' => 'Jusan', 'title' => 'Наличные'],
            [
                'loan_type' => 'наличные',
                'status' => 'активный',
                'principal_initial' => 1200000,
                'early_payoff_amount' => 920000,
                'monthly_payment' => 58200,
                'interest_rate_annual' => 31.5,
                'start_date' => $today->copy()->subMonths(8)->toDateString(),
                'end_date' => $today->copy()->addMonths(28)->toDateString(),
                'next_payment_date' => $today->copy()->addDays(1)->toDateString(),
                'months_total' => 36,
                'months_paid' => 8,
                'group_name' => 'Плачу не я',
            ]
        );

        Payment::query()->updateOrCreate(
            ['user_id' => $user->id, 'loan_id' => $loanA->id, 'payment_date' => $today->copy()->subMonth()->toDateString()],
            [
                'planned_amount' => $loanA->monthly_payment,
                'actual_amount' => $loanA->monthly_payment,
                'extra_payment' => 0,
                'status' => 'оплачен',
                'is_mass_action' => true,
                'note' => 'Демо платеж',
            ]
        );

        Payment::query()->updateOrCreate(
            ['user_id' => $user->id, 'loan_id' => $loanB->id, 'payment_date' => $today->copy()->subMonth()->toDateString()],
            [
                'planned_amount' => $loanB->monthly_payment,
                'actual_amount' => $loanB->monthly_payment,
                'extra_payment' => 0,
                'status' => 'оплачен',
                'is_mass_action' => true,
                'note' => 'Демо платеж',
            ]
        );
    }
}

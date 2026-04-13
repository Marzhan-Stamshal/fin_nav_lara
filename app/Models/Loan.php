<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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
    ];

    protected function casts(): array
    {
        return [
            'principal_initial' => 'float',
            'early_payoff_amount' => 'float',
            'monthly_payment' => 'float',
            'interest_rate_annual' => 'float',
            'start_date' => 'date',
            'end_date' => 'date',
            'next_payment_date' => 'date',
            'months_total' => 'integer',
            'months_paid' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}

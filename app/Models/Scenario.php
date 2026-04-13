<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scenario extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'extra_budget_monthly',
        'extra_budget_one_time',
        'strategy_type',
        'result_total_savings',
        'result_monthly_benefit',
        'result_payload',
    ];

    protected function casts(): array
    {
        return [
            'extra_budget_monthly' => 'float',
            'extra_budget_one_time' => 'float',
            'result_total_savings' => 'float',
            'result_monthly_benefit' => 'float',
            'result_payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scenarioLoans(): HasMany
    {
        return $this->hasMany(ScenarioLoan::class);
    }
}

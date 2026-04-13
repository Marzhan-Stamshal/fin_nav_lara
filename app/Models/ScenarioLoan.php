<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScenarioLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'scenario_id',
        'loan_id',
        'priority',
        'projected_close_months',
    ];

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(Scenario::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}

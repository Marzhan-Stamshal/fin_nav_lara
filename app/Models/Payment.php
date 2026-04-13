<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'loan_id',
        'payment_date',
        'planned_amount',
        'actual_amount',
        'extra_payment',
        'status',
        'is_mass_action',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'planned_amount' => 'float',
            'actual_amount' => 'float',
            'extra_payment' => 'float',
            'is_mass_action' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}

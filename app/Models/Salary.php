<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_number',
        'user_id',
        'period_month',
        'basic_salary',
        'allowance',
        'deductions',
        'net_salary',
        'payment_date',
        'payment_status',
        'payment_method',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'allowance' => 'decimal:2',
            'deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}

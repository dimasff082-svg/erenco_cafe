<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_number',
        'expense_type',
        'title',
        'category',
        'amount',
        'expense_date',
        'payment_method',
        'receipt_proof',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRawMaterial(): bool
    {
        return $this->expense_type === 'raw_material';
    }

    public function isOperational(): bool
    {
        return $this->expense_type === 'operational';
    }

    public function typeLabel(): string
    {
        return match ($this->expense_type) {
            'raw_material' => 'Pembelian Bahan Baku',
            'operational' => 'Pengeluaran Lainnya (Operasional)',
            default => 'Lainnya',
        };
    }
}

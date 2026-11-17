<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RawMaterialMutation extends Model
{
    use HasFactory;

    protected $fillable = [
        'raw_material_id',
        'user_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'stock_before' => 'decimal:2',
            'stock_after' => 'decimal:2',
        ];
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'in' => 'Masuk / Restock',
            'out' => 'Keluar / Pemakaian',
            'adjustment' => 'Penyesuaian / Opname',
            default => strtoupper((string) $this->type),
        };
    }

    public function typeBadge(): array
    {
        return match ($this->type) {
            'in' => [
                'label' => 'Masuk / Restock',
                'class' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                'sign' => '+',
            ],
            'out' => [
                'label' => 'Keluar / Pemakaian',
                'class' => 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
                'sign' => '-',
            ],
            'adjustment' => [
                'label' => 'Penyesuaian / Opname',
                'class' => 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
                'sign' => ((float) $this->quantity >= 0 ? '+' : ''),
            ],
            default => [
                'label' => (string) $this->type,
                'class' => 'bg-slate-500/10 text-slate-400 border border-slate-500/20',
                'sign' => '',
            ],
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'unit',
        'current_stock',
        'min_stock',
        'unit_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:2',
            'min_stock' => 'decimal:2',
            'unit_price' => 'decimal:2',
        ];
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(RawMaterialMutation::class)->latest('id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Catat mutasi stok dan perbarui current_stock secara otomatis.
     */
    public function recordMutation(
        string $type,
        float $quantity,
        ?string $referenceNumber = null,
        ?string $notes = null,
        ?int $userId = null
    ): RawMaterialMutation {
        $stockBefore = (float) $this->current_stock;

        if ($type === 'in') {
            $qty = abs($quantity);
            $stockAfter = $stockBefore + $qty;
        } elseif ($type === 'out') {
            $qty = abs($quantity);
            $stockAfter = max(0, $stockBefore - $qty);
        } elseif ($type === 'adjustment') {
            $qty = $quantity;
            $stockAfter = max(0, $stockBefore + $quantity);
        } else {
            $qty = $quantity;
            $stockAfter = $stockBefore;
        }

        $this->update(['current_stock' => $stockAfter]);

        return $this->mutations()->create([
            'user_id' => $userId ?? auth()->id(),
            'type' => $type,
            'quantity' => $qty,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_number' => $referenceNumber,
            'notes' => $notes,
        ]);
    }

    public function isLowStock(): bool
    {
        return (float) $this->current_stock <= (float) $this->min_stock && (float) $this->current_stock > 0;
    }

    public function isOutOfStock(): bool
    {
        return (float) $this->current_stock <= 0;
    }

    public function stockStatus(): string
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        }

        if ($this->isLowStock()) {
            return 'low_stock';
        }

        return 'safe';
    }

    public function statusBadge(): array
    {
        if ($this->isOutOfStock()) {
            return [
                'label' => 'Habis',
                'class' => 'bg-red-500/10 text-red-400 border border-red-500/20',
            ];
        }

        if ($this->isLowStock()) {
            return [
                'label' => 'Menipis',
                'class' => 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
            ];
        }

        return [
            'label' => 'Aman',
            'class' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
        ];
    }

    public function totalAssetValue(): float
    {
        return (float) $this->current_stock * (float) $this->unit_price;
    }
}

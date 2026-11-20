<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'description',
        'image',
        'unit',
        'selling_price',
        'is_active',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_available' => 'boolean',
        ];
    }

    public function isAvailable(): bool
    {
        return (bool) $this->is_active && (bool) $this->is_available;
    }

    public function isOutOfStock(): bool
    {
        return ! (bool) $this->is_available;
    }

    public function getPriceAttribute(): float
    {
        return (float) $this->selling_price;
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return asset('storage/'.$this->image);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('is_available', true);
    }
}

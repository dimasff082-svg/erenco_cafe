<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'table_number',
        'customer_name',
        'customer_phone',
        'user_id',
        'transaction_date',
        'subtotal',
        'discount_amount',
        'total_amount',
        'status',
        'order_status',
        'payment_status',
        'snap_token',
        'open_bill_id',
        'confirmed_by',
        'confirmed_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function openBill(): BelongsTo
    {
        return $this->belongsTo(OpenBill::class);
    }

    public function restaurantTable(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_number', 'table_number');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isOnline(): bool
    {
        return $this->table_number === 'ONLINE';
    }

    public function isTakeaway(): bool
    {
        return $this->table_number === 'TAKEAWAY';
    }

    public function isKiosk(): bool
    {
        return $this->table_number === 'KIOSK';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isUnpaid(): bool
    {
        return $this->payment_status === 'unpaid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled' || $this->order_status === 'cancelled';
    }

    public function orderStatusLabel(): string
    {
        return match ($this->order_status) {
            'processing' => 'Diproses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => 'Menunggu',
        };
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'paid' => 'Lunas',
            'cancelled' => 'Dibatalkan',
            default => 'Belum Dibayar',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'type',
        'time',
        'latitude',
        'longitude',
        'distance_meters',
        'photo_path',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'distance_meters' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'check_in' => 'Absen Pagi',
            'break_start' => 'Absen Mulai Istirahat',
            'break_end' => 'Absen Selesai Istirahat',
            'check_out' => 'Absen Pulang',
            default => strtoupper((string) $this->type),
        };
    }

    public function isWithinRadius(): bool
    {
        return $this->distance_meters !== null && (float) $this->distance_meters <= 50.0;
    }
}

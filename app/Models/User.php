<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi transaksi penjualan yang dilayani oleh user / kasir.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Dapatkan kasir yang sedang aktif login (berdasarkan sesi aktif di database),
     * atau fallback ke kasir aktif pertama di sistem jika belum ada sesi.
     */
    public static function getActiveCashier(): ?self
    {
        // 1. Jika dalam konteks request yang sedang dijalankan oleh kasir
        if (auth()->check() && auth()->user() && auth()->user()->isCashier()) {
            return auth()->user();
        }

        // 2. Cek sesi kasir yang paling baru aktif di tabel database sessions
        try {
            $cashierId = DB::table('sessions')
                ->join('users', 'sessions.user_id', '=', 'users.id')
                ->where('users.role', 'cashier')
                ->where('users.is_active', true)
                ->whereNotNull('sessions.user_id')
                ->orderByDesc('sessions.last_activity')
                ->value('users.id');

            if ($cashierId) {
                $cashier = self::find($cashierId);
                if ($cashier) {
                    return $cashier;
                }
            }
        } catch (\Throwable $e) {
            // Abaikan jika tabel sessions tidak ada / koneksi db testing berbeda
        }

        // 3. Fallback: kasir aktif pertama di sistem (atau admin aktif jika belum ada kasir)
        return self::where('role', 'cashier')->where('is_active', true)->first()
            ?? self::where('role', 'admin')->where('is_active', true)->first();
    }

    /**
     * Dapatkan ID kasir yang sedang aktif login.
     */
    public static function getActiveCashierId(): ?int
    {
        return static::getActiveCashier()?->id;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isKitchen(): bool
    {
        return $this->role === 'kitchen';
    }

    public function isWarehouse(): bool
    {
        return $this->role === 'warehouse';
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class);
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'admin' => 'ADMINISTRATOR',
            'owner' => 'PEMILIK TOKO',
            'cashier' => 'KASIR',
            'kitchen' => 'DAPUR',
            'warehouse' => 'STAF WAREHOUSE',
            default => strtoupper((string) $this->role),
        };
    }
}

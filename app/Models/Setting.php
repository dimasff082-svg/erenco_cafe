<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    /**
     * Nilai standar bawaan jika setting belum tersimpan di database.
     *
     * @var array<string, mixed>
     */
    public const DEFAULTS = [
        'store_name' => 'Fira Cake Bakery & Pastry',
        'store_phone' => '082131117996',
        'store_email' => 'info@firacake.com',
        'store_address' => 'BISOLPIN, Ruko Ruby, Jl. Boulevard Rubi II, Masale, Panakkukang, Makassar',
        'store_hours' => '08:00 - 21:00 WITA',
        'online_welcome_title' => 'Selamat Datang di Fira Cake!',
        'online_welcome_description' => 'Nikmati aneka pilihan roti, bolu fresh, kue tart, dan pastry premium kualitas terbaik kami langsung dibuat segar setiap hari.',
        'attendance_max_distance' => '15',
        'app_logo' => null,
    ];

    /**
     * Mengambil batas maksimal radius absensi karyawan dalam satuan meter.
     */
    public static function getAttendanceMaxDistance(): float
    {
        $val = static::get('attendance_max_distance', 15.0);

        return is_numeric($val) && (float) $val > 0 ? (float) $val : 15.0;
    }

    /**
     * Mengambil nilai pengaturan berdasarkan key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if ($setting !== null && $setting->value !== null && $setting->value !== '') {
            return $setting->value;
        }

        if ($default !== null) {
            return $default;
        }

        return self::DEFAULTS[$key] ?? null;
    }

    /**
     * Menyimpan atau memperbarui pengaturan.
     */
    public static function set(string $key, mixed $value, string $type = 'text', string $group = 'general'): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
            ]
        );
    }

    /**
     * Mengambil seluruh pengaturan sebagai associative array dengan default fallback.
     *
     * @return array<string, mixed>
     */
    public static function allMapped(): array
    {
        $dbSettings = static::pluck('value', 'key')->toArray();

        return array_merge(self::DEFAULTS, array_filter($dbSettings, fn ($v) => $v !== null && $v !== ''));
    }
}

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
     * Cache memori lokal per request lifecycle.
     *
     * @var array<string, mixed>|null
     */
    protected static ?array $cachedMapped = null;

    protected static function booted(): void
    {
        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }

    /**
     * Menghapus cache pengaturan in-memory.
     */
    public static function clearCache(): void
    {
        static::$cachedMapped = null;
    }

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
        'order_notification_whatsapp' => '',
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
     * Mengambil URL logo toko resmi (atau default logo jika belum diunggah).
     */
    public static function getLogoUrl(): string
    {
        $logo = static::get('app_logo');
        if (! empty($logo)) {
            return asset('storage/'.$logo);
        }

        return asset('images/logo.png');
    }

    /**
     * Mengambil nama toko/usaha saat ini.
     * Secara bawaan diformat menjadi 1 baris untuk judul halaman, pesan WhatsApp, atau atribut HTML.
     */
    public static function getStoreName(bool $singleLine = true): string
    {
        $name = (string) static::get('store_name', self::DEFAULTS['store_name']);
        if ($singleLine) {
            return trim(preg_replace('/\s+/', ' ', $name));
        }

        return $name;
    }

    /**
     * Mengambil nama toko yang dipecah menjadi 2 baris (array [baris1, baris2]).
     * Jika admin menulis dengan baris baru (\n), langsung gunakan baris tersebut.
     * Jika tidak, pecah secara otomatis agar tampil rapi dalam 2 baris.
     *
     * @return array{0: string, 1: string}
     */
    public static function getStoreNameLines(): array
    {
        $raw = trim((string) static::get('store_name', self::DEFAULTS['store_name']));

        if (str_contains($raw, "\n")) {
            $parts = array_map('trim', explode("\n", $raw, 2));

            return [$parts[0], $parts[1] ?? ''];
        }

        // Cek pemisah umum jika ditulis dalam satu baris
        foreach ([' • ', ' | ', ' - '] as $sep) {
            if (str_contains($raw, $sep)) {
                $parts = explode($sep, $raw, 2);

                return [trim($parts[0]), trim($parts[1])];
            }
        }

        if (str_contains($raw, ' & ')) {
            $parts = explode(' & ', $raw, 2);

            return [trim($parts[0]), '& '.trim($parts[1])];
        }

        $words = preg_split('/\s+/', $raw);
        if ($words !== false && count($words) >= 4) {
            $mid = (int) ceil(count($words) / 2);

            return [
                implode(' ', array_slice($words, 0, $mid)),
                implode(' ', array_slice($words, $mid)),
            ];
        } elseif ($words !== false && count($words) === 3) {
            return [
                $words[0].' '.$words[1],
                $words[2],
            ];
        } elseif ($words !== false && count($words) === 2) {
            return [
                $words[0],
                $words[1],
            ];
        }

        return [$raw, ''];
    }

    /**
     * Mengambil nomor kontak telepon / WhatsApp resmi toko.
     */
    public static function getStorePhone(): string
    {
        return (string) static::get('store_phone', self::DEFAULTS['store_phone']);
    }

    /**
     * Mengambil alamat email resmi toko.
     */
    public static function getStoreEmail(): string
    {
        return (string) static::get('store_email', self::DEFAULTS['store_email']);
    }

    /**
     * Mengambil alamat lengkap gerai / toko.
     */
    public static function getStoreAddress(): string
    {
        return (string) static::get('store_address', self::DEFAULTS['store_address']);
    }

    /**
     * Mengambil jam operasional toko.
     */
    public static function getStoreHours(): string
    {
        return (string) static::get('store_hours', self::DEFAULTS['store_hours']);
    }

    /**
     * Mengambil nilai pengaturan berdasarkan key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::allMapped();

        if (array_key_exists($key, $settings) && $settings[$key] !== null && $settings[$key] !== '') {
            return $settings[$key];
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
        static::clearCache();

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
        if (static::$cachedMapped !== null) {
            return static::$cachedMapped;
        }

        try {
            $dbSettings = static::pluck('value', 'key')->toArray();
            static::$cachedMapped = array_merge(self::DEFAULTS, array_filter($dbSettings, fn ($v) => $v !== null && $v !== ''));
        } catch (\Throwable) {
            return self::DEFAULTS;
        }

        return static::$cachedMapped;
    }
}

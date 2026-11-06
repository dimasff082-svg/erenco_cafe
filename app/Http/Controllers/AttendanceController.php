<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Koordinat kantor / toko Fira Cake (BISOLPIN, Ruko Ruby, Jl. Boulevard Rubi II, Masale, Makassar)
     * Sumber Google Maps: https://maps.app.goo.gl/8UpiFYzVZgmKkhmi6
     */
    public const OFFICE_LATITUDE = -5.155325;

    public const OFFICE_LONGITUDE = 119.441929;

    public const OFFICE_NAME = 'Kantor Fira Cake (Ruko Ruby Masale)';

    public const MAX_ALLOWED_DISTANCE_METERS = 15.0;

    /**
     * Tampilan form & dashboard absensi mandiri untuk Kasir dan Staf Warehouse.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $today = today()->toDateString();

        // 4 slot shift absensi hari ini
        $todayAttendances = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->get()
            ->keyBy('type');

        // Riwayat absensi user
        $history = Attendance::where('user_id', $user->id)
            ->latest('date')
            ->latest('time')
            ->paginate(15);

        $shiftSlots = [
            'check_in' => [
                'name' => 'Absen Pagi (Masuk)',
                'description' => 'Presensi awal jam kerja masuk toko/kantor',
                'icon_type' => 'sun',
                'color' => 'amber',
            ],
            'break_start' => [
                'name' => 'Absen Mulai Istirahat',
                'description' => 'Presensi saat memulai jam istirahat siang',
                'icon_type' => 'coffee',
                'color' => 'blue',
            ],
            'break_end' => [
                'name' => 'Absen Selesai Istirahat',
                'description' => 'Presensi saat selesai istirahat dan kembali bekerja',
                'icon_type' => 'bolt',
                'color' => 'indigo',
            ],
            'check_out' => [
                'name' => 'Absen Pulang',
                'description' => 'Presensi akhir jam kerja saat pulang',
                'icon_type' => 'house',
                'color' => 'emerald',
            ],
        ];

        $maxDistance = Setting::getAttendanceMaxDistance();

        $office = [
            'latitude' => self::OFFICE_LATITUDE,
            'longitude' => self::OFFICE_LONGITUDE,
            'max_distance' => $maxDistance,
        ];

        return view('attendance.index', compact('todayAttendances', 'history', 'shiftSlots', 'office', 'maxDistance'));
    }

    /**
     * Proses penyimpanan data absensi dengan verifikasi foto & radius lokasi (maksimal sesuai pengaturan toko).
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'type' => ['required', 'string', 'in:check_in,break_start,break_end,check_out'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photo_camera' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'type.required' => 'Pilih jenis jadwal absensi yang akan dilakukan.',
            'latitude.required' => 'Lokasi GPS tidak terdeteksi. Harap izinkan akses lokasi (GPS) pada peramban Anda.',
            'longitude.required' => 'Lokasi GPS tidak terdeteksi.',
            'photo_camera.required' => 'Wajib mengambil foto selfie bukti kehadiran langsung dari kamera perangkat (tidak diperkenankan menggunakan file foto).',
        ]);

        $type = $request->input('type');
        $today = today()->toDateString();

        // 1. Cek apakah sudah absen untuk slot tersebut hari ini
        $alreadyAttended = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->where('type', $type)
            ->exists();

        if ($alreadyAttended) {
            return back()->withErrors(['attendance' => 'Anda sudah melakukan '.$this->getTypeName($type).' untuk hari ini.']);
        }

        // 2. Hitung jarak dari kantor dengan rumus Haversine
        $userLat = (float) $request->input('latitude');
        $userLng = (float) $request->input('longitude');
        $distance = $this->calculateHaversineDistance(
            $userLat,
            $userLng,
            self::OFFICE_LATITUDE,
            self::OFFICE_LONGITUDE
        );

        // 3. Validasi radius maksimal dari pengaturan toko
        $maxDistance = Setting::getAttendanceMaxDistance();
        if ($distance > $maxDistance) {
            return back()->withErrors([
                'attendance' => 'Jarak lokasi Anda saat ini adalah '.number_format($distance, 1).' meter dari kantor. Absensi ditolak karena melebihi batas maksimal '.number_format($maxDistance, 0).' meter yang ditentukan toko.',
            ]);
        }

        // 4. Verifikasi & simpan foto kehadiran dari kamera (Base64)
        $photoPath = null;
        $base64Photo = $request->input('photo_camera');
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Photo, $matches)) {
            $imageType = $matches[1];
            $data = substr($base64Photo, strpos($base64Photo, ',') + 1);
            $decoded = base64_decode($data);

            if ($decoded !== false) {
                $filename = 'attendances/'.Str::uuid().'.'.$imageType;
                Storage::disk('public')->put($filename, $decoded);
                $photoPath = $filename;
            }
        }

        if (! $photoPath) {
            return back()->withErrors(['photo_camera' => 'Format foto kamera tidak valid. Silakan ambil foto ulang dari kamera Anda.']);
        }

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'type' => $type,
            'time' => now()->toTimeString(),
            'latitude' => $userLat,
            'longitude' => $userLng,
            'distance_meters' => round($distance, 2),
            'photo_path' => $photoPath,
            'status' => 'present',
            'notes' => $request->input('notes'),
        ]);

        return back()->with('success', 'Berhasil mencatat '.$this->getTypeName($type).' (Jarak: '.number_format($distance, 1).' m).');
    }

    /**
     * Hitung jarak dua titik koordinat dalam satuan meter (Haversine formula).
     */
    public function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }

    protected function getTypeName(string $type): string
    {
        return match ($type) {
            'check_in' => 'Absen Pagi',
            'break_start' => 'Absen Mulai Istirahat',
            'break_end' => 'Absen Selesai Istirahat',
            'check_out' => 'Absen Pulang',
            default => 'Absensi',
        };
    }
}

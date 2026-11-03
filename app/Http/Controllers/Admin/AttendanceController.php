<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Tampilan monitoring dan rekapitulasi data presensi seluruh karyawan untuk Admin & Owner.
     */
    public function index(Request $request): View
    {
        $query = Attendance::with('user')->latest('date')->latest('time');

        // Filter Karyawan
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        // Filter Jenis Shift Absensi
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Filter Tanggal
        if ($period = $request->input('period')) {
            if ($period === 'today') {
                $query->whereDate('date', today());
            } elseif ($period === 'this_week') {
                $query->whereBetween('date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
            } elseif ($period === 'this_month') {
                $query->whereMonth('date', now()->month)->whereYear('date', now()->year);
            }
        } elseif ($dateFrom = $request->input('date_from')) {
            $query->whereDate('date', '>=', $dateFrom);
            if ($dateTo = $request->input('date_to')) {
                $query->whereDate('date', '<=', $dateTo);
            }
        } else {
            // Default: hari ini
            $query->whereDate('date', today());
        }

        $attendances = $query->paginate(20)->withQueryString();

        // Ringkasan statistik hari ini
        $today = today()->toDateString();
        $todayStats = Attendance::whereDate('date', $today)
            ->selectRaw('
                COUNT(*) as total_attendance,
                COUNT(DISTINCT user_id) as total_employees_present,
                SUM(CASE WHEN distance_meters <= 50.0 THEN 1 ELSE 0 END) as valid_radius_count,
                SUM(CASE WHEN distance_meters > 50.0 THEN 1 ELSE 0 END) as out_of_radius_count
            ')
            ->first();

        $summary = [
            'today_total' => (int) ($todayStats->total_attendance ?? 0),
            'today_employees' => (int) ($todayStats->total_employees_present ?? 0),
            'today_valid' => (int) ($todayStats->valid_radius_count ?? 0),
            'today_invalid' => (int) ($todayStats->out_of_radius_count ?? 0),
        ];

        // Daftar karyawan untuk filter dropdown
        $employees = User::whereIn('role', ['cashier', 'warehouse', 'kitchen'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.attendances.index', compact('attendances', 'summary', 'employees'));
    }
}

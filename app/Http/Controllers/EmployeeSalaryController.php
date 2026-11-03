<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeSalaryController extends Controller
{
    /**
     * Tampilan daftar slip gaji khusus untuk karyawan yang sedang login (Kasir, Warehouse, dll).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Salary::where('user_id', $user->id);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('payroll_number', 'like', "%{$search}%")
                    ->orWhere('period_month', 'like', "%{$search}%");
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        $salaries = $query->latest('payment_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $allUserSalaries = Salary::where('user_id', $user->id)->get();
        $summary = [
            'total_received' => (float) $allUserSalaries->where('payment_status', 'paid')->sum('net_salary'),
            'total_pending' => (float) $allUserSalaries->where('payment_status', 'pending')->sum('net_salary'),
            'total_slips' => $allUserSalaries->count(),
            'latest_slip' => $allUserSalaries->sortByDesc('payment_date')->first(),
        ];

        return view('employee.salaries.index', compact('salaries', 'summary', 'user'));
    }

    /**
     * Cetak / Unduh slip gaji dalam format resmi A4 & PDF.
     */
    public function print(Request $request, Salary $salary): View
    {
        $user = $request->user();

        // Karyawan hanya berhak mengakses slip gajinya sendiri (kecuali admin/owner)
        if ($salary->user_id !== $user->id && ! $user->isAdmin() && ! $user->isOwner()) {
            abort(403, 'Akses ditolak. Anda hanya dapat melihat dan mengunduh slip gaji milik Anda sendiri.');
        }

        $salary->load(['user', 'creator']);

        return view('admin.salaries.print', compact('salary'));
    }
}


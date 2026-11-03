<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SalaryController extends Controller
{
    /**
     * Tampilan daftar kelola penggajian karyawan untuk Admin dan Pemilik Toko.
     */
    public function index(Request $request): View
    {
        $query = Salary::with(['user', 'creator'])->latest('payment_date');

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($status = $request->input('payment_status')) {
            $query->where('payment_status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('payroll_number', 'like', "%{$search}%")
                    ->orWhere('period_month', 'like', "%{$search}%");
            });
        }

        $salaries = $query->paginate(15)->withQueryString();

        // Ringkasan statistik penggajian
        $stats = Salary::selectRaw("
            COUNT(*) as total_slips,
            COALESCE(SUM(net_salary), 0) as total_payroll,
            COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN net_salary ELSE 0 END), 0) as total_paid,
            COALESCE(SUM(CASE WHEN payment_status = 'pending' THEN net_salary ELSE 0 END), 0) as total_pending
        ")->first();

        $summary = [
            'total_slips' => (int) ($stats->total_slips ?? 0),
            'total_payroll' => (float) ($stats->total_payroll ?? 0),
            'total_paid' => (float) ($stats->total_paid ?? 0),
            'total_pending' => (float) ($stats->total_pending ?? 0),
        ];

        // Daftar seluruh karyawan aktif yang berhak menerima slip gaji (Kasir dan Staf Warehouse)
        $employees = User::whereIn('role', ['cashier', 'warehouse'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.salaries.index', compact('salaries', 'summary', 'employees'));
    }

    /**
     * Menyimpan data slip gaji baru yang diinput oleh Admin atau Pemilik.
     */
    public function store(Request $request): RedirectResponse
    {
        // Bersihkan tanda titik ribuan (misal: 3.500.000 -> 3500000)
        foreach (['basic_salary', 'allowance', 'deductions'] as $field) {
            if ($request->has($field)) {
                $clean = preg_replace('/[^\d]/', '', (string) $request->input($field));
                $request->merge([$field => $clean !== '' ? (float) $clean : 0]);
            }
        }

        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role', ['cashier', 'warehouse'])->where('is_active', true);
                }),
            ],
            'period_month' => ['required', 'string', 'max:50'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'allowance' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'payment_date' => ['required', 'date'],
            'payment_status' => ['required', 'in:paid,pending'],
            'payment_method' => ['required', 'in:transfer,cash'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'user_id.required' => 'Pilih karyawan penerima gaji.',
            'user_id.exists' => 'Karyawan yang dipilih tidak valid atau bukan staf kasir / warehouse aktif.',
            'period_month.required' => 'Periode bulan gaji wajib diisi.',
            'basic_salary.required' => 'Nominal gaji pokok wajib diisi.',
            'payment_date.required' => 'Tanggal pembayaran wajib diisi.',
        ]);

        $basicSalary = (float) $validated['basic_salary'];
        $allowance = (float) ($validated['allowance'] ?? 0);
        $deductions = (float) ($validated['deductions'] ?? 0);
        $netSalary = $basicSalary + $allowance - $deductions;

        // Generate nomor slip gaji unik: PAY-YYYYMM-001
        $count = Salary::count() + 1;
        $prefix = 'PAY-'.date('Ym').'-';
        $payrollNumber = sprintf('%s%03d', $prefix, $count);
        while (Salary::where('payroll_number', $payrollNumber)->exists()) {
            $count++;
            $payrollNumber = sprintf('%s%03d', $prefix, $count);
        }

        Salary::create([
            'payroll_number' => $payrollNumber,
            'user_id' => $validated['user_id'],
            'period_month' => $validated['period_month'],
            'basic_salary' => $basicSalary,
            'allowance' => $allowance,
            'deductions' => $deductions,
            'net_salary' => $netSalary,
            'payment_date' => $validated['payment_date'],
            'payment_status' => $validated['payment_status'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', "Slip gaji {$payrollNumber} berhasil diinput ke sistem.");
    }

    /**
     * Memperbarui data slip gaji.
     */
    public function update(Request $request, Salary $salary): RedirectResponse
    {
        // Bersihkan tanda titik ribuan (misal: 3.500.000 -> 3500000)
        foreach (['basic_salary', 'allowance', 'deductions'] as $field) {
            if ($request->has($field)) {
                $clean = preg_replace('/[^\d]/', '', (string) $request->input($field));
                $request->merge([$field => $clean !== '' ? (float) $clean : 0]);
            }
        }

        $validated = $request->validate([
            'period_month' => ['required', 'string', 'max:50'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'allowance' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'payment_date' => ['required', 'date'],
            'payment_status' => ['required', 'in:paid,pending'],
            'payment_method' => ['required', 'in:transfer,cash'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $basicSalary = (float) $validated['basic_salary'];
        $allowance = (float) ($validated['allowance'] ?? 0);
        $deductions = (float) ($validated['deductions'] ?? 0);
        $netSalary = $basicSalary + $allowance - $deductions;

        $salary->update([
            'period_month' => $validated['period_month'],
            'basic_salary' => $basicSalary,
            'allowance' => $allowance,
            'deductions' => $deductions,
            'net_salary' => $netSalary,
            'payment_date' => $validated['payment_date'],
            'payment_status' => $validated['payment_status'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Data slip gaji {$salary->payroll_number} berhasil diperbarui.");
    }

    /**
     * Menghapus slip gaji.
     */
    public function destroy(Salary $salary): RedirectResponse
    {
        $payrollNumber = $salary->payroll_number;
        $salary->delete();

        return back()->with('success', "Slip gaji {$payrollNumber} berhasil dihapus.");
    }

    /**
     * Tampilan cetak slip gaji resmi format A4.
     */
    public function print(Salary $salary): View
    {
        $salary->load(['user', 'creator']);

        return view('admin.salaries.print', compact('salary'));
    }
}

<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Salary;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfitLossController extends Controller
{
    /**
     * Tampilan Laporan Laba/Rugi komprehensif khusus untuk Pemilik Toko (Owner).
     * Membandingkan Pendapatan Penjualan, Beban Bahan Baku, Beban Operasional, dan Gaji Karyawan.
     */
    public function index(Request $request): View
    {
        $period = $request->input('period', 'this_month');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $startDate = null;
        $endDate = null;
        $periodLabel = 'Bulan Ini';

        if ($dateFrom) {
            $startDate = Carbon::parse($dateFrom)->startOfDay();
            $endDate = $dateTo ? Carbon::parse($dateTo)->endOfDay() : Carbon::parse($dateFrom)->endOfDay();
            $periodLabel = $startDate->format('d/m/Y').' - '.$endDate->format('d/m/Y');
        } else {
            switch ($period) {
                case 'today':
                    $startDate = Carbon::today()->startOfDay();
                    $endDate = Carbon::today()->endOfDay();
                    $periodLabel = 'Hari Ini ('.Carbon::today()->translatedFormat('d M Y').')';
                    break;
                case 'last_month':
                    $startDate = Carbon::now()->subMonth()->startOfMonth();
                    $endDate = Carbon::now()->subMonth()->endOfMonth();
                    $periodLabel = 'Bulan Lalu ('.$startDate->translatedFormat('F Y').')';
                    break;
                case 'this_year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    $periodLabel = 'Tahun Ini ('.Carbon::now()->year.')';
                    break;
                case 'this_month':
                default:
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    $periodLabel = 'Bulan Ini ('.Carbon::now()->translatedFormat('F Y').')';
                    break;
            }
        }

        // 1. PENDAPATAN (Hanya penjualan yang LUNAS dan TIDAK DIBATALKAN)
        $salesQuery = Sale::where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('order_status', '!=', 'cancelled')
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        $salesStats = (clone $salesQuery)->selectRaw('
            COUNT(*) as total_orders,
            COALESCE(SUM(subtotal), 0) as gross_revenue,
            COALESCE(SUM(discount_amount), 0) as total_discounts,
            COALESCE(SUM(total_amount), 0) as net_revenue
        ')->first();

        $grossRevenue = (float) ($salesStats->gross_revenue ?? 0);
        $totalDiscounts = (float) ($salesStats->total_discounts ?? 0);
        $netRevenue = (float) ($salesStats->net_revenue ?? 0);
        $totalOrders = (int) ($salesStats->total_orders ?? 0);

        // 2. BEBAN POKOK PRODUKSI / BAHAN BAKU (Raw Materials)
        $rawMaterialQuery = Expense::where('expense_type', 'raw_material')
            ->whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $rawMaterialExpense = (float) (clone $rawMaterialQuery)->sum('amount');
        $rawMaterialCount = (clone $rawMaterialQuery)->count();
        $rawMaterialItems = (clone $rawMaterialQuery)->latest('expense_date')->limit(10)->get();

        // LABA KOTOR (GROSS PROFIT)
        $grossProfit = $netRevenue - $rawMaterialExpense;

        // 3. BEBAN OPERASIONAL (Listrik, Air, Telepon, Kebersihan, dll)
        $operationalQuery = Expense::where('expense_type', 'operational')
            ->whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $operationalExpense = (float) (clone $operationalQuery)->sum('amount');
        $operationalCount = (clone $operationalQuery)->count();
        $operationalItems = (clone $operationalQuery)->latest('expense_date')->limit(10)->get();

        // 4. BEBAN GAJI KARYAWAN (Payroll)
        $salaryQuery = Salary::where('payment_status', 'paid')
            ->whereBetween('payment_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $salaryExpense = (float) (clone $salaryQuery)->sum('net_salary');
        $salaryCount = (clone $salaryQuery)->count();

        // TOTAL BEBAN OPERASIONAL
        $totalOperatingExpenses = $operationalExpense + $salaryExpense;

        // LABA / (RUGI) BERSIH (NET PROFIT / LOSS)
        $netProfit = $grossProfit - $totalOperatingExpenses;

        $report = [
            'period_label' => $periodLabel,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_orders' => $totalOrders,
            'gross_revenue' => $grossRevenue,
            'total_discounts' => $totalDiscounts,
            'net_revenue' => $netRevenue,
            'raw_material_expense' => $rawMaterialExpense,
            'raw_material_count' => $rawMaterialCount,
            'gross_profit' => $grossProfit,
            'operational_expense' => $operationalExpense,
            'operational_count' => $operationalCount,
            'salary_expense' => $salaryExpense,
            'salary_count' => $salaryCount,
            'total_operating_expenses' => $totalOperatingExpenses,
            'net_profit' => $netProfit,
        ];

        return view('owner.reports.profit_loss', compact('report', 'rawMaterialItems', 'operationalItems'));
    }
}

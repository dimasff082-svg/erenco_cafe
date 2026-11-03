<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    /**
     * Tampilan satu halaman transaksi pengeluaran untuk Staf Warehouse
     * (Menyatukan pembelian bahan baku dan pengeluaran operasional lainnya: listrik, air, telepon, dll).
     */
    public function index(Request $request): View
    {
        $query = Expense::with('user')->latest('expense_date');

        if ($type = $request->input('expense_type')) {
            if ($type !== 'all') {
                $query->where('expense_type', $type);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('expense_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('expense_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('expense_date', '<=', $dateTo);
        }

        $expenses = $query->paginate(15)->withQueryString();

        // Akumulasi statistik pengeluaran
        $baseStatsQuery = Expense::query();
        if ($dateFrom) {
            $baseStatsQuery->whereDate('expense_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $baseStatsQuery->whereDate('expense_date', '<=', $dateTo);
        }

        $stats = (clone $baseStatsQuery)
            ->selectRaw("
                COUNT(*) as total_count,
                COALESCE(SUM(amount), 0) as total_expense,
                COALESCE(SUM(CASE WHEN expense_type = 'raw_material' THEN amount ELSE 0 END), 0) as raw_material_expense,
                COALESCE(SUM(CASE WHEN expense_type = 'operational' THEN amount ELSE 0 END), 0) as operational_expense
            ")
            ->first();

        $summary = [
            'total_count' => (int) ($stats->total_count ?? 0),
            'total_expense' => (float) ($stats->total_expense ?? 0),
            'raw_material_expense' => (float) ($stats->raw_material_expense ?? 0),
            'operational_expense' => (float) ($stats->operational_expense ?? 0),
        ];

        return view('warehouse.expenses.index', compact('expenses', 'summary'));
    }

    /**
     * Menyimpan transaksi pengeluaran baru.
     */
    public function store(Request $request): RedirectResponse
    {
        // Bersihkan tanda titik ribuan (misal: 250.000 -> 250000)
        if ($request->has('amount')) {
            $cleanAmount = preg_replace('/[^\d]/', '', (string) $request->input('amount'));
            $request->merge(['amount' => $cleanAmount !== '' ? (float) $cleanAmount : null]);
        }

        $validated = $request->validate([
            'expense_type' => ['required', 'string', 'in:raw_material,operational'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'in:cash,transfer'],
            'receipt_proof' => ['nullable', 'image', 'max:5120'], // max 5MB
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'expense_type.required' => 'Pilih jenis pengeluaran (Bahan Baku atau Operasional Lainnya).',
            'title.required' => 'Nama pengeluaran / barang wajib diisi.',
            'amount.required' => 'Nominal pengeluaran wajib diisi.',
            'amount.min' => 'Nominal pengeluaran minimal Rp 1.',
            'expense_date.required' => 'Tanggal pengeluaran wajib diisi.',
            'receipt_proof.image' => 'Bukti pengeluaran harus berupa file gambar (JPG, PNG, WebP).',
        ]);

        // Generate nomor transaksi pengeluaran unik: EXP001, EXP002, dst.
        $count = Expense::count() + 1;
        $expenseNumber = sprintf('EXP%03d', $count);
        while (Expense::where('expense_number', $expenseNumber)->exists()) {
            $count++;
            $expenseNumber = sprintf('EXP%03d', $count);
        }

        $proofPath = null;
        if ($request->hasFile('receipt_proof')) {
            $proofPath = $request->file('receipt_proof')->store('expenses', 'public');
        }

        Expense::create([
            'expense_number' => $expenseNumber,
            'expense_type' => $validated['expense_type'],
            'title' => $validated['title'],
            'category' => $validated['category'] ?? ($validated['expense_type'] === 'raw_material' ? 'Bahan Baku' : 'Operasional'),
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'payment_method' => $validated['payment_method'],
            'receipt_proof' => $proofPath,
            'notes' => $validated['notes'] ?? null,
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('warehouse.expenses.index')
            ->with('success', "Transaksi pengeluaran {$expenseNumber} berhasil dicatat.");
    }

    /**
     * Menghapus transaksi pengeluaran jika salah input.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        if ($expense->receipt_proof) {
            Storage::disk('public')->delete($expense->receipt_proof);
        }

        $expense->delete();

        return redirect()->route('warehouse.expenses.index')
            ->with('success', 'Catatan pengeluaran berhasil dihapus.');
    }
}

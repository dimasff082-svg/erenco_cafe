<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\OpenBill;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard Kasir Sederhana sesuai spesifikasi revisi:
     * Tabel: ID Pesanan | Meja | Total | Pembayaran | Aksi
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Pemilik toko diarahkan ke Dashboard Pemilik
        if ($user->isOwner()) {
            return redirect()->route('owner.dashboard');
        }

        $query = Sale::with(['items.product', 'confirmer', 'openBill'])->latest();

        // Pesanan Online wajib lunas terlebih dahulu (via Midtrans) sebelum masuk ke antrean kasir
        $query->where(function ($q) {
            $q->where('table_number', '!=', 'ONLINE')
                ->orWhere('payment_status', 'paid');
        });

        // Filter nomor pesanan / pencarian
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('table_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Filter status pembayaran (Belum Dibayar / Lunas)
        if ($paymentStatus = $request->input('payment_status')) {
            if ($paymentStatus !== 'all') {
                $query->where('payment_status', $paymentStatus);
            }
        }

        // Filter nomor meja
        if ($tableNumber = $request->input('table_number')) {
            if ($tableNumber !== 'all') {
                $query->where('table_number', $tableNumber);
            }
        }

        $sales = $query->paginate(15)->withQueryString();

        // Statistik hari ini untuk terminal kasir (dioptimalkan dalam 1 query gabungan)
        $today = now()->toDateString();
        $stats = Sale::whereDate('transaction_date', $today)
            ->where(function ($q) {
                $q->where('table_number', '!=', 'ONLINE')
                    ->orWhere('payment_status', 'paid');
            })
            ->selectRaw("
                SUM(CASE WHEN payment_status = 'unpaid' THEN 1 ELSE 0 END) as unpaid_count,
                SUM(CASE WHEN payment_status = 'unpaid' THEN total_amount ELSE 0 END) as unpaid_total,
                SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid_total
            ")
            ->first();

        $unpaidCount = (int) ($stats->unpaid_count ?? 0);
        $unpaidTotal = (float) ($stats->unpaid_total ?? 0);
        $paidCount = (int) ($stats->paid_count ?? 0);
        $paidTotal = (float) ($stats->paid_total ?? 0);

        $tables = RestaurantTable::orderBy('table_number')->get();
        $activeOpenBillsCount = OpenBill::where('status', 'open')->count();
        $pendingOpenBillRequests = OpenBill::where('status', 'requested')->latest()->get();

        return view('cashier.dashboard', compact(
            'sales',
            'unpaidCount',
            'unpaidTotal',
            'paidCount',
            'paidTotal',
            'tables',
            'activeOpenBillsCount',
            'pendingOpenBillRequests'
        ));
    }

    /**
     * Kasir mengonfirmasi pembayaran pesanan (dari Belum Dibayar -> Lunas).
     * Mencatat ID Kasir dan Waktu Konfirmasi sesuai aturan backend.
     */
    public function confirmPayment(Request $request, Sale $sale): RedirectResponse
    {
        $user = $request->user();

        // Otorisasi hak akses di backend
        if (! $user->isCashier() && ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki wewenang untuk mengonfirmasi pembayaran.');
        }

        if ($sale->payment_status === 'paid') {
            return back()->with('info', "Pesanan {$sale->transaction_number} sudah berstatus LUNAS sebelumnya.");
        }

        if ($request->has('amount_tendered') && is_string($request->input('amount_tendered'))) {
            $cleaned = preg_replace('/[^\d]/', '', $request->input('amount_tendered'));
            $request->merge(['amount_tendered' => $cleaned !== '' ? (float) $cleaned : null]);
        }

        $validated = $request->validate([
            'payment_method' => ['nullable', 'string', 'in:cash,transfer,qris'],
            'amount_tendered' => ['nullable', 'numeric', 'min:0'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ]);

        $method = $validated['payment_method'] ?? 'cash';
        $amountPaid = (float) $sale->total_amount;
        $amountTendered = isset($validated['amount_tendered']) && $validated['amount_tendered'] > 0
            ? (float) $validated['amount_tendered']
            : $amountPaid;

        if ($method === 'cash' && $amountTendered < $amountPaid) {
            return back()->withErrors([
                'amount_tendered' => 'Uang yang diterima kurang dari total tagihan pesanan (Rp '.number_format($amountPaid, 0, ',', '.').').',
            ]);
        }

        $changeAmount = max(0, $amountTendered - $amountPaid);

        DB::transaction(function () use ($sale, $user, $method, $amountPaid, $amountTendered, $changeAmount, $validated) {
            // 1. Update status pembayaran pada pesanan
            $sale->update([
                'payment_status' => 'paid',
                'status' => 'completed',
                'user_id' => $sale->user_id ?: $user->id,
                'confirmed_by' => $user->id,
                'confirmed_at' => now(),
            ]);

            // 2. Simpan atau perbarui data pembayaran
            Payment::updateOrCreate(
                ['sale_id' => $sale->id],
                [
                    'method' => $method,
                    'amount_paid' => $amountPaid,
                    'amount_tendered' => $amountTendered,
                    'change_amount' => $changeAmount,
                    'status' => 'paid',
                    'reference_number' => $validated['reference_number'] ?? null,
                    'verified_by' => $user->id,
                    'paid_at' => now(),
                ]
            );

            // 3. Jika pesanan terhubung ke Open Bill, periksa apakah semua pesanan di open bill sudah lunas
            if ($sale->open_bill_id) {
                $openBill = OpenBill::find($sale->open_bill_id);
                if ($openBill && $openBill->isOpen()) {
                    $hasUnpaidRemaining = Sale::where('open_bill_id', $openBill->id)
                        ->where('payment_status', 'unpaid')
                        ->exists();

                    if (! $hasUnpaidRemaining) {
                        $openBill->update([
                            'status' => 'paid',
                            'closed_by' => $user->id,
                            'closed_at' => now(),
                        ]);
                    }
                }
            }
        });

        return back()->with('success', "Pembayaran untuk pesanan {$sale->transaction_number} (Meja {$sale->table_number}) berhasil dikonfirmasi LUNAS.");
    }
}

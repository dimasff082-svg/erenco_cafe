<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KitchenController extends Controller
{
    /**
     * Tampilan Kitchen Order Display (KDS) untuk staf dapur.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->input('status', 'active'); // 'active' (menunggu + diproses), 'completed', 'all'

        // Aturan KDS Dapur:
        // 1. Meja dengan Open Bill langsung masuk dapur meskipun belum lunas.
        // 2. Meja tanpa Open Bill WAJIB sudah lunas (paid) baru masuk antrean dapur.
        // 3. Pesanan yang DIBATALKAN (cancelled) tidak boleh masuk ke antrean dapur.
        $kitchenBaseQuery = Sale::whereDate('transaction_date', today())
            ->where('order_status', '!=', 'cancelled')
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->whereNotNull('open_bill_id')
                    ->orWhere('payment_status', 'paid');
            });

        $query = (clone $kitchenBaseQuery)->with(['items.product'])
            ->latest('transaction_date');

        if ($statusFilter === 'active') {
            $query->whereIn('order_status', ['pending', 'processing']);
        } elseif ($statusFilter === 'completed') {
            $query->where('order_status', 'completed');
        } elseif ($statusFilter === 'pending') {
            $query->where('order_status', 'pending');
        } elseif ($statusFilter === 'processing') {
            $query->where('order_status', 'processing');
        }

        $orders = $query->get();

        $statusSummary = (clone $kitchenBaseQuery)
            ->selectRaw("
                SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN order_status = 'processing' THEN 1 ELSE 0 END) as processing_count,
                SUM(CASE WHEN order_status = 'completed' THEN 1 ELSE 0 END) as completed_count
            ")
            ->first();

        $counts = [
            'pending' => (int) ($statusSummary->pending_count ?? 0),
            'processing' => (int) ($statusSummary->processing_count ?? 0),
            'completed' => (int) ($statusSummary->completed_count ?? 0),
        ];

        return view('kitchen.index', compact('orders', 'counts', 'statusFilter'));
    }

    /**
     * Dapur memperbarui status pesanan makanan (Menunggu -> Diproses -> Selesai).
     * PERATURAN: Perubahan status ini TIDAK BOLEH mengubah status pembayaran!
     */
    public function updateStatus(Request $request, Sale $sale): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Verifikasi hak akses: Kitchen, Admin, atau Kasir
        if (! $user->isKitchen() && ! $user->isAdmin() && ! $user->isCashier()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'order_status' => ['required', 'string', 'in:pending,processing,completed'],
        ]);

        // Simpan status pesanan tanpa menyentuh status pembayaran
        $sale->update([
            'order_status' => $validated['order_status'],
        ]);

        $statusLabels = [
            'pending' => 'Menunggu',
            'processing' => 'Sedang Diproses',
            'completed' => 'Selesai Dimasak',
        ];

        $message = "Status pesanan {$sale->transaction_number} (Meja {$sale->table_number}) diperbarui menjadi: ".$statusLabels[$validated['order_status']];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'order_status' => $sale->order_status,
                'order_status_label' => $sale->orderStatusLabel(),
                'payment_status' => $sale->payment_status,
            ]);
        }

        return back()->with('success', $message);
    }
}

<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderCancellationController extends Controller
{
    /**
     * Membatalkan pesanan transaksi penjualan.
     * Hak akses wewenang dimiliki oleh Pemilik Toko (Owner) dan Administrator Toko.
     * Sesuai alur: jika terjadi kesalahan input atau pembatalan, wewenang membatalkan transaksi ada pada pemilik/admin.
     */
    public function cancel(Request $request, Sale $sale): RedirectResponse
    {
        $user = $request->user();

        if (! $user || (! $user->isOwner() && ! $user->isAdmin())) {
            abort(403, 'Hanya Administrator atau Pemilik Toko (Owner) yang memiliki wewenang untuk membatalkan transaksi pesanan.');
        }

        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:3', 'max:500'],
        ], [
            'cancellation_reason.required' => 'Alasan pembatalan pesanan wajib diisi.',
            'cancellation_reason.min' => 'Alasan pembatalan minimal 3 karakter.',
            'cancellation_reason.max' => 'Alasan pembatalan maksimal 500 karakter.',
        ]);

        if ($sale->isCancelled()) {
            return back()->withErrors(['cancellation' => 'Pesanan ini sudah dalam status dibatalkan sebelumnya.']);
        }

        DB::transaction(function () use ($sale, $validated, $user) {
            $sale->update([
                'status' => 'cancelled',
                'order_status' => 'cancelled',
                'payment_status' => 'cancelled',
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $validated['cancellation_reason'],
            ]);
        });

        $roleLabel = $user->isOwner() ? 'Pemilik Toko' : 'Administrator';

        return back()->with('success', "Pesanan #{$sale->transaction_number} berhasil dibatalkan oleh {$roleLabel}.");
    }
}

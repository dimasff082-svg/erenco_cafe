<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TakeawayOrderController extends Controller
{
    /**
     * Tampilan antarmuka kasir & admin untuk melayani transaksi langsung bawa pulang (Takeaway).
     */
    public function index(Request $request): View
    {
        $categories = Category::with(['products' => function ($q) {
            $q->active()->orderBy('name');
        }])->active()->orderBy('name')->get();

        // Riwayat pesanan bawa pulang hari ini untuk referensi cepat kasir
        $todayTakeaways = Sale::with(['items.product', 'payment', 'user', 'confirmer'])
            ->where('table_number', 'TAKEAWAY')
            ->whereDate('transaction_date', today())
            ->latest('transaction_date')
            ->take(8)
            ->get();

        return view('takeaway.index', compact('categories', 'todayTakeaways'));
    }

    /**
     * Memproses transaksi pembelian langsung bawa pulang (Takeaway).
     * Transaksi langsung LUNAS di kasir dan otomatis dikirimkan ke Layar Dapur untuk dibungkus.
     */
    public function checkout(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Otorisasi: Hanya Kasir dan Admin
        if (! $user->isCashier() && ! $user->isAdmin()) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Kasir dan Administrator.');
        }

        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:25'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'string', 'in:cash,transfer,qris'],
            'amount_tendered' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ], [
            'items.required' => 'Keranjang pesanan masih kosong. Pilih menu makanan atau minuman terlebih dahulu.',
            'items.min' => 'Pilih minimal satu menu untuk membuat pesanan bawa pulang.',
            'payment_method.required' => 'Pilih metode pembayaran (Tunai, QRIS, atau Transfer).',
        ]);

        $method = $validated['payment_method'];

        // Jika pembayaran Tunai, nominal uang yang diterima WAJIB diisi
        if ($method === 'cash' && (! isset($validated['amount_tendered']) || (float) $validated['amount_tendered'] <= 0)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Uang tunai yang diterima belum diisi. Masukkan nominal uang yang diterima atau klik Uang Pas.',
                ], 422);
            }

            return back()->withInput()->withErrors(['amount_tendered' => 'Uang tunai yang diterima belum diisi.']);
        }

        try {
            $sale = DB::transaction(function () use ($validated, $user, $method) {
                $itemIds = array_column($validated['items'], 'id');
                $products = Product::whereIn('id', $itemIds)->lockForUpdate()->get()->keyBy('id');

                $subtotal = 0;
                $processedItems = [];

                foreach ($validated['items'] as $item) {
                    $productId = $item['id'];
                    $qty = (int) $item['quantity'];
                    $itemNote = $item['notes'] ?? null;

                    if (! isset($products[$productId])) {
                        throw new Exception("Menu dengan ID {$productId} tidak ditemukan.");
                    }

                    $product = $products[$productId];

                    if (! $product->is_active) {
                        throw new Exception("Menu '{$product->name}' sedang tidak aktif.");
                    }

                    $lineTotal = $qty * (float) $product->selling_price;
                    $subtotal += $lineTotal;

                    $processedItems[] = [
                        'product' => $product,
                        'quantity' => $qty,
                        'unit_price' => (float) $product->selling_price,
                        'line_total' => $lineTotal,
                        'notes' => $itemNote,
                    ];
                }

                // Format nomor transaksi Takeaway yang rapi: TKW001, TKW002, dst.
                $todayCount = Sale::whereDate('transaction_date', today())->count() + 1;
                $orderNumber = sprintf('TKW%03d', $todayCount);
                while (Sale::where('transaction_number', $orderNumber)->exists()) {
                    $todayCount++;
                    $orderNumber = sprintf('TKW%03d', $todayCount);
                }

                $totalAmount = $subtotal;
                if ($method === 'cash') {
                    $tendered = (float) $validated['amount_tendered'];
                    if ($tendered < $totalAmount) {
                        throw new Exception('Uang tunai yang diterima kurang dari total tagihan (Rp '.number_format($totalAmount, 0, ',', '.').').');
                    }
                } else {
                    $tendered = isset($validated['amount_tendered']) && $validated['amount_tendered'] > 0
                        ? (float) $validated['amount_tendered']
                        : $totalAmount;
                }

                $changeAmount = $method === 'cash' ? max(0, $tendered - $totalAmount) : 0;

                // 1. Simpan Transaksi Penjualan
                $sale = Sale::create([
                    'transaction_number' => $orderNumber,
                    'table_number' => 'TAKEAWAY',
                    'customer_name' => (! empty($validated['customer_name'])) ? $validated['customer_name'] : 'Pelanggan Bawa Pulang',
                    'customer_phone' => $validated['customer_phone'] ?? null,
                    'user_id' => $user->id,
                    'transaction_date' => now(),
                    'subtotal' => $subtotal,
                    'discount_amount' => 0,
                    'total_amount' => $totalAmount,
                    'status' => 'completed',
                    'order_status' => 'pending', // Menunggu diproses dapur (bungkus)
                    'payment_status' => 'paid',  // Langsung Lunas
                    'open_bill_id' => null,
                    'confirmed_by' => $user->id,
                    'confirmed_at' => now(),
                    'notes' => (! empty($validated['notes'])) ? '[Bawa Pulang] '.$validated['notes'] : '[Bawa Pulang / Takeaway]',
                ]);

                // 2. Simpan Item Detail
                foreach ($processedItems as $itemData) {
                    $prod = $itemData['product'];
                    $qty = $itemData['quantity'];

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $prod->id,
                        'product_name_snapshot' => $prod->name.($itemData['notes'] ? " ({$itemData['notes']})" : ''),
                        'sku_snapshot' => $prod->sku,
                        'quantity' => $qty,
                        'unit_price' => $itemData['unit_price'],
                        'line_total' => $itemData['line_total'],
                    ]);
                }

                // 3. Simpan Catatan Pembayaran Lunas
                Payment::create([
                    'sale_id' => $sale->id,
                    'method' => $method,
                    'amount_paid' => $totalAmount,
                    'amount_tendered' => $tendered,
                    'change_amount' => $changeAmount,
                    'status' => 'paid',
                    'verified_by' => $user->id,
                    'paid_at' => now(),
                ]);

                return $sale;
            });

            $sale->load(['items.product', 'payment', 'user', 'confirmer']);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Pesanan bawa pulang {$sale->transaction_number} berhasil diproses dan dikirim ke dapur.",
                    'sale' => $sale,
                    'receipt_url' => route('pos.receipt', $sale->id),
                    'change_amount' => $sale->payment?->change_amount ?? 0,
                ]);
            }

            return redirect()->route('pos.receipt', $sale->id)
                ->with('success', "Pesanan bawa pulang {$sale->transaction_number} berhasil dibayar lunas.");
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\MidtransService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KioskOrderController extends Controller
{
    /**
     * Tampilan layar sentuh pemesanan mandiri untuk pelanggan (Self-Ordering Kiosk).
     * Dapat diakses langsung melalui URL tanpa tautan navigasi di bilah sisi staf.
     */
    public function index(): View
    {
        $categories = Category::with(['products' => function ($q) {
            $q->active()->orderBy('name');
        }])->active()->orderBy('name')->get();

        return view('kiosk.index', compact('categories'));
    }

    /**
     * Memproses pesanan mandiri pelanggan dari layar kiosk.
     */
    public function checkout(Request $request, MidtransService $midtransService): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:25'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'string', 'in:cash,gateway,qris,midtrans'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ], [
            'customer_name.required' => 'Mohon isi nama Anda untuk pemanggilan pesanan.',
            'items.required' => 'Keranjang pesanan masih kosong. Pilih menu favorit Anda terlebih dahulu.',
            'items.min' => 'Pilih minimal satu menu untuk membuat pesanan.',
            'payment_method.required' => 'Pilih metode pembayaran (Tunai atau Payment Gateway / QRIS).',
        ]);

        // Tetapkan kasir yang aktif saat ini agar riwayat otomatis tercatat pada kasir tersebut
        $activeCashierId = auth()->check() && (auth()->user()->isCashier() || auth()->user()->isAdmin())
            ? auth()->id()
            : User::getActiveCashierId();

        $method = $validated['payment_method'];

        try {
            $sale = DB::transaction(function () use ($validated, $activeCashierId, $method) {
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
                        throw new Exception("Menu '{$product->name}' sedang tidak tersedia.");
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

                // Format nomor transaksi Kiosk mandiri: KSK001, KSK002, dst.
                $todayCount = Sale::whereDate('transaction_date', today())->count() + 1;
                $orderNumber = sprintf('KSK%03d', $todayCount);
                while (Sale::where('transaction_number', $orderNumber)->exists()) {
                    $todayCount++;
                    $orderNumber = sprintf('KSK%03d', $todayCount);
                }

                $totalAmount = $subtotal;

                $isPaid = ($method !== 'cash');

                // 1. Simpan Transaksi Penjualan Kiosk
                // Sesuai aturan: jika tunai, status belum lunas (unpaid) dan baru muncul di dapur setelah kasir konfirmasi lunas
                $sale = Sale::create([
                    'transaction_number' => $orderNumber,
                    'table_number' => 'KIOSK',
                    'customer_name' => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'] ?? null,
                    'user_id' => $activeCashierId,
                    'transaction_date' => now(),
                    'subtotal' => $subtotal,
                    'discount_amount' => 0,
                    'total_amount' => $totalAmount,
                    'status' => $isPaid ? 'completed' : 'pending',
                    'order_status' => 'pending', // Dikirim ke layar dapur setelah lunas
                    'payment_status' => $isPaid ? 'paid' : 'unpaid',
                    'open_bill_id' => null,
                    'confirmed_by' => $isPaid ? $activeCashierId : null,
                    'confirmed_at' => $isPaid ? now() : null,
                    'notes' => (! empty($validated['notes'])) ? '[Pesan Mandiri] '.$validated['notes'] : '[Pesan Mandiri Kiosk]',
                ]);

                // 2. Simpan Item Detail
                foreach ($processedItems as $itemData) {
                    $prod = $itemData['product'];
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $prod->id,
                        'product_name_snapshot' => $prod->name.($itemData['notes'] ? " ({$itemData['notes']})" : ''),
                        'sku_snapshot' => $prod->sku,
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'line_total' => $itemData['line_total'],
                    ]);
                }

                // 3. Simpan Catatan Pembayaran
                Payment::create([
                    'sale_id' => $sale->id,
                    'method' => ($method === 'cash') ? 'cash' : 'qris',
                    'amount_paid' => $totalAmount,
                    'amount_tendered' => $isPaid ? $totalAmount : 0,
                    'change_amount' => 0,
                    'status' => $isPaid ? 'paid' : 'pending',
                    'verified_by' => $isPaid ? $activeCashierId : null,
                    'paid_at' => $isPaid ? now() : null,
                ]);

                return $sale;
            });

            $sale->load(['items.product', 'payment', 'user', 'confirmer']);

            $message = $sale->isPaid()
                ? "Pesanan Anda ({$sale->transaction_number}) berhasil dibayar lunas dan langsung dikirim ke Dapur!"
                : "Pesanan Anda ({$sale->transaction_number}) berhasil dibuat! Silakan lakukan pembayaran di kasir agar pesanan segera diproses Dapur.";

            return response()->json([
                'success' => true,
                'message' => $message,
                'sale' => $sale,
                'receipt_url' => route('kiosk.receipt', $sale->id),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Struk pemesanan mandiri untuk dicetak oleh pelanggan di kiosk.
     */
    public function receipt(Sale $sale): View
    {
        $sale->load(['items.product', 'payment', 'user', 'confirmer']);

        return view('kiosk.receipt', compact('sale'));
    }
}

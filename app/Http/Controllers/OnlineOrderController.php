<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\User;
use App\Services\MidtransService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OnlineOrderController extends Controller
{
    /**
     * Tampilan katalog pemesanan online untuk pelanggan (Mobile-First / Smartphone Responsive).
     */
    public function index(): View
    {
        $categories = Category::with(['products' => function ($query) {
            $query->where('is_active', true)->orderBy('name');
        }])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $settings = Setting::allMapped();
        $midtransClientKey = config('services.midtrans.client_key');
        $midtransSnapUrl = config('services.midtrans.snap_url');

        return view('online_order.index', compact('categories', 'midtransClientKey', 'midtransSnapUrl', 'settings'));
    }

    /**
     * Memproses checkout pesanan online pelanggan dengan Midtrans Snap Payment Gateway.
     */
    public function checkout(Request $request, MidtransService $midtransService): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'min:9', 'max:25'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['nullable', 'string', 'in:midtrans,qris,gateway'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ], [
            'customer_name.required' => 'Mohon isi nama lengkap Anda.',
            'customer_phone.required' => 'Mohon isi nomor telepon / WhatsApp Anda.',
            'customer_phone.min' => 'Nomor telepon WhatsApp minimal 9 digit.',
            'items.required' => 'Keranjang pesanan masih kosong. Silakan pilih menu terlebih dahulu.',
            'items.min' => 'Pilih minimal satu menu untuk membuat pesanan online.',
        ]);

        // Tetapkan kasir yang aktif agar riwayat tercatat
        $activeCashierId = auth()->check() && (auth()->user()->isCashier() || auth()->user()->isAdmin())
            ? auth()->id()
            : User::getActiveCashierId();

        try {
            $sale = DB::transaction(function () use ($validated, $activeCashierId) {
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

                // Format nomor transaksi Pesanan Online: ONL001, ONL002, dst.
                $todayCount = Sale::whereDate('transaction_date', today())->count() + 1;
                $orderNumber = sprintf('ONL%03d', $todayCount);
                while (Sale::where('transaction_number', $orderNumber)->exists()) {
                    $todayCount++;
                    $orderNumber = sprintf('ONL%03d', $todayCount);
                }

                $totalAmount = $subtotal;

                // Format catatan pesanan terpadu
                $notesParts = ['[Pesanan Online]'];
                if (! empty($validated['address'])) {
                    $notesParts[] = 'Alamat: '.trim($validated['address']);
                }
                if (! empty($validated['notes'])) {
                    $notesParts[] = 'Catatan: '.trim($validated['notes']);
                }
                $fullNotes = implode(' | ', $notesParts);

                // 1. Simpan Transaksi Penjualan Online Awal (Status Belum Bayar sebelum Midtrans Lunas)
                $sale = Sale::create([
                    'transaction_number' => $orderNumber,
                    'table_number' => 'ONLINE',
                    'customer_name' => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'],
                    'user_id' => $activeCashierId,
                    'transaction_date' => now(),
                    'subtotal' => $subtotal,
                    'discount_amount' => 0,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                    'order_status' => 'pending',
                    'payment_status' => 'unpaid', // Wajib lunas via Midtrans sebelum masuk antrean kasir & dapur
                    'open_bill_id' => null,
                    'confirmed_by' => null,
                    'confirmed_at' => null,
                    'notes' => $fullNotes,
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

                // 3. Simpan Catatan Pembayaran Awal (pending)
                Payment::create([
                    'sale_id' => $sale->id,
                    'method' => 'midtrans',
                    'amount_paid' => $totalAmount,
                    'amount_tendered' => $totalAmount,
                    'change_amount' => 0,
                    'status' => 'pending',
                    'reference_number' => 'MIDTRANS-'.$orderNumber,
                    'verified_by' => null,
                    'paid_at' => null,
                ]);

                return $sale;
            });

            // 3. Generate Midtrans Snap Token
            $snapToken = $midtransService->createSnapToken($sale);
            if ($snapToken) {
                $sale->update(['snap_token' => $snapToken]);
            }

            // Opsi bypass otomatis saat pengujian unit / simulasi langsung lunas jika diminta
            if ($request->boolean('auto_paid')) {
                $midtransService->markSaleAsPaid($sale, 'SIMULATED-MIDTRANS-'.strtoupper(Str::random(8)), 'midtrans');
            }

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat. Silakan selesaikan pembayaran via Midtrans.',
                'data' => [
                    'sale_id' => $sale->id,
                    'transaction_number' => $sale->transaction_number,
                    'snap_token' => $snapToken,
                    'total_amount' => (float) $sale->total_amount,
                    'redirect_url' => route('online_order.status', $sale->transaction_number),
                    'success_callback_url' => route('online_order.midtrans_success', $sale->id),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Konfirmasi pembayaran Midtrans berhasil dari frontend Snap callback.
     */
    public function markMidtransSuccess(Request $request, Sale $sale, MidtransService $midtransService): JsonResponse
    {
        if ($sale->table_number !== 'ONLINE') {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak cocok.'], 404);
        }

        $refNumber = $request->input('transaction_id') ?: $request->input('order_id', 'MIDTRANS-'.strtoupper(Str::random(10)));
        $midtransService->markSaleAsPaid($sale, $refNumber, 'midtrans');

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran online Midtrans berhasil dikonfirmasi.',
            'data' => [
                'transaction_number' => $sale->transaction_number,
                'redirect_url' => route('online_order.status', $sale->transaction_number),
            ],
        ]);
    }

    /**
     * Endpoint untuk meminta Snap Token baru jika sebelumnya belum ada.
     */
    public function getSnapToken(Sale $sale, MidtransService $midtransService): JsonResponse
    {
        if ($sale->table_number !== 'ONLINE') {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak cocok.'], 404);
        }

        $token = $midtransService->createSnapToken($sale);

        return response()->json([
            'success' => (bool) $token,
            'snap_token' => $token,
            'total_amount' => (float) $sale->total_amount,
        ]);
    }

    /**
     * Tampilan pelacakan status pesanan online dan struk digital untuk pelanggan.
     */
    public function status(string $transaction_number): View
    {
        $sale = Sale::with(['items.product', 'payment'])
            ->where('transaction_number', $transaction_number)
            ->where('table_number', 'ONLINE')
            ->firstOrFail();

        return view('online_order.status', compact('sale'));
        $midtransClientKey = config('services.midtrans.client_key');
        $midtransSnapUrl = config('services.midtrans.snap_url');

        return view('online_order.status', compact('sale', 'midtransClientKey', 'midtransSnapUrl'));
    }
}

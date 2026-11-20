<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\OpenBill;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\MidtransService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    /**
     * Halaman antarmuka pemesanan mandiri untuk pelanggan melalui QR Code Meja.
     */
    public function index(string $table_number): View
    {
        $tableNumber = strtoupper(trim($table_number));

        // Validasi atau buat meja jika belum terdaftar
        $table = RestaurantTable::firstOrCreate(
            ['table_number' => $tableNumber],
            [
                'name' => "Meja {$tableNumber}",
                'status' => 'available',
            ]
        );

        $categories = Category::with(['products' => function ($q) {
            $q->active()->orderBy('name');
        }])->active()->orderBy('name')->get();

        // Cek apakah meja memiliki Open Bill yang aktif
        $activeOpenBill = OpenBill::with(['sales.items', 'sales.payment'])
            ->where('table_number', $tableNumber)
            ->where('status', 'open')
            ->latest()
            ->first();

        if ($activeOpenBill) {
            $activeOpenBill->recalculateTotal();
        }

        // Cek apakah ada permintaan Open Bill yang sedang menunggu konfirmasi kasir
        $requestedOpenBill = OpenBill::where('table_number', $tableNumber)
            ->where('status', 'requested')
            ->latest()
            ->first();

        // Riwayat pesanan hari ini di meja ini
        $recentOrders = Sale::with(['items.product'])
            ->where('table_number', $tableNumber)
            ->whereDate('transaction_date', today())
            ->latest('transaction_date')
            ->take(5)
            ->get();

        $midtransClientKey = config('services.midtrans.client_key');
        $midtransSnapUrl = config('services.midtrans.snap_url');

        return view('customer.order', compact(
            'table',
            'categories',
            'activeOpenBill',
            'requestedOpenBill',
            'recentOrders',
            'midtransClientKey',
            'midtransSnapUrl'
        ));
    }

    /**
     * Pelanggan mengirimkan permintaan (Request) pembukaan Open Bill ke Kasir.
     */
    public function requestOpenBill(Request $request, string $table_number): RedirectResponse
    {
        $tableNumber = strtoupper(trim($table_number));

        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:25'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Cek apakah meja sudah aktif open bill
        $existingActive = OpenBill::where('table_number', $tableNumber)
            ->where('status', 'open')
            ->first();

        if ($existingActive) {
            return back()->with('info', "Meja {$tableNumber} sudah berstatus Open Bill aktif ({$existingActive->bill_number}).");
        }

        // Cek apakah sudah ada request yang sedang menunggu
        $existingRequest = OpenBill::where('table_number', $tableNumber)
            ->where('status', 'requested')
            ->first();

        if ($existingRequest) {
            return back()->with('info', 'Permintaan Open Bill sebelumnya sedang menunggu konfirmasi kasir.');
        }

        $billNumber = 'BILL-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));

        OpenBill::create([
            'bill_number' => $billNumber,
            'table_number' => $tableNumber,
            'customer_name' => $validated['customer_name'] ?? null,
            'status' => 'requested',
            'opened_at' => now(),
            'total_amount' => 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        RestaurantTable::where('table_number', $tableNumber)->update(['status' => 'occupied']);

        return back()->with('success', 'Permintaan Open Bill berhasil dikirim ke Kasir. Kasir akan segera meninjau dan mengonfirmasi.');
    }

    /**
     * Pelanggan membatalkan permintaan Open Bill yang masih menunggu.
     */
    public function cancelOpenBillRequest(string $table_number): RedirectResponse
    {
        $tableNumber = strtoupper(trim($table_number));

        OpenBill::where('table_number', $tableNumber)
            ->where('status', 'requested')
            ->delete();

        return back()->with('info', 'Permintaan Open Bill telah dibatalkan.');
    }

    /**
     * Pelanggan mengirimkan pesanan dari keranjang belanja QR Code.
     * Untuk meja yang open bill, pesanan langsung masuk ke nota meja tanpa perlu bayar dulu.
     */
    public function checkout(Request $request, string $table_number, MidtransService $midtransService): JsonResponse|RedirectResponse
    {
        $tableNumber = strtoupper(trim($table_number));

        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:25'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_choice' => ['nullable', 'string', 'in:midtrans,cash,open_bill'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ], [
            'items.required' => 'Keranjang pesanan masih kosong. Silakan pilih menu makanan atau minuman.',
            'items.min' => 'Pilih minimal satu menu untuk membuat pesanan.',
        ]);

        try {
            $sale = DB::transaction(function () use ($validated, $tableNumber) {
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
                        throw new Exception("Menu '{$product->name}' sedang tidak tersedia saat ini.");
                    }

                    if (! $product->is_available) {
                        throw new Exception("Menu '{$product->name}' sedang habis dan tidak dapat dipesan.");
                    }

                    $lineTotal = $qty * (float) $product->selling_price;
                    $subtotal += $lineTotal;

                    $processedItems[] = [
                        'product' => $product,
                        'quantity' => $qty,
                        'unit_price' => $product->selling_price,
                        'line_total' => $lineTotal,
                        'notes' => $itemNote,
                    ];
                }

                // Cek apakah meja memiliki Open Bill yang aktif
                $activeOpenBill = OpenBill::where('table_number', $tableNumber)
                    ->where('status', 'open')
                    ->latest()
                    ->first();

                // Untuk meja open bill: otomatis gabung ke open bill tanpa perlu bayar dulu
                $openBillId = null;
                if ($activeOpenBill) {
                    $openBillId = $activeOpenBill->id;
                    $paymentChoice = 'open_bill';
                } else {
                    $paymentChoice = $validated['payment_choice'] ?? 'cash';
                }

                // Format nomor pesanan rapi: ORD001, ORD002, dst
                $todayCount = Sale::whereDate('transaction_date', today())->count() + 1;
                $orderNumber = sprintf('ORD%03d', $todayCount);
                while (Sale::where('transaction_number', $orderNumber)->exists()) {
                    $todayCount++;
                    $orderNumber = sprintf('ORD%03d', $todayCount);
                }

                // 1. Simpan Pesanan Utama (Sale)
                $sale = Sale::create([
                    'transaction_number' => $orderNumber,
                    'table_number' => $tableNumber,
                    'customer_name' => $validated['customer_name'] ?? ($activeOpenBill?->customer_name ?? null),
                    'customer_phone' => $validated['customer_phone'] ?? null,
                    'user_id' => User::getActiveCashierId(), // Dicatat pada kasir yang sedang aktif login
                    'transaction_date' => now(),
                    'subtotal' => $subtotal,
                    'discount_amount' => 0,
                    'total_amount' => $subtotal,
                    'status' => 'pending',
                    'order_status' => 'pending', // Menunggu diproses dapur
                    'payment_status' => 'unpaid', // Belum Dibayar
                    'open_bill_id' => $openBillId,
                    'notes' => $validated['notes'] ?? null,
                ]);

                // 2. Simpan Item Pesanan
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
                        'discount_amount' => 0,
                        'line_total' => $itemData['line_total'],
                    ]);
                }

                // 3. Jika terhubung ke Open Bill, update akumulasi total tagihan meja
                if ($sale->open_bill_id && $activeOpenBill) {
                    $activeOpenBill->recalculateTotal();
                }

                // Tandai meja sebagai occupied
                RestaurantTable::where('table_number', $tableNumber)->update(['status' => 'occupied']);

                return $sale;
            });

            // 4. Jika pesanan terhubung ke Open Bill aktif
            if ($sale->open_bill_id) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Pesanan berhasil dikirim ke dapur dan ditambahkan ke Nota Tagihan Meja!',
                        'order_number' => $sale->transaction_number,
                        'payment_choice' => 'open_bill',
                        'snap_token' => null,
                        'redirect_url' => route('order.index', $tableNumber),
                    ]);
                }

                return redirect()->route('order.index', $tableNumber)
                    ->with('success', "Pesanan Anda ({$sale->transaction_number}) berhasil dikirim ke Dapur dan ditambahkan ke Nota Meja!");
            }

            // 5. Jika non-open-bill dan memilih Midtrans, buat Snap Token
            $snapToken = null;
            $paymentChoice = $validated['payment_choice'] ?? 'cash';
            if ($paymentChoice === 'midtrans') {
                $snapToken = $midtransService->createSnapToken($sale);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan berhasil dikirim ke dapur dan kasir.',
                    'order_number' => $sale->transaction_number,
                    'payment_choice' => $paymentChoice,
                    'snap_token' => $snapToken,
                    'redirect_url' => route('order.status', [$tableNumber, $sale->id]),
                ]);
            }

            return redirect()->route('order.status', [$tableNumber, $sale->id])
                ->with('success', 'Pesanan Anda berhasil dikirim ke Dapur!');
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Endpoint untuk mendapatkan Snap Token pelunasan seluruh tagihan Open Bill.
     */
    public function getOpenBillSnapToken(string $table_number, MidtransService $midtransService): JsonResponse
    {
        $tableNumber = strtoupper(trim($table_number));

        $activeOpenBill = OpenBill::where('table_number', $tableNumber)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (! $activeOpenBill) {
            return response()->json(['success' => false, 'message' => 'Tidak ada tagihan Open Bill aktif untuk meja ini.'], 404);
        }

        $token = $midtransService->createSnapTokenForOpenBill($activeOpenBill);

        return response()->json([
            'success' => (bool) $token,
            'token' => $token,
            'total_amount' => (float) $activeOpenBill->total_amount,
        ]);
    }

    /**
     * Frontend Snap Callback: Pelanggan menyelesaikan pelunasan seluruh Open Bill secara online.
     */
    public function markOpenBillMidtransSuccess(Request $request, string $table_number, MidtransService $midtransService): JsonResponse
    {
        $tableNumber = strtoupper(trim($table_number));

        $activeOpenBill = OpenBill::where('table_number', $tableNumber)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (! $activeOpenBill) {
            return response()->json(['success' => false, 'message' => 'Tidak ada tagihan Open Bill aktif.'], 404);
        }

        $refNumber = $request->input('transaction_id') ?: $request->input('order_id', $activeOpenBill->bill_number);
        $midtransService->markOpenBillAsPaid($activeOpenBill, $refNumber, 'midtrans');

        return response()->json([
            'success' => true,
            'message' => 'Seluruh tagihan meja berhasil dilunasi via Midtrans online.',
            'redirect_url' => route('order.index', $tableNumber),
        ]);
    }

    /**
     * Halaman status pesanan untuk pelanggan setelah mengirim pesanan (khusus non-open bill).
     */
    public function status(string $table_number, Sale $sale, MidtransService $midtransService): View
    {
        $tableNumber = strtoupper(trim($table_number));

        if ($sale->table_number !== $tableNumber) {
            abort(404, 'Pesanan tidak ditemukan di meja ini.');
        }

        $sale->load(['items.product', 'openBill', 'payment']);

        // Jika pesanan belum lunas dan belum memiliki snap token, generate token
        if ($sale->isUnpaid() && empty($sale->snap_token) && ! $sale->open_bill_id) {
            $midtransService->createSnapToken($sale);
            $sale->refresh();
        }

        $midtransClientKey = config('services.midtrans.client_key');
        $midtransSnapUrl = config('services.midtrans.snap_url');

        return view('customer.status', compact('tableNumber', 'sale', 'midtransClientKey', 'midtransSnapUrl'));
    }

    /**
     * Frontend Snap Callback: Pelanggan menyelesaikan pembayaran online via Snap Popup.
     */
    public function markMidtransSuccess(Request $request, string $table_number, Sale $sale, MidtransService $midtransService): JsonResponse
    {
        $tableNumber = strtoupper(trim($table_number));

        if ($sale->table_number !== $tableNumber) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak cocok dengan meja ini.'], 404);
        }

        $refNumber = $request->input('transaction_id') ?: $request->input('order_id', $sale->transaction_number);
        $midtransService->markSaleAsPaid($sale, $refNumber, 'midtrans');

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran online Midtrans berhasil dikonfirmasi.',
            'redirect_url' => route('order.status', [$tableNumber, $sale->id]),
        ]);
    }

    /**
     * Endpoint untuk mendapatkan Snap Token secara dinamis jika belum ada.
     */
    public function getSnapToken(string $table_number, Sale $sale, MidtransService $midtransService): JsonResponse
    {
        $tableNumber = strtoupper(trim($table_number));

        if ($sale->table_number !== $tableNumber) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak cocok.'], 404);
        }

        $token = $midtransService->createSnapToken($sale);

        return response()->json([
            'success' => (bool) $token,
            'token' => $token,
        ]);
    }

    /**
     * Webhook HTTP Notification dari Midtrans Gateway.
     */
    public function midtransNotification(Request $request, MidtransService $midtransService): JsonResponse
    {
        $sale = $midtransService->handleNotification($request->all());

        return response()->json([
            'status' => 'success',
            'sale_id' => $sale?->id,
            'payment_status' => $sale?->payment_status,
        ]);
    }
}

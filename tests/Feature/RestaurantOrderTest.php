<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\OpenBill;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RestaurantOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected User $kitchen;

    protected User $admin;

    protected Category $category;

    protected Product $food;

    protected Product $drink;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create([
            'email' => 'admin@pos.test',
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'role' => 'cashier',
            'email' => 'kasir@pos.test',
            'is_active' => true,
        ]);

        $this->kitchen = User::factory()->create([
            'role' => 'kitchen',
            'email' => 'dapur@pos.test',
            'is_active' => true,
        ]);

        $this->category = Category::factory()->create([
            'name' => 'Makanan Utama',
            'is_active' => true,
        ]);

        $this->food = Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Nasi Goreng Spesial',
            'sku' => 'NG-001',
            'selling_price' => 25000,
            'is_active' => true,
        ]);

        $this->drink = Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Es Teh Manis',
            'sku' => 'ET-001',
            'selling_price' => 5000,
            'is_active' => true,
        ]);

        RestaurantTable::create([
            'table_number' => 'M001',
            'name' => 'Meja 01 (Indoor)',
            'status' => 'available',
        ]);

        RestaurantTable::create([
            'table_number' => 'M002',
            'name' => 'Meja 02 (Indoor)',
            'status' => 'available',
        ]);
    }

    /**
     * 1. Pelanggan dapat membuka halaman pemesanan QR code (/order/{table_number}).
     */
    public function test_customer_can_view_menu_on_table_qr_page(): void
    {
        $response = $this->get(route('order.index', 'M001'));
        $response->assertOk();
        $response->assertSee('Meja M001');
        $response->assertSee('Nasi Goreng Spesial');
        $response->assertSee('Es Teh Manis');
    }

    /**
     * 2. Pelanggan memesan mandiri via checkout.
     * Sistem otomatis menyimpan pesanan, status pesanan 'pending', status bayar 'unpaid'.
     */
    public function test_customer_can_self_order_successfully(): void
    {
        $response = $this->post(route('order.checkout', 'M001'), [
            'customer_name' => 'Dimas',
            'notes' => 'Tolong pedas sedikit',
            'items' => [
                [
                    'id' => $this->food->id,
                    'quantity' => 2,
                    'notes' => 'Pedas level 1',
                ],
                [
                    'id' => $this->drink->id,
                    'quantity' => 1,
                    'notes' => 'Es sedikit',
                ],
            ],
        ]);

        $sale = Sale::where('table_number', 'M001')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(55000, (float) $sale->total_amount); // 2 * 25000 + 1 * 5000
        $this->assertEquals('pending', $sale->order_status);
        $this->assertEquals('unpaid', $sale->payment_status);
        $this->assertEquals($this->cashier->id, $sale->user_id); // Dicatat pada kasir yang sedang aktif login

        $response->assertRedirect(route('order.status', ['M001', $sale->id]));
    }

    /**
     * 3. Kasir tidak lagi bertugas memilih menu / membuat pesanan POS (/pos redirect ke cashier.dashboard).
     */
    public function test_cashier_is_redirected_away_from_manual_pos(): void
    {
        $this->actingAs($this->cashier);

        $response = $this->get(route('pos.index'));
        $response->assertRedirect(route('cashier.dashboard'));
    }

    /**
     * 4. Dashboard Kasir menampilkan tabel pesanan pelanggan: ID Pesanan, Meja, Total, Pembayaran, Aksi.
     */
    public function test_cashier_dashboard_displays_orders_table_with_required_columns(): void
    {
        $sale = Sale::create([
            'transaction_number' => 'ORD001',
            'table_number' => 'M001',
            'customer_name' => 'Budi',
            'transaction_date' => now(),
            'subtotal' => 30000,
            'total_amount' => 30000,
            'status' => 'pending',
            'order_status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($this->cashier);
        $response = $this->get(route('cashier.dashboard'));

        $response->assertOk();
        $response->assertSee('ID Pesanan');
        $response->assertSee('Meja');
        $response->assertSee('Total');
        $response->assertSee('Pembayaran');
        $response->assertSee('Aksi');

        $response->assertSee('ORD001');
        $response->assertSee('M001');
        $response->assertSee('Belum Dibayar');
        $response->assertSee('Konfirmasi');
    }

    /**
     * 5. Kasir mengonfirmasi pembayaran: payment_status menjadi 'paid', mencatat confirmed_by dan confirmed_at.
     */
    public function test_cashier_confirms_payment_successfully(): void
    {
        $sale = Sale::create([
            'transaction_number' => 'ORD002',
            'table_number' => 'M001',
            'customer_name' => 'Ani',
            'transaction_date' => now(),
            'subtotal' => 50000,
            'total_amount' => 50000,
            'status' => 'pending',
            'order_status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($this->cashier);

        $response = $this->post(route('cashier.orders.confirm_payment', $sale->id), [
            'payment_method' => 'cash',
            'amount_tendered' => 50000,
        ]);

        $response->assertRedirect();
        $sale->refresh();

        $this->assertEquals('paid', $sale->payment_status);
        $this->assertEquals('completed', $sale->status);
        $this->assertEquals($this->cashier->id, $sale->confirmed_by);
        $this->assertNotNull($sale->confirmed_at);

        // Payment record created
        $payment = Payment::where('sale_id', $sale->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals(50000, (float) $payment->amount_paid);
    }

    /**
     * 6. Pemisahan Status: Dapur memperbarui order_status (pending -> processing -> completed)
     * tanpa mengubah payment_status.
     */
    public function test_kitchen_updates_order_status_without_affecting_payment_status(): void
    {
        $sale = Sale::create([
            'transaction_number' => 'ORD003',
            'table_number' => 'M001',
            'transaction_date' => now(),
            'subtotal' => 25000,
            'total_amount' => 25000,
            'status' => 'pending',
            'order_status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($this->kitchen);

        // Dapur mulai masak
        $response = $this->patch(route('kitchen.orders.status', $sale->id), [
            'order_status' => 'processing',
        ]);
        $response->assertRedirect();

        $sale->refresh();
        $this->assertEquals('processing', $sale->order_status);
        $this->assertEquals('unpaid', $sale->payment_status); // Tetap Belum Dibayar

        // Dapur selesai masak
        $response2 = $this->patch(route('kitchen.orders.status', $sale->id), [
            'order_status' => 'completed',
        ]);
        $response2->assertRedirect();

        $sale->refresh();
        $this->assertEquals('completed', $sale->order_status);
        $this->assertEquals('unpaid', $sale->payment_status); // Tetap Belum Dibayar
    }

    /**
     * 7. Fitur Open Bill: Kasir membuka open bill untuk meja, pesanan baru otomatis digabung,
     * dan pelunasan open bill menutup sesi meja dan menandai semua pesanan lunas.
     */
    public function test_open_bill_workflow_merges_orders_and_settles_all(): void
    {
        $this->actingAs($this->cashier);

        // Buka open bill meja M002
        $openBillResponse = $this->post(route('cashier.open_bills.store'), [
            'table_number' => 'M002',
            'notes' => 'Rombongan Keluarga',
        ]);
        $openBillResponse->assertRedirect();

        $openBill = OpenBill::where('table_number', 'M002')->where('status', 'open')->first();
        $this->assertNotNull($openBill);

        // Pelanggan membuat pesanan pertama di M002
        $this->post(route('order.checkout', 'M002'), [
            'items' => [
                ['id' => $this->food->id, 'quantity' => 1],
            ],
        ]);

        // Pelanggan membuat pesanan kedua di M002
        $this->post(route('order.checkout', 'M002'), [
            'items' => [
                ['id' => $this->drink->id, 'quantity' => 2],
            ],
        ]);

        $openBill->refresh();
        $this->assertEquals(2, $openBill->sales()->count());
        $this->assertEquals(35000, (float) $openBill->total_amount); // 25.000 + 10.000

        // Kasir melunasi seluruh tagihan Open Bill
        $this->actingAs($this->cashier);
        $payResponse = $this->post(route('cashier.open_bills.confirm_payment', $openBill->id), [
            'payment_method' => 'cash',
            'amount_tendered' => 40000,
        ]);
        $payResponse->assertRedirect(route('cashier.open_bills.index'));

        $openBill->refresh();
        $this->assertEquals('paid', $openBill->status);
        $this->assertEquals($this->cashier->id, $openBill->closed_by);

        // Semua pesanan dalam open bill berstatus paid
        foreach ($openBill->sales as $sale) {
            $this->assertEquals('paid', $sale->payment_status);
            $this->assertEquals($this->cashier->id, $sale->confirmed_by);
        }
    }

    /**
     * 8. Kasir TIDAK memiliki akses ke Layar Dapur KDS (Akses Ditolak / 403).
     */
    public function test_cashier_cannot_access_kitchen_display_kds(): void
    {
        $this->actingAs($this->cashier);

        $response = $this->get(route('kitchen.index'));
        $response->assertForbidden();
    }

    /**
     * 9. Kitchen staff memiliki roleLabel 'DAPUR' dan dapat mengakses Layar Dapur.
     */
    public function test_kitchen_role_label_is_dapur_and_can_access_kds(): void
    {
        $this->assertEquals('DAPUR', $this->kitchen->roleLabel());

        $this->actingAs($this->kitchen);
        $response = $this->get(route('kitchen.index'));
        $response->assertOk();
        $response->assertSee('Antrean Masakan Dapur');
    }

    /**
     * 10. Admin dapat mengelola Data Meja (CRUD) dan mencetak barcode / QR Code meja.
     */
    public function test_admin_can_manage_restaurant_tables_crud_and_print_qr(): void
    {
        $this->actingAs($this->admin);

        // A. Buka daftar meja
        $indexResponse = $this->get(route('admin.tables.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Manajemen Data Meja Restoran');
        $indexResponse->assertSee('M001');

        // B. Tambah meja baru
        $storeResponse = $this->post(route('admin.tables.store'), [
            'table_number' => 'M009',
            'name' => 'Meja 09 (Outdoor)',
            'status' => 'available',
        ]);
        $storeResponse->assertRedirect(route('admin.tables.index'));
        $this->assertDatabaseHas('restaurant_tables', [
            'table_number' => 'M009',
            'name' => 'Meja 09 (Outdoor)',
        ]);

        $newTable = RestaurantTable::where('table_number', 'M009')->first();

        // C. Ubah data meja
        $updateResponse = $this->put(route('admin.tables.update', $newTable->id), [
            'table_number' => 'M009-VIP',
            'name' => 'Meja 09 VIP (Rooftop)',
            'status' => 'available',
        ]);
        $updateResponse->assertRedirect(route('admin.tables.index'));
        $this->assertDatabaseHas('restaurant_tables', [
            'table_number' => 'M009-VIP',
            'name' => 'Meja 09 VIP (Rooftop)',
        ]);

        // D. Halaman cetak barcode / QR Code meja
        $printResponse = $this->get(route('admin.tables.print_qr', $newTable->id));
        $printResponse->assertOk();
        $printResponse->assertSee('M009-VIP');
        $printResponse->assertSee('Scan Barcode / QR Meja');

        // E. Hapus meja
        $deleteResponse = $this->delete(route('admin.tables.destroy', $newTable->id));
        $deleteResponse->assertRedirect(route('admin.tables.index'));
        $this->assertDatabaseMissing('restaurant_tables', ['id' => $newTable->id]);
    }

    /**
     * 11. Meja tidak dapat dihapus jika masih memiliki pesanan aktif yang belum dibayar.
     */
    public function test_admin_cannot_delete_table_with_active_unpaid_orders(): void
    {
        $table = RestaurantTable::where('table_number', 'M001')->first();

        Sale::create([
            'transaction_number' => 'ORD-DEL-01',
            'table_number' => 'M001',
            'transaction_date' => now(),
            'subtotal' => 25000,
            'total_amount' => 25000,
            'status' => 'pending',
            'order_status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($this->admin);
        $response = $this->delete(route('admin.tables.destroy', $table->id));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('restaurant_tables', ['id' => $table->id]);
    }

    /**
     * 12. Admin dapat membuat dan mengubah pengguna dengan role Dapur (kitchen) & Kasir.
     */
    public function test_admin_can_create_and_update_user_with_kitchen_and_cashier_role(): void
    {
        $this->actingAs($this->admin);

        // Buat user dapur
        $storeResponse = $this->post(route('admin.users.store'), [
            'name' => 'Chef Juna',
            'email' => 'chef.juna@pos.test',
            'role' => 'kitchen',
            'password' => 'secret123',
        ]);
        $storeResponse->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'chef.juna@pos.test')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isKitchen());
        $this->assertEquals('DAPUR', $user->roleLabel());

        // Update jadi kasir
        $updateResponse = $this->put(route('admin.users.update', $user->id), [
            'name' => 'Chef Juna (Kasir)',
            'email' => 'chef.juna@pos.test',
            'role' => 'cashier',
        ]);
        $updateResponse->assertRedirect(route('admin.users.index'));

        $user->refresh();
        $this->assertTrue($user->isCashier());
        $this->assertEquals('KASIR', $user->roleLabel());
    }

    /**
     * 13. Pelanggan memilih metode Bayar Tunai di Kasir.
     */
    public function test_customer_can_choose_cash_payment_and_pay_at_cashier(): void
    {
        $response = $this->post(route('order.checkout', 'M001'), [
            'customer_name' => 'Budi Tunai',
            'payment_choice' => 'cash',
            'items' => [
                ['id' => $this->food->id, 'quantity' => 1],
            ],
        ]);

        $sale = Sale::where('customer_name', 'Budi Tunai')->first();
        $this->assertNotNull($sale);
        $this->assertEquals('unpaid', $sale->payment_status);
        $this->assertNull($sale->open_bill_id);

        $response->assertRedirect(route('order.status', ['M001', $sale->id]));

        // Kasir mengonfirmasi pembayaran tunai
        $this->actingAs($this->cashier);
        $confirmResponse = $this->post(route('cashier.orders.confirm_payment', $sale->id), [
            'payment_method' => 'cash',
            'amount_tendered' => 25000,
        ]);
        $confirmResponse->assertRedirect();

        $sale->refresh();
        $this->assertEquals('paid', $sale->payment_status);
        $this->assertEquals('completed', $sale->status);
    }

    /**
     * 14. Pelanggan memilih metode Payment Gateway Midtrans dan pembayaran berhasil dikonfirmasi.
     */
    public function test_customer_can_pay_via_midtrans_gateway_and_auto_confirm_payment(): void
    {
        $response = $this->postJson(route('order.checkout', 'M001'), [
            'customer_name' => 'Siti Online',
            'payment_choice' => 'midtrans',
            'items' => [
                ['id' => $this->food->id, 'quantity' => 2],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['success', 'redirect_url']);

        $sale = Sale::where('customer_name', 'Siti Online')->first();
        $this->assertNotNull($sale);

        // Simulasi callback Midtrans Snap sukses
        $callbackResponse = $this->postJson(route('order.midtrans_success', ['M001', $sale->id]), [
            'transaction_id' => 'MIDTRANS-TRX-12345',
        ]);

        $callbackResponse->assertOk();
        $callbackResponse->assertJson(['success' => true]);

        $sale->refresh();
        $this->assertEquals('paid', $sale->payment_status);
        $this->assertEquals('completed', $sale->status);

        $payment = Payment::where('sale_id', $sale->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('midtrans', $payment->method);
        $this->assertEquals('MIDTRANS-TRX-12345', $payment->reference_number);
    }

    /**
     * 15. Kasir dapat membuka halaman Riwayat Pembayaran Lunas tanpa ada eror.
     */
    public function test_cashier_can_open_paid_transactions_history_without_error(): void
    {
        // Buat transaksi lunas
        $sale = Sale::create([
            'transaction_number' => 'ORD-PAID-001',
            'table_number' => 'M001',
            'customer_name' => 'Pelanggan Lunas',
            'transaction_date' => now(),
            'subtotal' => 30000,
            'total_amount' => 30000,
            'status' => 'completed',
            'order_status' => 'completed',
            'payment_status' => 'paid',
            'confirmed_by' => $this->cashier->id,
            'confirmed_at' => now(),
        ]);

        Payment::create([
            'sale_id' => $sale->id,
            'method' => 'midtrans',
            'amount_paid' => 30000,
            'amount_tendered' => 30000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->actingAs($this->cashier);

        $response = $this->get(route('cashier.transactions.index'));
        $response->assertOk();
        $response->assertSee('Riwayat Pembayaran Lunas');
        $response->assertSee('ORD-PAID-001');

        $showResponse = $this->get(route('cashier.transactions.show', $sale->id));
        $showResponse->assertOk();
        $showResponse->assertSee('ORD-PAID-001');
    }

    /**
     * 16. Riwayat transaksi lunas kasir hanya menampilkan transaksi kasir yang bersangkutan.
     */
    public function test_cashier_transaction_history_only_shows_transactions_served_by_own_id(): void
    {
        $cashierB = User::factory()->create([
            'role' => 'cashier',
            'email' => 'kasir.b@pos.test',
            'is_active' => true,
        ]);

        // Transaksi oleh Kasir A ($this->cashier)
        $saleA = Sale::create([
            'transaction_number' => 'ORD-CASHIER-A',
            'table_number' => 'M001',
            'customer_name' => 'Pelanggan Kasir A',
            'transaction_date' => now(),
            'subtotal' => 20000,
            'total_amount' => 20000,
            'status' => 'completed',
            'order_status' => 'completed',
            'payment_status' => 'paid',
            'confirmed_by' => $this->cashier->id,
            'confirmed_at' => now(),
        ]);

        Payment::create([
            'sale_id' => $saleA->id,
            'method' => 'cash',
            'amount_paid' => 20000,
            'amount_tendered' => 20000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Transaksi oleh Kasir B
        $saleB = Sale::create([
            'transaction_number' => 'ORD-CASHIER-B',
            'table_number' => 'M002',
            'customer_name' => 'Pelanggan Kasir B',
            'transaction_date' => now(),
            'subtotal' => 45000,
            'total_amount' => 45000,
            'status' => 'completed',
            'order_status' => 'completed',
            'payment_status' => 'paid',
            'confirmed_by' => $cashierB->id,
            'confirmed_at' => now(),
        ]);

        Payment::create([
            'sale_id' => $saleB->id,
            'method' => 'cash',
            'amount_paid' => 45000,
            'amount_tendered' => 50000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Login sebagai Kasir A
        $this->actingAs($this->cashier);

        $indexResponse = $this->get(route('cashier.transactions.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('ORD-CASHIER-A');
        $indexResponse->assertDontSee('ORD-CASHIER-B');

        // Kasir A bisa melihat detail transaksi miliknya
        $showResponse = $this->get(route('cashier.transactions.show', $saleA->id));
        $showResponse->assertOk();

        // Kasir A TIDAK BISA melihat detail transaksi Kasir B (403 Forbidden)
        $forbiddenResponse = $this->get(route('cashier.transactions.show', $saleB->id));
        $forbiddenResponse->assertForbidden();
    }

    /**
     * 17. Layar Dapur menampilkan pesanan belum lunas JIKA meja mengaktifkan fitur Open Bill.
     */
    public function test_kitchen_screen_shows_unpaid_order_for_open_bill_table_immediately(): void
    {
        $openBill = OpenBill::create([
            'bill_number' => 'BILL-TEST-001',
            'table_number' => 'M002',
            'opened_by' => $this->cashier->id,
            'status' => 'open',
            'total_amount' => 25000,
            'opened_at' => now(),
        ]);

        $sale = Sale::create([
            'transaction_number' => 'ORD-OPENBILL-01',
            'table_number' => 'M002',
            'customer_name' => 'Meja Open Bill',
            'transaction_date' => now(),
            'subtotal' => 25000,
            'total_amount' => 25000,
            'status' => 'pending',
            'order_status' => 'pending',
            'payment_status' => 'unpaid',
            'open_bill_id' => $openBill->id,
        ]);

        $this->actingAs($this->kitchen);

        $response = $this->get(route('kitchen.index'));
        $response->assertOk();
        $response->assertSee('ORD-OPENBILL-01');
        $response->assertSee('M002');
    }

    /**
     * 18. Layar Dapur TIDAK menampilkan pesanan non-open bill yang belum dibayar,
     * dan baru menampilkannya setelah status pembayaran lunas (paid).
     */
    public function test_kitchen_screen_hides_unpaid_order_for_non_open_bill_until_paid(): void
    {
        $sale = Sale::create([
            'transaction_number' => 'ORD-NON-OPENBILL-01',
            'table_number' => 'M001',
            'customer_name' => 'Pelanggan Meja 1',
            'transaction_date' => now(),
            'subtotal' => 30000,
            'total_amount' => 30000,
            'status' => 'pending',
            'order_status' => 'pending',
            'payment_status' => 'unpaid',
            'open_bill_id' => null, // Bukan open bill
        ]);

        $this->actingAs($this->kitchen);

        // Belum bayar: TIDAK boleh tampil di dapur
        $responseBefore = $this->get(route('kitchen.index'));
        $responseBefore->assertOk();
        $responseBefore->assertDontSee('ORD-NON-OPENBILL-01');

        // Kasir melunasi pesanan
        $sale->update([
            'payment_status' => 'paid',
            'status' => 'completed',
            'confirmed_by' => $this->cashier->id,
            'confirmed_at' => now(),
        ]);

        // Sudah bayar: SEKARANG TAMPIL di dapur
        $responseAfter = $this->get(route('kitchen.index'));
        $responseAfter->assertOk();
        $responseAfter->assertSee('ORD-NON-OPENBILL-01');
    }

    /**
     * 19. Riwayat Transaksi Admin & Pemilik mendukung filter 'Hari Ini' dan rincian accordion.
     */
    public function test_admin_and_owner_transaction_history_supports_today_filter_and_accordion(): void
    {
        $owner = User::factory()->owner()->create(['is_active' => true]);

        $saleToday = Sale::factory()->create([
            'transaction_number' => 'TRX-TODAY-01',
            'status' => 'completed',
            'transaction_date' => now(),
            'total_amount' => 45000,
            'table_number' => 'M001',
            'customer_name' => 'Budi Hari Ini',
        ]);
        SaleItem::factory()->create([
            'sale_id' => $saleToday->id,
            'product_name_snapshot' => 'Nasi Goreng Spesial',
            'sku_snapshot' => 'NG-001',
            'quantity' => 1,
            'unit_price' => 25000,
            'line_total' => 25000,
        ]);

        $saleOld = Sale::factory()->create([
            'transaction_number' => 'TRX-OLD-99',
            'status' => 'completed',
            'transaction_date' => now()->subDays(5),
            'total_amount' => 50000,
        ]);

        // Admin view with period=today
        $this->actingAs($this->admin);
        $resAdmin = $this->get(route('admin.transactions.index', ['period' => 'today']));
        $resAdmin->assertOk();
        $resAdmin->assertSee('TRX-TODAY-01');
        $resAdmin->assertDontSee('TRX-OLD-99');
        $resAdmin->assertSee('Hari Ini');
        $resAdmin->assertSee('toggleTrxDetail(');

        // Owner view with period=today
        $this->actingAs($owner);
        $resOwner = $this->get(route('owner.transactions.index', ['period' => 'today']));
        $resOwner->assertOk();
        $resOwner->assertSee('TRX-TODAY-01');
        $resOwner->assertDontSee('TRX-OLD-99');
    }

    /**
     * 20. Halaman Data Menu Admin memiliki tabel 'Menu', form 5 kolom (kode menu, kategori, nama, harga, foto) dan unggah foto.
     */
    public function test_admin_menu_management_form_fields_and_image_upload(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin);

        // Index page table headers
        $resIndex = $this->get(route('admin.products.index'));
        $resIndex->assertOk();
        $resIndex->assertSee('Data Menu');
        $resIndex->assertSee('Kode Menu');
        $resIndex->assertSee('Nama Menu');
        $resIndex->assertSee('Harga');
        $resIndex->assertSee('Tambah Menu');

        // Create page form fields
        $resCreate = $this->get(route('admin.products.create'));
        $resCreate->assertOk();
        $resCreate->assertSee('Tambah Menu Baru');
        $resCreate->assertSee('Kode Menu');
        $resCreate->assertSee('Kategori Menu');
        $resCreate->assertSee('Nama Menu');
        $resCreate->assertSee('Harga Menu');
        $resCreate->assertSee('Deskripsi Menu');
        $resCreate->assertSee('Foto Menu');

        // Store menu with image and description
        $imageFile = UploadedFile::fake()->image('ayam_bakar.jpg', 600, 600);
        $postRes = $this->post(route('admin.products.store'), [
            'sku' => 'MNU-AYAM-01',
            'category_id' => $this->category->id,
            'name' => 'Ayam Bakar Madu',
            'description' => 'Ayam bakar dengan baluran saus madu gurih manis khas resto.',
            'selling_price' => 32000,
            'image' => $imageFile,
        ]);

        $postRes->assertRedirect(route('admin.products.index'));
        $postRes->assertSessionHas('success');

        $product = Product::where('sku', 'MNU-AYAM-01')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->image);
        $this->assertEquals('Ayam bakar dengan baluran saus madu gurih manis khas resto.', $product->description);
        $this->assertEquals(32000, (float) $product->selling_price);
        Storage::disk('public')->assertExists($product->image);
    }

    /**
     * 21. Modal konfirmasi kasir mengosongkan nominal uang diterima secara default dan menyediakan tombol uang pas.
     */
    public function test_cashier_dashboard_modal_and_customer_ordering_features(): void
    {
        // Cashier dashboard confirmation modal
        $this->actingAs($this->cashier);
        $resCashier = $this->get(route('cashier.dashboard'));
        $resCashier->assertOk();
        $resCashier->assertSee('Uang Pas');
        $resCashier->assertSee("document.getElementById('inputTendered').value = ''", false);

        // Customer menu display shows photo when image is set
        $this->food->update(['image' => 'products/sample.jpg']);
        $resCust = $this->get(route('order.index', 'M001'));
        $resCust->assertOk();
        $resCust->assertSee('Nasi Goreng Spesial');
        $resCust->assertSee('object-cover');
    }

    /**
     * 22. Dashboard dan Laporan Admin maupun Pemilik tidak lagi menampilkan HPP atau estimasi laba kotor.
     */
    public function test_admin_and_owner_reports_and_dashboards_exclude_hpp_and_laba_kotor(): void
    {
        $owner = User::factory()->owner()->create(['is_active' => true]);

        // Admin Dashboard & Reports
        $this->actingAs($this->admin);
        $resAdminDash = $this->get(route('admin.dashboard'));
        $resAdminDash->assertOk();
        $resAdminDash->assertDontSee('Laba Kotor');
        $resAdminDash->assertDontSee('Total HPP');

        $resAdminRep = $this->get(route('admin.reports.index'));
        $resAdminRep->assertOk();
        $resAdminRep->assertDontSee('Laba Kotor');
        $resAdminRep->assertDontSee('Total HPP');
        $resAdminRep->assertSee('Rata-rata Transaksi');

        // Owner Dashboard & Reports
        $this->actingAs($owner);
        $resOwnerDash = $this->get(route('owner.dashboard'));
        $resOwnerDash->assertOk();
        $resOwnerDash->assertDontSee('HPP (Modal Barang)');
        $resOwnerDash->assertDontSee('Laba Kotor (Margin)');
        $resOwnerDash->assertSee('Menu Terjual');

        $resOwnerRep = $this->get(route('owner.reports.index'));
        $resOwnerRep->assertOk();
        $resOwnerRep->assertDontSee('Total HPP (Modal Barang)');
        $resOwnerRep->assertDontSee('Estimasi Laba Kotor');
        $resOwnerRep->assertSee('Total Menu Terjual');
    }

    /**
     * 23. Pelanggan dapat mengajukan request Buka Open Bill dari meja, dan kasir dapat melihat request tersebut.
     */
    public function test_customer_can_request_open_bill_and_cashier_sees_request(): void
    {
        // 1. Pelanggan buka halaman pemesanan meja M001
        $resIndex = $this->get(route('order.index', 'M001'));
        $resIndex->assertOk();
        $resIndex->assertSee('Minta Buka Open Bill');

        // 2. Pelanggan kirim request open bill
        $postRes = $this->post(route('order.request_open_bill', 'M001'), [
            'customer_name' => 'Dimas Pemesan',
            'notes' => 'Makan berempat',
        ]);
        $postRes->assertRedirect();
        $postRes->assertSessionHas('success');

        $openBill = OpenBill::where('table_number', 'M001')->where('status', 'requested')->first();
        $this->assertNotNull($openBill);
        $this->assertEquals('Dimas Pemesan', $openBill->customer_name);

        // 3. Meja M001 sekarang menampilkan status menunggu konfirmasi kasir
        $resPending = $this->get(route('order.index', 'M001'));
        $resPending->assertOk();
        $resPending->assertSee('Permintaan Open Bill Sedang Menunggu Kasir');

        // 4. Kasir melihat notifikasi permintaan open bill di dashboard kasir dan halaman open bill
        $this->actingAs($this->cashier);
        $resCashierDash = $this->get(route('cashier.dashboard'));
        $resCashierDash->assertOk();
        $resCashierDash->assertSee('Permintaan Buka Open Bill Masuk');
        $resCashierDash->assertSee('Dimas Pemesan');
        $resCashierDash->assertSee('M001');

        $resCashierOpenBill = $this->get(route('cashier.open_bills.index'));
        $resCashierOpenBill->assertOk();
        $resCashierOpenBill->assertSee('Permintaan Buka Open Bill Menunggu Persetujuan');
        $resCashierOpenBill->assertSee('Dimas Pemesan');
    }

    /**
     * 24. Kasir dapat menyetujui (approve) atau menolak (reject) permintaan Open Bill.
     */
    public function test_cashier_can_approve_or_reject_open_bill_request(): void
    {
        // Setup request open bill dari Meja M001
        $reqBill = OpenBill::create([
            'bill_number' => 'BILL-TEST-001',
            'table_number' => 'M001',
            'customer_name' => 'Budi Customer',
            'status' => 'requested',
            'opened_at' => now(),
            'total_amount' => 0,
        ]);

        $this->actingAs($this->cashier);

        // Kasir setujui request
        $approveRes = $this->post(route('cashier.open_bills.approve', $reqBill->id));
        $approveRes->assertRedirect();
        $approveRes->assertSessionHas('success');

        $reqBill->refresh();
        $this->assertEquals('open', $reqBill->status);
        $this->assertEquals($this->cashier->id, $reqBill->opened_by);
        $this->assertEquals('occupied', RestaurantTable::where('table_number', 'M001')->value('status'));

        // Test reject for another request
        $reqBill2 = OpenBill::create([
            'bill_number' => 'BILL-TEST-002',
            'table_number' => 'M002',
            'customer_name' => 'Andi Customer',
            'status' => 'requested',
            'opened_at' => now(),
            'total_amount' => 0,
        ]);

        $rejectRes = $this->post(route('cashier.open_bills.reject', $reqBill2->id));
        $rejectRes->assertRedirect();
        $reqBill2->refresh();
        $this->assertEquals('rejected', $reqBill2->status);
    }

    /**
     * 25. Meja Open Bill menampilkan Nota Tagihan Berjalan (akumulasi seluruh pesanan) saat dibuka kembali.
     * Pelanggan dapat memesan tambahan tanpa bayar di awal, dan memilih opsi 'Masih Ingin Memesan' atau 'Sudah Mau Membayar'.
     */
    public function test_open_bill_table_displays_running_bill_and_supports_ordering_without_prepayment(): void
    {
        $openBill = OpenBill::create([
            'bill_number' => 'BILL-20261029-ABCD',
            'table_number' => 'M001',
            'customer_name' => 'Dimas Rombongan',
            'status' => 'open',
            'opened_by' => $this->cashier->id,
            'opened_at' => now(),
            'total_amount' => 0,
        ]);

        // Pesanan 1 di meja ini: Nasi Goreng (25.000)
        $sale1 = Sale::create([
            'transaction_number' => 'ORD-OPEN-01',
            'table_number' => 'M001',
            'transaction_date' => now(),
            'subtotal' => 25000,
            'total_amount' => 25000,
            'status' => 'pending',
            'order_status' => 'pending',
            'payment_status' => 'unpaid',
            'open_bill_id' => $openBill->id,
        ]);
        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $this->food->id,
            'product_name_snapshot' => $this->food->name,
            'sku_snapshot' => $this->food->sku,
            'quantity' => 1,
            'unit_price' => 25000,
            'line_total' => 25000,
        ]);
        $openBill->recalculateTotal();

        // Saat halaman meja M001 dibuka kembali: Nota Berjalan Tampil dengan total akumulasi
        $resTable = $this->get(route('order.index', 'M001'));
        $resTable->assertOk();
        $resTable->assertSee('Nota Tagihan Meja');
        $resTable->assertSee('BILL-20261029-ABCD');
        $resTable->assertSee('ORD-OPEN-01');
        $resTable->assertSee('25.000');
        $resTable->assertSee('Masih Ingin Memesan');
        $resTable->assertSee('Sudah Mau Membayar');

        // Pelanggan memesan tambahan menu tanpa bayar di awal: Es Teh (5.000)
        $postOrder2 = $this->post(route('order.checkout', 'M001'), [
            'payment_choice' => 'open_bill',
            'items' => [
                ['id' => $this->drink->id, 'quantity' => 2], // 2 x 5000 = 10000
            ],
        ]);
        $postOrder2->assertRedirect(route('order.index', 'M001'));

        $openBill->refresh();
        $this->assertEquals(35000, (float) $openBill->total_amount); // 25000 + 10000 = 35000
        $this->assertEquals(2, $openBill->sales()->count());

        // Buka kembali halaman meja: akumulasi total menjadi 35.000
        $resTableAfter = $this->get(route('order.index', 'M001'));
        $resTableAfter->assertOk();
        $resTableAfter->assertSee('35.000');

        // Pelunasan seluruh open bill secara online (Midtrans)
        Http::fake([
            '*midtrans*' => Http::response(['token' => 'dummy-snap-token-123'], 201),
        ]);
        $tokenRes = $this->post(route('order.open_bill_snap_token', 'M001'));
        $tokenRes->assertOk();
        $this->assertNotEmpty($tokenRes->json('token'));
        $this->assertEquals(35000, (float) $tokenRes->json('total_amount'));

        $successRes = $this->post(route('order.open_bill_midtrans_success', 'M001'), [
            'transaction_id' => 'MIDTRANS-SETTLE-001',
        ]);
        $successRes->assertOk();
        $this->assertTrue($successRes->json('success'));

        $openBill->refresh();
        $this->assertEquals('paid', $openBill->status);
        $this->assertEquals('available', RestaurantTable::where('table_number', 'M001')->value('status'));
    }

    public function test_customer_order_is_attributed_to_active_cashier(): void
    {
        // 1. Simulasikan sesi kasir aktif di tabel database sessions
        DB::table('sessions')->insert([
            'id' => 'session-cashier-test-id',
            'user_id' => $this->cashier->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Browser',
            'payload' => serialize(['test' => true]),
            'last_activity' => time(),
        ]);

        $this->assertEquals($this->cashier->id, User::getActiveCashierId());

        // 2. Pelanggan melakukan pemesanan mandiri via QR Code meja
        $response = $this->post(route('order.checkout', 'M001'), [
            'customer_name' => 'Budi Santoso',
            'payment_choice' => 'cash',
            'items' => [
                ['id' => $this->food->id, 'quantity' => 1],
            ],
        ]);

        $response->assertRedirect();
        $sale = Sale::latest()->first();
        $this->assertNotNull($sale);
        // Transaksi harus tercatat pada kasir yang sedang aktif login
        $this->assertEquals($this->cashier->id, $sale->user_id);
    }

    public function test_transaction_history_unifies_open_bill_into_single_row_with_label(): void
    {
        // 1. Buat Open Bill yang memiliki 2 pesanan berbeda
        $openBill = OpenBill::create([
            'bill_number' => 'BILL-20261029-UNIFY',
            'table_number' => 'M001',
            'customer_name' => 'Meja Rombongan',
            'status' => 'paid',
            'opened_by' => $this->cashier->id,
            'closed_by' => $this->cashier->id,
            'opened_at' => now()->subHour(),
            'closed_at' => now(),
            'total_amount' => 35000,
        ]);

        $sale1 = Sale::create([
            'transaction_number' => 'ORD-SUB-01',
            'table_number' => 'M001',
            'customer_name' => 'Meja Rombongan',
            'user_id' => $this->cashier->id,
            'transaction_date' => now()->subMinutes(45),
            'subtotal' => 25000,
            'total_amount' => 25000,
            'status' => 'completed',
            'order_status' => 'completed',
            'payment_status' => 'paid',
            'open_bill_id' => $openBill->id,
            'confirmed_by' => $this->cashier->id,
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $this->food->id,
            'product_name_snapshot' => $this->food->name,
            'sku_snapshot' => $this->food->sku,
            'quantity' => 1,
            'unit_price' => 25000,
            'line_total' => 25000,
        ]);

        $sale2 = Sale::create([
            'transaction_number' => 'ORD-SUB-02',
            'table_number' => 'M001',
            'customer_name' => 'Meja Rombongan',
            'user_id' => $this->cashier->id,
            'transaction_date' => now()->subMinutes(20),
            'subtotal' => 10000,
            'total_amount' => 10000,
            'status' => 'completed',
            'order_status' => 'completed',
            'payment_status' => 'paid',
            'open_bill_id' => $openBill->id,
            'confirmed_by' => $this->cashier->id,
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $this->drink->id,
            'product_name_snapshot' => $this->drink->name,
            'sku_snapshot' => $this->drink->sku,
            'quantity' => 2,
            'unit_price' => 5000,
            'line_total' => 10000,
        ]);

        Payment::create([
            'sale_id' => $sale1->id,
            'method' => 'cash',
            'amount_paid' => 35000,
            'amount_tendered' => 40000,
            'change_amount' => 5000,
            'status' => 'paid',
            'paid_at' => now(),
            'verified_by' => $this->cashier->id,
        ]);

        // 2. Buat transaksi reguler (bukan open bill)
        $saleRegular = Sale::create([
            'transaction_number' => 'ORD-REG-01',
            'table_number' => 'M001',
            'customer_name' => 'Pelanggan Cepat',
            'user_id' => $this->cashier->id,
            'transaction_date' => now(),
            'subtotal' => 25000,
            'total_amount' => 25000,
            'status' => 'completed',
            'order_status' => 'completed',
            'payment_status' => 'paid',
            'open_bill_id' => null,
            'confirmed_by' => $this->cashier->id,
        ]);

        SaleItem::create([
            'sale_id' => $saleRegular->id,
            'product_id' => $this->food->id,
            'product_name_snapshot' => $this->food->name,
            'sku_snapshot' => $this->food->sku,
            'quantity' => 1,
            'unit_price' => 25000,
            'line_total' => 25000,
        ]);

        Payment::create([
            'sale_id' => $saleRegular->id,
            'method' => 'cash',
            'amount_paid' => 25000,
            'amount_tendered' => 25000,
            'change_amount' => 0,
            'status' => 'paid',
            'paid_at' => now(),
            'verified_by' => $this->cashier->id,
        ]);

        // 3. Admin membuka halaman riwayat transaksi
        $responseAdmin = $this->actingAs($this->admin)->get(route('admin.transactions.index'));
        $responseAdmin->assertOk();

        // Riwayat Open Bill bersatu menjadi 1 transaksi (BILL-20261029-UNIFY) dengan label Open Bill
        $responseAdmin->assertSee('BILL-20261029-UNIFY');
        $responseAdmin->assertSee('Open Bill (2 Pesanan)');
        // Menampilkan rincian sub-pesanan di dalam accordion Open Bill
        $responseAdmin->assertSee('ORD-SUB-01');
        $responseAdmin->assertSee('ORD-SUB-02');
        // Transaksi reguler tetap tampil sebagai Reguler
        $responseAdmin->assertSee('ORD-REG-01');
        $responseAdmin->assertSee('Reguler');

        // 4. Kasir membuka halaman riwayat transaksi
        $responseCashier = $this->actingAs($this->cashier)->get(route('cashier.transactions.index'));
        $responseCashier->assertOk();
        $responseCashier->assertSee('BILL-20261029-UNIFY');
        $responseCashier->assertSee('Open Bill (2 Pesanan)');
        $responseCashier->assertSee('ORD-REG-01');
    }

    public function test_takeaway_page_can_be_accessed_by_cashier_and_admin_but_forbidden_for_kitchen_and_guest(): void
    {
        // 1. Guest diarahkan ke login
        $this->get(route('takeaway.index'))->assertRedirect(route('login'));

        // 2. Kitchen role dilarang (403)
        $this->actingAs($this->kitchen)->get(route('takeaway.index'))->assertForbidden();

        // 3. Kasir dapat mengakses
        $responseCashier = $this->actingAs($this->cashier)->get(route('takeaway.index'));
        $responseCashier->assertOk();
        $responseCashier->assertSee('Transaksi Penjualan');
        $responseCashier->assertSee('Keranjang Bawa Pulang');
        $responseCashier->assertSee('Fira Cake');

        // 4. Admin dapat mengakses
        $responseAdmin = $this->actingAs($this->admin)->get(route('takeaway.index'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Transaksi Penjualan');
        $responseAdmin->assertSee('Keranjang Bawa Pulang');
    }

    public function test_takeaway_order_can_be_checked_out_successfully_by_cashier(): void
    {
        $payload = [
            'customer_name' => 'Bapak Budi',
            'notes' => 'Tolong bungkus rapi pakai kantong',
            'payment_method' => 'cash',
            'amount_tendered' => 60000,
            'items' => [
                [
                    'id' => $this->food->id,
                    'quantity' => 2,
                    'notes' => 'Pedas',
                ],
                [
                    'id' => $this->drink->id,
                    'quantity' => 1,
                    'notes' => 'Dingin',
                ],
            ],
        ];

        $expectedTotal = (2 * (float) $this->food->selling_price) + (1 * (float) $this->drink->selling_price);

        $response = $this->actingAs($this->cashier)
            ->postJson(route('takeaway.checkout'), $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        // Verifikasi Sale tercatat di database
        $this->assertDatabaseHas('sales', [
            'table_number' => 'TAKEAWAY',
            'customer_name' => 'Bapak Budi',
            'user_id' => $this->cashier->id,
            'confirmed_by' => $this->cashier->id,
            'payment_status' => 'paid',
            'order_status' => 'pending',
            'status' => 'completed',
            'total_amount' => $expectedTotal,
        ]);

        // Verifikasi Payment tercatat
        $sale = Sale::where('table_number', 'TAKEAWAY')->latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals($expectedTotal, (float) $sale->total_amount);
        $this->assertEquals(60000 - $expectedTotal, (float) $sale->payment->change_amount);
        $this->assertEquals('paid', $sale->payment->status);

        // Verifikasi item detail
        $this->assertCount(2, $sale->items);

        // Verifikasi pesanan takeaway muncul di Layar Dapur (KDS)
        $kitchenResponse = $this->actingAs($this->kitchen)->get(route('kitchen.index'));
        $kitchenResponse->assertOk();
        $kitchenResponse->assertSee($sale->transaction_number);
        $kitchenResponse->assertSee('BAWA PULANG (TAKEAWAY)');

        // Verifikasi transaksi takeaway muncul di Riwayat Transaksi Lunas Kasir
        $cashierHistory = $this->actingAs($this->cashier)->get(route('cashier.transactions.index'));
        $cashierHistory->assertOk();
        $cashierHistory->assertSee($sale->transaction_number);
        $cashierHistory->assertSee('Takeaway');
        $cashierHistory->assertSee('Bawa Pulang');

        // Verifikasi transaksi takeaway muncul di Riwayat Transaksi Admin
        $adminHistory = $this->actingAs($this->admin)->get(route('admin.transactions.index'));
        $adminHistory->assertOk();
        $adminHistory->assertSee($sale->transaction_number);
        $adminHistory->assertSee('Takeaway');
        $adminHistory->assertSee('Bawa Pulang');
    }

    public function test_takeaway_order_can_be_checked_out_by_admin(): void
    {
        $payload = [
            'customer_name' => 'Pelanggan Walk-in',
            'payment_method' => 'qris',
            'items' => [
                [
                    'id' => $this->food->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->postJson(route('takeaway.checkout'), $payload);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('sales', [
            'table_number' => 'TAKEAWAY',
            'customer_name' => 'Pelanggan Walk-in',
            'user_id' => $this->admin->id,
            'payment_status' => 'paid',
        ]);
    }

    public function test_takeaway_order_insufficient_cash_fails(): void
    {
        $payload = [
            'payment_method' => 'cash',
            'amount_tendered' => 1000, // Sengaja kurang dari total harga
            'items' => [
                [
                    'id' => $this->food->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('takeaway.checkout'), $payload);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_takeaway_order_without_cash_tendered_fails_validation(): void
    {
        $payload = [
            'payment_method' => 'cash',
            'amount_tendered' => null, // Kasir lupa mengisi nominal uang tunai
            'items' => [
                [
                    'id' => $this->food->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('takeaway.checkout'), $payload);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Uang tunai yang diterima belum diisi. Masukkan nominal uang yang diterima atau klik Uang Pas.',
        ]);
    }
}

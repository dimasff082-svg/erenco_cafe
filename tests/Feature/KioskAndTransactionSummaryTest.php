<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KioskAndTransactionSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected User $otherCashier;

    protected User $admin;

    protected User $kitchen;

    protected Category $category;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create([
            'name' => 'Kasir Utama',
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->otherCashier = User::factory()->create([
            'name' => 'Kasir Lain',
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->admin()->create([
            'name' => 'Administrator',
            'is_active' => true,
        ]);

        $this->kitchen = User::factory()->create([
            'name' => 'Staf Dapur',
            'role' => 'kitchen',
            'is_active' => true,
        ]);

        $this->category = Category::factory()->create([
            'name' => 'Kue Basah',
            'is_active' => true,
        ]);

        $this->product = Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Brownies Fira Cake',
            'selling_price' => 50000,
            'is_active' => true,
        ]);
    }

    public function test_kiosk_menu_page_is_publicly_accessible(): void
    {
        $response = $this->get(route('kiosk.index'));

        $response->assertOk();
        $response->assertSee('Brownies Fira Cake');
        $response->assertSee('Self-Order');
    }

    public function test_kiosk_cash_order_is_unpaid_initially_and_only_appears_in_kitchen_after_paid(): void
    {
        // 1. Pelanggan memesan di Kiosk dengan metode TUNAI
        $response = $this->postJson(route('kiosk.checkout'), [
            'customer_name' => 'Pelanggan Mandiri 1',
            'customer_phone' => '081299998888',
            'notes' => 'Tolong bungkus rapi',
            'payment_method' => 'cash',
            'items' => [
                [
                    'id' => $this->product->id,
                    'quantity' => 2,
                    'notes' => 'Porsi manis',
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        // Status awal pesanan tunai: unpaid (belum lunas)
        $this->assertDatabaseHas('sales', [
            'customer_name' => 'Pelanggan Mandiri 1',
            'customer_phone' => '081299998888',
            'table_number' => 'KIOSK',
            'total_amount' => 100000,
            'order_status' => 'pending',
            'payment_status' => 'unpaid',
            'user_id' => $this->cashier->id,
        ]);

        $sale = Sale::where('customer_name', 'Pelanggan Mandiri 1')->first();
        $this->assertNotNull($sale);

        // 2. KDS Dapur TIDAK memunculkan pesanan ini sebelum lunas!
        $kitchenBeforePaid = $this->actingAs($this->kitchen)->get(route('kitchen.index'));
        $kitchenBeforePaid->assertOk();
        $kitchenBeforePaid->assertDontSee($sale->transaction_number);

        // Struk Kiosk memuat nomor pemilik toko 082131117996
        $receiptResponse = $this->get(route('kiosk.receipt', $sale->id));
        $receiptResponse->assertOk();
        $receiptResponse->assertSee('082131117996');
        $receiptResponse->assertSee('Pelanggan Mandiri 1');
        $receiptResponse->assertSee('081299998888');
        $receiptResponse->assertSee('Menunggu Bayar di Kasir');

        // 3. Pelanggan membayar tunai di kasir -> Kasir konfirmasi lunas
        $confirmResponse = $this->actingAs($this->cashier)->post(route('cashier.orders.confirm_payment', $sale->id), [
            'payment_method' => 'cash',
            'amount_tendered' => 100000,
        ]);
        $confirmResponse->assertRedirect();

        $sale->refresh();
        $this->assertTrue($sale->isPaid());

        // 4. Setelah lunas, pesanan BARU MUNCUL di Layar Dapur!
        $kitchenAfterPaid = $this->actingAs($this->kitchen)->get(route('kitchen.index'));
        $kitchenAfterPaid->assertOk();
        $kitchenAfterPaid->assertSee($sale->transaction_number);
        $kitchenAfterPaid->assertSee('Pelanggan Mandiri 1');
    }

    public function test_customer_can_checkout_on_kiosk_with_qris_gateway_and_appears_in_kitchen_immediately(): void
    {
        $response = $this->postJson(route('kiosk.checkout'), [
            'customer_name' => 'Pelanggan QRIS',
            'customer_phone' => '085566778899',
            'payment_method' => 'qris',
            'items' => [
                [
                    'id' => $this->product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('sales', [
            'customer_name' => 'Pelanggan QRIS',
            'payment_status' => 'paid',
        ]);

        $this->assertDatabaseHas('payments', [
            'method' => 'qris',
            'amount_paid' => 50000,
            'status' => 'paid',
        ]);

        $sale = Sale::where('customer_name', 'Pelanggan QRIS')->first();

        // Pesanan QRIS yang langsung lunas otomatis langsung muncul di dapur
        $kitchenResponse = $this->actingAs($this->kitchen)->get(route('kitchen.index'));
        $kitchenResponse->assertOk();
        $kitchenResponse->assertSee($sale->transaction_number);
    }

    public function test_takeaway_checkout_saves_customer_phone_number(): void
    {
        $response = $this->actingAs($this->cashier)->postJson(route('takeaway.checkout'), [
            'customer_name' => 'Ibu Siti',
            'customer_phone' => '082211334455',
            'notes' => 'Bungkus terpisah',
            'payment_method' => 'cash',
            'amount_tendered' => 60000,
            'items' => [
                [
                    'id' => $this->product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('sales', [
            'customer_name' => 'Ibu Siti',
            'customer_phone' => '082211334455',
            'table_number' => 'TAKEAWAY',
        ]);

        $sale = Sale::where('customer_name', 'Ibu Siti')->first();
        $this->assertNotNull($sale);

        // Periksa struk POS juga memuat nomor pemilik 082131117996 dan no HP pelanggan
        $receiptResponse = $this->actingAs($this->cashier)->get(route('pos.receipt', $sale->id));
        $receiptResponse->assertOk();
        $receiptResponse->assertSee('082131117996');
        $receiptResponse->assertSee('082211334455');
    }

    public function test_transaction_history_summary_scoped_for_cashier_and_overall_for_admin(): void
    {
        // 1. Transaksi untuk Kasir Utama: Rp 50.000
        $sale1 = Sale::create([
            'transaction_number' => 'TKW001',
            'table_number' => 'TAKEAWAY',
            'customer_name' => 'Pelanggan A',
            'user_id' => $this->cashier->id,
            'confirmed_by' => $this->cashier->id,
            'transaction_date' => now(),
            'subtotal' => 50000,
            'total_amount' => 50000,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);
        Payment::create([
            'sale_id' => $sale1->id,
            'method' => 'cash',
            'amount_paid' => 50000,
            'amount_tendered' => 50000,
            'change_amount' => 0,
            'status' => 'paid',
            'verified_by' => $this->cashier->id,
            'paid_at' => now(),
        ]);

        // 2. Transaksi untuk Kasir Lain: Rp 75.000
        $sale2 = Sale::create([
            'transaction_number' => 'TKW002',
            'table_number' => 'TAKEAWAY',
            'customer_name' => 'Pelanggan B',
            'user_id' => $this->otherCashier->id,
            'confirmed_by' => $this->otherCashier->id,
            'transaction_date' => now(),
            'subtotal' => 75000,
            'total_amount' => 75000,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);
        Payment::create([
            'sale_id' => $sale2->id,
            'method' => 'cash',
            'amount_paid' => 75000,
            'amount_tendered' => 75000,
            'change_amount' => 0,
            'status' => 'paid',
            'verified_by' => $this->otherCashier->id,
            'paid_at' => now(),
        ]);

        // Kasir Utama melihat riwayatnya: hanya 1 transaksi, Rp 50.000
        $cashierResponse = $this->actingAs($this->cashier)->get(route('cashier.transactions.index'));
        $cashierResponse->assertOk();
        $cashierResponse->assertSee('1');
        $cashierResponse->assertSee('50.000');
        $cashierResponse->assertDontSee('75.000');

        // Admin melihat riwayat: keseluruhan 2 transaksi, Rp 125.000
        $adminResponse = $this->actingAs($this->admin)->get(route('admin.transactions.index'));
        $adminResponse->assertOk();
        $adminResponse->assertSee('2');
        $adminResponse->assertSee('125.000');
    }
}

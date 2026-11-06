<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OnlineOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected User $kitchen;

    protected Category $category;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create([
            'name' => 'Kasir Toko',
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->kitchen = User::factory()->create([
            'name' => 'Staf Dapur',
            'role' => 'kitchen',
            'is_active' => true,
        ]);

        $this->category = Category::factory()->create([
            'name' => 'Kue Tart & Pastry',
            'is_active' => true,
        ]);

        $this->product = Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Croissant Almond Signature',
            'selling_price' => 35000,
            'is_active' => true,
        ]);

        Http::fake([
            '*midtrans*' => Http::response(['token' => 'dummy-snap-token-123'], 201),
        ]);
    }

    public function test_online_order_catalog_page_can_be_accessed_publicly(): void
    {
        $response = $this->get(route('online_order.index'));

        $response->assertStatus(200);
        $response->assertSee('Fira Cake');
        $response->assertSee('Croissant Almond Signature');
        $response->assertSee('Payment Gateway QRIS');
        $response->assertSee('Midtrans Payment Gateway');
    }

    public function test_pesan_online_redirects_to_online_order_index(): void
    {
        $response = $this->get('/pesan-online');

        $response->assertRedirect(route('online_order.index'));
    }

    public function test_checkout_validates_required_fields_and_customer_phone(): void
    {
        $response = $this->postJson(route('online_order.checkout'), [
            'customer_name' => '',
            'customer_phone' => '',
            'payment_method' => 'qris',
            'payment_method' => 'midtrans',
            'items' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['customer_name', 'customer_phone', 'items']);
    }

    public function test_checkout_rejects_cash_payment(): void
    {
        $response = $this->postJson(route('online_order.checkout'), [
            'customer_name' => 'Pelanggan Online',
            'customer_phone' => '081234567890',
            'payment_method' => 'cash',
            'items' => [
                [
                    'id' => $this->product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_method']);
    }

    public function test_checkout_succeeds_with_payment_gateway_qris(): void
    public function test_checkout_creates_order_and_can_be_confirmed_via_midtrans_success(): void
    {
        $payload = [
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081234567890',
            'address' => 'Jl. Mawar No. 12, Jakarta',
            'notes' => 'Tolong lilin ulang tahun 1 bungkus',
            'payment_method' => 'qris',
            'payment_method' => 'midtrans',
            'items' => [
                [
                    'id' => $this->product->id,
                    'quantity' => 2,
                    'notes' => 'Hangatkan sebentar',
                ],
            ],
        ];

        $response = $this->postJson(route('online_order.checkout'), $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $sale = Sale::where('customer_phone', '081234567890')->first();
        $this->assertNotNull($sale);
        $this->assertEquals('ONLINE', $sale->table_number);
        $this->assertEquals('completed', $sale->status);
        $this->assertEquals('paid', $sale->payment_status);
        $this->assertEquals('pending', $sale->order_status);
        $this->assertEquals('unpaid', $sale->payment_status);
        $this->assertEquals(70000, (float) $sale->total_amount);
        $this->assertStringStartsWith('ONL', $sale->transaction_number);

        // Pastikan catatan tersimpan
        $this->assertStringContainsString('Jl. Mawar No. 12', $sale->notes);
        // Simulasi callback sukses pembayaran Midtrans
        $successResponse = $this->postJson(route('online_order.midtrans_success', $sale->id), [
            'transaction_id' => 'MIDTRANS-TEST-998877',
        ]);

        // Pastikan detail pembayaran qris tersimpan
        $successResponse->assertStatus(200);
        $successResponse->assertJsonPath('success', true);

        $sale->refresh();
        $this->assertEquals('completed', $sale->status);
        $this->assertEquals('paid', $sale->payment_status);

        // Pastikan detail pembayaran midtrans tersimpan
        $payment = Payment::where('sale_id', $sale->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('qris', $payment->method);
        $this->assertEquals('midtrans', $payment->method);
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals('MIDTRANS-TEST-998877', $payment->reference_number);
        $this->assertEquals(70000, (float) $payment->amount_paid);

        // Pastikan redirect URL diarahkan ke halaman status
        $response->assertJsonPath('data.redirect_url', route('online_order.status', $sale->transaction_number));
    }

    public function test_online_order_status_page_can_be_viewed(): void
    public function test_unpaid_online_order_does_not_appear_on_cashier_dashboard_or_kitchen(): void
    {
        $sale = Sale::create([
            'transaction_number' => 'ONL099',
        $unpaidSale = Sale::create([
            'transaction_number' => 'ONL-UNPAID-01',
            'table_number' => 'ONLINE',
            'customer_name' => 'Siti Nurhaliza',
            'customer_phone' => '081987654321',
            'customer_name' => 'Belum Bayar',
            'customer_phone' => '089911223344',
            'user_id' => $this->cashier->id,
            'transaction_date' => now(),
            'subtotal' => 35000,
            'discount_amount' => 0,
            'total_amount' => 35000,
            'status' => 'completed',
            'status' => 'pending',
            'order_status' => 'pending',
            'payment_status' => 'paid',
            'notes' => '[Pesanan Online] | Alamat: Kompleks Melati Blok A3',
            'payment_status' => 'unpaid',
            'notes' => '[Pesanan Online]',
        ]);

        Payment::create([
            'sale_id' => $sale->id,
            'method' => 'qris',
            'amount_paid' => 35000,
            'amount_tendered' => 35000,
            'change_amount' => 0,
            'status' => 'paid',
            'reference_number' => 'PG-TEST12345',
            'paid_at' => now(),
        ]);
        // Kasir dashboard tidak boleh menampilkan pesanan online yang belum lunas
        $cashierRes = $this->actingAs($this->cashier)->get(route('cashier.dashboard'));
        $cashierRes->assertStatus(200);
        $cashierRes->assertDontSee('ONL-UNPAID-01');

        $response = $this->get(route('online_order.status', $sale->transaction_number));

        $response->assertStatus(200);
        $response->assertSee('ONL099');
        $response->assertSee('Siti Nurhaliza');
        $response->assertSee('Pembayaran Lunas');
        $response->assertSee('Menunggu Antrean Dapur');
        // Dapur tidak boleh menampilkan pesanan online yang belum lunas
        $kitchenRes = $this->actingAs($this->kitchen)->get(route('kitchen.index'));
        $kitchenRes->assertStatus(200);
        $kitchenRes->assertDontSee('ONL-UNPAID-01');
    }

    public function test_cashier_dashboard_displays_online_order_with_whatsapp_button(): void
    {
        $sale = Sale::create([
            'transaction_number' => 'ONL100',
            'table_number' => 'ONLINE',
            'customer_name' => 'Rina Wijaya',
            'customer_phone' => '081234567899',
            'user_id' => $this->cashier->id,
            'transaction_date' => now(),
            'subtotal' => 35000,
            'discount_amount' => 0,
            'total_amount' => 35000,
            'status' => 'completed',
            'order_status' => 'pending',
            'payment_status' => 'paid',
            'notes' => '[Pesanan Online]',
        ]);

        $response = $this->actingAs($this->cashier)->get(route('cashier.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('ONL100');
        $response->assertSee('PESANAN ONLINE');
        $response->assertSee('Chat WA');
        $response->assertSee('081234567899');
    }

    public function test_takeaway_order_does_not_have_whatsapp_button_on_cashier_dashboard(): void
    {
        $takeawaySale = Sale::create([
            'transaction_number' => 'TKW200',
            'table_number' => 'TAKEAWAY',
            'customer_name' => 'Andi Takeaway',
            'customer_phone' => '081399887766',
            'user_id' => $this->cashier->id,
            'transaction_date' => now(),
            'subtotal' => 35000,
            'discount_amount' => 0,
            'total_amount' => 35000,
            'status' => 'completed',
            'order_status' => 'pending',
            'payment_status' => 'paid',
            'notes' => '[Takeaway]',
        ]);

        $response = $this->actingAs($this->cashier)->get(route('cashier.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('TKW200');
        $response->assertSee('TAKEAWAY');
        // Pesanan takeaway TIDAK boleh memiliki link atau tombol Chat WA ke nomornya
        $response->assertDontSee('https://api.whatsapp.com/send?phone=6281399887766', false);
    }

    public function test_kitchen_display_shows_online_order_with_badge(): void
    {
        Sale::create([
            'transaction_number' => 'ONL101',
            'table_number' => 'ONLINE',
            'customer_name' => 'Dimas R',
            'customer_phone' => '085233344455',
            'user_id' => $this->cashier->id,
            'transaction_date' => now(),
            'subtotal' => 35000,
            'discount_amount' => 0,
            'total_amount' => 35000,
            'status' => 'completed',
            'order_status' => 'pending',
            'payment_status' => 'paid',
            'notes' => '[Pesanan Online]',
        ]);

        $response = $this->actingAs($this->kitchen)->get(route('kitchen.index'));

        $response->assertStatus(200);
        $response->assertSee('ONL101');
        $response->assertSee('PESANAN ONLINE');
        $response->assertSee('Dimas R');
    }
}

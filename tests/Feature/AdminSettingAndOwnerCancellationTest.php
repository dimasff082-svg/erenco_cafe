<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Salary;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSettingAndOwnerCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $owner;

    protected User $cashier;

    protected User $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->warehouse = User::factory()->create([
            'role' => 'warehouse',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_settings_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Toko & Pemesanan Online', false);
        $response->assertSee('082131117996');
    }

    public function test_non_admin_cannot_access_settings_page(): void
    {
        $this->actingAs($this->cashier)->get(route('admin.settings.index'))->assertStatus(403);
        $this->actingAs($this->warehouse)->get(route('admin.settings.index'))->assertStatus(403);
        $this->actingAs($this->owner)->get(route('admin.settings.index'))->assertStatus(403);
    }

    public function test_admin_can_update_settings_and_upload_logo(): void
    {
        Storage::fake('public');

        $logo = UploadedFile::fake()->image('fira-logo.png', 200, 200);

        $response = $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'store_name' => 'Fira Cake Premium Bakery',
            'store_phone' => '082131117996',
            'store_email' => 'admin@firacake.id',
            'store_address' => 'Jl. Boulevard Rubi II No. 99, Makassar',
            'store_hours' => '07:30 - 22:00 WITA',
            'online_welcome_title' => 'Selamat Datang di Toko Resmi Fira Cake!',
            'online_welcome_description' => 'Aneka roti dan kue bolu fresh oven setiap hari.',
            'attendance_max_distance' => '25',
            'order_notification_whatsapp' => '082131117996',
            'app_logo' => $logo,
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('Fira Cake Premium Bakery', Setting::get('store_name'));
        $this->assertEquals('082131117996', Setting::get('store_phone'));
        $this->assertEquals('Jl. Boulevard Rubi II No. 99, Makassar', Setting::get('store_address'));
        $this->assertEquals('Selamat Datang di Toko Resmi Fira Cake!', Setting::get('online_welcome_title'));
        $this->assertEquals('25', Setting::get('attendance_max_distance'));

        $savedLogo = Setting::get('app_logo');
        $this->assertNotNull($savedLogo);
        Storage::disk('public')->assertExists($savedLogo);
    }

    public function test_online_order_page_renders_dynamic_settings_cleanly(): void
    {
        Setting::set('store_phone', '082131117996');
        Setting::set('online_welcome_title', 'Selamat Datang di Online Store Fira Cake!');
        Setting::set('online_welcome_description', 'Pilihan bolu dan pastry terbaik.');

        $category = Category::factory()->create(['name' => 'Bolu Fresh', 'is_active' => true]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Bolu Gulung Cokelat',
            'selling_price' => 45000,
            'is_active' => true,
        ]);

        $response = $this->get(route('online_order.index'));

        $response->assertStatus(200);
        $response->assertSee('082131117996');
        $response->assertSee('Selamat Datang di Online Store Fira Cake!');
        $response->assertSee('Bolu Gulung Cokelat');
        $response->assertDontSee('Undefined variable');
    }

    public function test_owner_can_cancel_order_with_reason(): void
    {
        $sale = Sale::factory()->create([
            'user_id' => $this->cashier->id,
            'status' => 'completed',
            'order_status' => 'processing',
            'payment_status' => 'paid',
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.transactions.cancel', $sale->id), [
            'cancellation_reason' => 'Pelanggan membatalkan pesanan karena salah varian rasa',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $sale->refresh();
        $this->assertTrue($sale->isCancelled());
        $this->assertEquals('cancelled', $sale->status);
        $this->assertEquals('cancelled', $sale->order_status);
        $this->assertEquals('cancelled', $sale->payment_status);
        $this->assertEquals($this->owner->id, $sale->cancelled_by);
        $this->assertEquals('Pelanggan membatalkan pesanan karena salah varian rasa', $sale->cancellation_reason);
    }

    public function test_admin_can_cancel_order_with_reason(): void
    {
        $sale = Sale::factory()->create([
            'user_id' => $this->cashier->id,
            'status' => 'completed',
            'order_status' => 'processing',
            'payment_status' => 'paid',
            'total_amount' => 75000,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.transactions.cancel', $sale->id), [
            'cancellation_reason' => 'Kasir salah input pesanan',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $sale->refresh();
        $this->assertTrue($sale->isCancelled());
        $this->assertEquals('cancelled', $sale->status);
        $this->assertEquals('cancelled', $sale->order_status);
        $this->assertEquals('cancelled', $sale->payment_status);
        $this->assertEquals($this->admin->id, $sale->cancelled_by);
        $this->assertEquals('Kasir salah input pesanan', $sale->cancellation_reason);
    }

    public function test_unauthorized_roles_cannot_cancel_order(): void
    {
        $sale = Sale::factory()->create([
            'user_id' => $this->cashier->id,
            'status' => 'completed',
        ]);

        $this->actingAs($this->cashier)
            ->post(route('owner.transactions.cancel', $sale->id), [
                'cancellation_reason' => 'Kasir ingin membatalkan',
            ])
            ->assertStatus(403);

        $this->actingAs($this->cashier)
            ->post(route('admin.transactions.cancel', $sale->id), [
                'cancellation_reason' => 'Kasir ingin membatalkan',
            ])
            ->assertStatus(403);

        $this->actingAs($this->warehouse)
            ->post(route('admin.transactions.cancel', $sale->id), [
                'cancellation_reason' => 'Warehouse ingin membatalkan',
            ])
            ->assertStatus(403);
    }

    public function test_online_order_status_page_links_directly_to_admin_configured_store_phone(): void
    {
        Setting::set('store_phone', '082131117996');

        $sale = Sale::factory()->create([
            'transaction_number' => 'ONL005',
            'table_number' => 'ONLINE',
            'customer_name' => 'Dimas Customer',
            'customer_phone' => '085725890139',
            'status' => 'completed',
            'order_status' => 'ready',
            'payment_status' => 'paid',
        ]);

        $response = $this->get(route('online_order.status', 'ONL005'));

        $response->assertStatus(200);
        // Memastikan WhatsApp mengarah langsung ke nomor toko 6282131117996 (bukan tombol share tanpa parameter phone)
        $response->assertSee('https://api.whatsapp.com/send?phone=6282131117996', false);
        $response->assertSee('Hubungi Toko via WhatsApp');
    }

    public function test_attendance_page_and_submission_respects_dynamic_max_distance(): void
    {
        Storage::fake('public');

        // Set max distance 30 meter
        Setting::set('attendance_max_distance', '30');

        $response = $this->actingAs($this->cashier)->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('30 Meter');

        // Fake photo base64
        $fakeBase64 = 'data:image/png;base64,'.base64_encode('fake-image-binary-data');

        // Submit tepat di kantor (0 meter) -> Sukses
        $submit = $this->actingAs($this->cashier)->post(route('attendance.store'), [
            'type' => 'check_in',
            'latitude' => -5.155325,
            'longitude' => 119.441929,
            'photo_camera' => $fakeBase64,
            'notes' => 'Presensi tepat di lokasi kantor',
        ]);

        $submit->assertRedirect();
        $submit->assertSessionHas('success');

        // Ganti batas maksimal menjadi 5 meter
        Setting::set('attendance_max_distance', '5');

        // Jarak ~20 meter dari kantor (-5.15515, 119.441929) -> Ditolak karena > 5 meter
        $rejectedSubmit = $this->actingAs($this->warehouse)->post(route('attendance.store'), [
            'type' => 'check_in',
            'latitude' => -5.155150,
            'longitude' => 119.441929,
            'photo_camera' => $fakeBase64,
            'notes' => 'Presensi di luar 5 meter',
        ]);

        $rejectedSubmit->assertSessionHasErrors(['attendance']);
    }

    public function test_admin_and_owner_can_filter_transactions_by_status(): void
    {
        $paidSale = Sale::factory()->create([
            'transaction_number' => 'TRX-PAID-001',
            'user_id' => $this->cashier->id,
            'status' => 'completed',
            'order_status' => 'completed',
            'payment_status' => 'paid',
            'total_amount' => 50000,
        ]);

        $cancelledSale = Sale::factory()->create([
            'transaction_number' => 'TRX-CANCEL-001',
            'user_id' => $this->cashier->id,
            'status' => 'cancelled',
            'order_status' => 'cancelled',
            'payment_status' => 'cancelled',
            'cancelled_by' => $this->owner->id,
            'cancellation_reason' => 'Barang habis',
            'total_amount' => 80000,
        ]);

        // 1. Owner filter cancelled
        $responseOwnerCancelled = $this->actingAs($this->owner)->get(route('owner.transactions.index', ['status' => 'cancelled']));
        $responseOwnerCancelled->assertStatus(200);
        $responseOwnerCancelled->assertSee('TRX-CANCEL-001');
        $responseOwnerCancelled->assertDontSee('TRX-PAID-001');

        // 2. Owner filter paid
        $responseOwnerPaid = $this->actingAs($this->owner)->get(route('owner.transactions.index', ['status' => 'paid']));
        $responseOwnerPaid->assertStatus(200);
        $responseOwnerPaid->assertSee('TRX-PAID-001');
        $responseOwnerPaid->assertDontSee('TRX-CANCEL-001');

        // 3. Admin filter cancelled
        $responseAdminCancelled = $this->actingAs($this->admin)->get(route('admin.transactions.index', ['status' => 'cancelled']));
        $responseAdminCancelled->assertStatus(200);
        $responseAdminCancelled->assertSee('TRX-CANCEL-001');
        $responseAdminCancelled->assertDontSee('TRX-PAID-001');

        // 4. Admin filter paid
        $responseAdminPaid = $this->actingAs($this->admin)->get(route('admin.transactions.index', ['status' => 'paid']));
        $responseAdminPaid->assertStatus(200);
        $responseAdminPaid->assertSee('TRX-PAID-001');
        $responseAdminPaid->assertDontSee('TRX-CANCEL-001');
    }

    public function test_all_application_parts_reflect_dynamic_store_settings_changes(): void
    {
        // 1. Simpan perubahan identitas toko oleh Admin
        $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'store_name' => 'Liny Cafe & Bakery Special',
            'store_phone' => '081299998888',
            'store_email' => 'halo@linyresto.id',
            'store_address' => 'Jl. Boulevard Rubi No. 77, Panakkukang, Makassar',
            'store_hours' => '09:00 - 23:00 WITA',
            'online_welcome_title' => 'Selamat Datang di Liny Cafe!',
            'online_welcome_description' => 'Sajian pastry dan kopi terbaik fresh setiap hari.',
            'attendance_max_distance' => '20',
            'order_notification_whatsapp' => '081299998888',
        ])->assertRedirect(route('admin.settings.index'));

        // Pastikan Setting tersimpan
        $this->assertEquals('Liny Cafe & Bakery Special', Setting::get('store_name'));
        $this->assertEquals('081299998888', Setting::get('store_phone'));
        $this->assertEquals('Jl. Boulevard Rubi No. 77, Panakkukang, Makassar', Setting::get('store_address'));

        // A. Halaman Login (Guest)
        $this->post(route('logout'));
        $loginRes = $this->get(route('login'));
        $loginRes->assertStatus(200);
        $loginRes->assertSee('Liny Cafe & Bakery Special');
        $loginRes->assertSee('Jl. Boulevard Rubi No. 77, Panakkukang, Makassar');

        // B. Layout Utama Aplikasi (Admin Dashboard, Cashier Dashboard)
        $adminDash = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $adminDash->assertStatus(200);
        $adminDash->assertSee('Liny Cafe & Bakery Special');

        $cashierDash = $this->actingAs($this->cashier)->get(route('cashier.dashboard'));
        $cashierDash->assertStatus(200);
        $cashierDash->assertSee('Liny Cafe & Bakery Special');

        // C. Struk POS (pos.receipt)
        $posSale = Sale::factory()->create([
            'user_id' => $this->cashier->id,
            'table_number' => '05',
            'status' => 'completed',
            'total_amount' => 50000,
        ]);
        $posReceipt = $this->actingAs($this->cashier)->get(route('pos.receipt', $posSale->id));
        $posReceipt->assertStatus(200);
        $posReceipt->assertSee('Liny Cafe & Bakery Special');
        $posReceipt->assertSee('081299998888');
        $posReceipt->assertSee('Jl. Boulevard Rubi No. 77, Panakkukang, Makassar');
        $posReceipt->assertSee('09:00 - 23:00 WITA');

        // D. Kiosk Mandiri & Kiosk Receipt
        $kioskIndex = $this->get(route('kiosk.index'));
        $kioskIndex->assertStatus(200);
        $kioskIndex->assertSee('Liny Cafe & Bakery Special');

        $kioskSale = Sale::factory()->create([
            'user_id' => $this->cashier->id,
            'table_number' => 'KIOSK',
            'status' => 'completed',
            'total_amount' => 35000,
        ]);
        $kioskReceipt = $this->get(route('kiosk.receipt', $kioskSale->id));
        $kioskReceipt->assertStatus(200);
        $kioskReceipt->assertSee('Liny Cafe & Bakery Special');
        $kioskReceipt->assertSee('081299998888');
        $kioskReceipt->assertSee('Jl. Boulevard Rubi No. 77, Panakkukang, Makassar');

        // E. Pemesanan Mandiri Meja via QR Code & Status
        $table = RestaurantTable::create([
            'table_number' => 'M-99',
            'name' => 'Meja VIP 99',
            'capacity' => 4,
            'status' => 'available',
            'is_active' => true,
        ]);
        $tableOrder = $this->get(route('order.index', $table->table_number));
        $tableOrder->assertStatus(200);
        $tableOrder->assertSee('Liny Cafe & Bakery Special');

        $tableSale = Sale::factory()->create([
            'table_number' => $table->table_number,
            'status' => 'pending',
            'total_amount' => 25000,
        ]);
        $tableStatus = $this->get(route('order.status', [$table->table_number, $tableSale->id]));
        $tableStatus->assertStatus(200);
        $tableStatus->assertSee('Liny Cafe & Bakery Special');

        // F. Cetak QR Code Meja (admin.tables.print_qr)
        $printQr = $this->actingAs($this->admin)->get(route('admin.tables.print_qr', $table->id));
        $printQr->assertStatus(200);
        $printQr->assertSee('Liny Cafe & Bakery Special');

        // G. Slip Gaji Karyawan (print) & Index Slip
        $salary = Salary::factory()->create([
            'user_id' => $this->cashier->id,
            'basic_salary' => 3000000,
            'allowance' => 500000,
            'net_salary' => 3500000,
        ]);
        $salaryPrint = $this->actingAs($this->admin)->get(route('admin.salaries.print', $salary->id));
        $salaryPrint->assertStatus(200);
        $salaryPrint->assertSee('Liny Cafe & Bakery Special');
        $salaryPrint->assertSee('Jl. Boulevard Rubi No. 77, Panakkukang, Makassar');
        $salaryPrint->assertSee('081299998888');

        // H. Halaman Presensi Karyawan
        $attendanceRes = $this->actingAs($this->cashier)->get(route('attendance.index'));
        $attendanceRes->assertStatus(200);
        $attendanceRes->assertSee('Liny Cafe & Bakery Special');
        $attendanceRes->assertSee('Jl. Boulevard Rubi No. 77, Panakkukang, Makassar');

        // I. Halaman Pesan Online & Status Online
        $onlineIndex = $this->get(route('online_order.index'));
        $onlineIndex->assertStatus(200);
        $onlineIndex->assertSee('Liny Cafe & Bakery Special');
        $onlineIndex->assertSee('081299998888');
        $onlineIndex->assertSee('Jl. Boulevard Rubi No. 77, Panakkukang, Makassar');

        $onlineSale = Sale::factory()->create([
            'transaction_number' => 'ONL-TEST-99',
            'table_number' => 'ONLINE',
            'customer_name' => 'Budi Customer',
            'customer_phone' => '081234567890',
            'status' => 'completed',
            'order_status' => 'processing',
            'payment_status' => 'paid',
        ]);
        $onlineStatus = $this->get(route('online_order.status', 'ONL-TEST-99'));
        $onlineStatus->assertStatus(200);
        $onlineStatus->assertSee('Liny Cafe & Bakery Special');
        $onlineStatus->assertSee('6281299998888');
    }

    public function test_admin_can_remove_custom_logo_back_to_default(): void
    {
        Storage::fake('public');

        // 1. Upload logo baru
        $logo = UploadedFile::fake()->image('custom-logo.png', 100, 100);
        $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'store_name' => 'Liny Cafe',
            'store_phone' => '081299998888',
            'store_address' => 'Makassar',
            'store_hours' => '08:00 - 22:00',
            'online_welcome_title' => 'Selamat Datang',
            'online_welcome_description' => 'Deskripsi',
            'attendance_max_distance' => '15',
            'app_logo' => $logo,
        ]);

        $uploadedPath = Setting::get('app_logo');
        $this->assertNotNull($uploadedPath);
        Storage::disk('public')->assertExists($uploadedPath);

        // 2. Kirim remove_logo => 1
        $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'store_name' => 'Liny Cafe',
            'store_phone' => '081299998888',
            'store_address' => 'Makassar',
            'store_hours' => '08:00 - 22:00',
            'online_welcome_title' => 'Selamat Datang',
            'online_welcome_description' => 'Deskripsi',
            'attendance_max_distance' => '15',
            'remove_logo' => 1,
        ]);

        $this->assertNull(Setting::get('app_logo'));
        Storage::disk('public')->assertMissing($uploadedPath);
        $this->assertStringContainsString('images/logo.png', Setting::getLogoUrl());
    }

    public function test_store_name_two_lines_formatting_and_rendering(): void
    {
        // 1. Simpan nama toko dengan enter / newline (\n)
        $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'store_name' => "Liny Cafe\nResto & Bakery",
            'store_phone' => '081299998888',
            'store_address' => 'Makassar',
            'store_hours' => '08:00 - 22:00',
            'online_welcome_title' => 'Selamat Datang',
            'online_welcome_description' => 'Deskripsi Toko',
            'attendance_max_distance' => '15',
        ])->assertRedirect(route('admin.settings.index'));

        $this->assertEquals("Liny Cafe\nResto & Bakery", Setting::get('store_name'));
        $this->assertEquals('Liny Cafe Resto & Bakery', Setting::getStoreName(true));

        $lines = Setting::getStoreNameLines();
        $this->assertEquals('Liny Cafe', $lines[0]);
        $this->assertEquals('Resto & Bakery', $lines[1]);

        // 2. Sidebar pada layout menampilkan baris 1 dan baris 2
        $adminDash = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $adminDash->assertStatus(200);
        $adminDash->assertSee('Liny Cafe');
        $adminDash->assertSee('Resto & Bakery');

        // 3. Halaman Login menampilkan baris 1 dan baris 2
        $this->post(route('logout'));
        $loginRes = $this->get(route('login'));
        $loginRes->assertStatus(200);
        $loginRes->assertSee('Liny Cafe');
        $loginRes->assertSee('Resto & Bakery');

        // 4. Form Admin Settings menggunakan textarea untuk nama toko 2 baris
        $settingsView = $this->actingAs($this->admin)->get(route('admin.settings.index'));
        $settingsView->assertStatus(200);
        $settingsView->assertSee('<textarea name="store_name"', false);
        $settingsView->assertSee('Mendukung 2 Baris (Tekan Enter)');
    }
}

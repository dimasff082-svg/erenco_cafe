<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
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

    public function test_non_owner_cannot_cancel_order(): void
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

        $this->actingAs($this->admin)
            ->post(route('owner.transactions.cancel', $sale->id), [
                'cancellation_reason' => 'Admin ingin membatalkan',
            ])
            ->assertStatus(403);
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
}

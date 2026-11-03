<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Salary;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WarehouseAndOwnerNewFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Staf Warehouse dapat membuka halaman transaksi pengeluaran dan mencatat bahan baku serta operasional.
     */
    public function test_warehouse_staff_can_record_expenses(): void
    {
        $warehouseUser = User::factory()->warehouse()->create();

        // 1. Input pengeluaran pembelian bahan baku
        $response = $this->actingAs($warehouseUser)
            ->post(route('warehouse.expenses.store'), [
                'expense_type' => 'raw_material',
                'title' => 'Tepung Terigu Segitiga Biru 25kg',
                'category' => 'Bahan Roti',
                'amount' => 320000,
                'expense_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'notes' => 'Pembelian grosir 1 karung',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'expense_type' => 'raw_material',
            'title' => 'Tepung Terigu Segitiga Biru 25kg',
            'amount' => 320000,
            'user_id' => $warehouseUser->id,
        ]);

        // 2. Input pengeluaran operasional lainnya (listrik, air, telpon)
        $response2 = $this->actingAs($warehouseUser)
            ->post(route('warehouse.expenses.store'), [
                'expense_type' => 'operational',
                'title' => 'Tagihan Listrik PLN Bulan Ini',
                'category' => 'Listrik & Air',
                'amount' => 850000,
                'expense_date' => now()->toDateString(),
                'payment_method' => 'transfer',
            ]);

        $response2->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'expense_type' => 'operational',
            'title' => 'Tagihan Listrik PLN Bulan Ini',
            'amount' => 850000,
        ]);

        // 3. Tampilan halaman transaksi pengeluaran memuat kedua data
        $indexResponse = $this->actingAs($warehouseUser)->get(route('warehouse.expenses.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Tepung Terigu Segitiga Biru 25kg');
        $indexResponse->assertSee('Tagihan Listrik PLN Bulan Ini');
    }

    /**
     * Admin dan Pemilik dapat mengelola slip gaji karyawan, sedangkan Warehouse dilarang.
     */
    public function test_admin_and_owner_can_manage_salaries(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->owner()->create();
        $warehouseUser = User::factory()->warehouse()->create();

        // 1. Admin membuat slip gaji karyawan
        $createResponse = $this->actingAs($admin)->post(route('admin.salaries.store'), [
            'user_id' => $warehouseUser->id,
            'period_month' => 'November 2026',
            'basic_salary' => 3500000,
            'allowance' => 500000,
            'deductions' => 100000,
            'payment_date' => now()->toDateString(),
            'payment_status' => 'paid',
            'payment_method' => 'transfer',
            'notes' => 'Gaji bulanan reguler',
        ]);

        $createResponse->assertRedirect();
        $this->assertDatabaseHas('salaries', [
            'user_id' => $warehouseUser->id,
            'period_month' => 'November 2026',
            'basic_salary' => 3500000,
            'net_salary' => 3900000,
            'payment_status' => 'paid',
        ]);

        $salary = Salary::where('user_id', $warehouseUser->id)->first();

        // 2. Admin dapat melihat daftar dan cetak slip gaji
        $kitchenUser = User::factory()->kitchen()->create(['name' => 'Chef Dono']);
        $adminIndex = $this->actingAs($admin)->get(route('admin.salaries.index'));
        $adminIndex->assertOk();
        $adminIndex->assertSee('November 2026');
        $adminIndex->assertSee($warehouseUser->name);
        $adminIndex->assertDontSee('Chef Dono — DAPUR');

        // Admin mencoba membuat slip gaji untuk role kitchen -> Gagal validasi
        $kitchenSalaryResponse = $this->actingAs($admin)->post(route('admin.salaries.store'), [
            'user_id' => $kitchenUser->id,
            'period_month' => 'November 2026',
            'basic_salary' => 3000000,
            'payment_date' => now()->toDateString(),
            'payment_status' => 'paid',
            'payment_method' => 'transfer',
        ]);
        $kitchenSalaryResponse->assertSessionHasErrors('user_id');

        $printResponse = $this->actingAs($admin)->get(route('admin.salaries.print', $salary));
        $printResponse->assertOk();
        $printResponse->assertSee('SLIP PENGGAJIAN RESMI');
        $printResponse->assertSee('3.900.000');

        // 3. Pemilik dapat membuka halaman penggajian
        $ownerIndex = $this->actingAs($owner)->get(route('owner.salaries.index'));
        $ownerIndex->assertOk();
        $ownerIndex->assertSee('November 2026');

        // 4. Staf Warehouse dilarang mengakses halaman penggajian (403 Forbidden)
        $warehouseResponse = $this->actingAs($warehouseUser)->get(route('admin.salaries.index'));
        $warehouseResponse->assertForbidden();
    }

    /**
     * Kasir dan Staf Warehouse dapat membuka halaman presensi tanpa error TypeError.
     */
    public function test_cashier_and_warehouse_can_view_attendance_page_without_error(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $warehouseUser = User::factory()->warehouse()->create();

        // 1. Kasir membuka halaman presensi
        $cashierResponse = $this->actingAs($cashier)->get(route('attendance.index'));
        $cashierResponse->assertOk();
        $cashierResponse->assertSee('Presensi Kehadiran Karyawan');
        $cashierResponse->assertSee('Jadwal 4 Bagian Presensi Hari Ini');

        // 2. Staf Warehouse membuka halaman presensi
        $warehouseResponse = $this->actingAs($warehouseUser)->get(route('attendance.index'));
        $warehouseResponse->assertOk();
        $warehouseResponse->assertSee('Presensi Kehadiran Karyawan');
        $warehouseResponse->assertSee('Jadwal 4 Bagian Presensi Hari Ini');
    }

    /**
     * Absensi staf berhasil ketika berada dalam radius <= 50 meter dari kantor.
     */
    public function test_attendance_succeeds_when_within_50_meters(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $fakeCameraBase64 = 'data:image/jpeg;base64,'.base64_encode('fake-camera-selfie-data');

        // Koordinat tepat di kantor toko Fira Cake Ruko Ruby (-5.155325, 119.441929)
        $response = $this->actingAs($cashier)->post(route('attendance.store'), [
            'type' => 'check_in',
            'latitude' => -5.155325,
            'longitude' => 119.441929,
            'photo_camera' => $fakeCameraBase64,
            'notes' => 'Tepat waktu',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'user_id' => $cashier->id,
            'type' => 'check_in',
            'status' => 'present',
        ]);

        $attendance = Attendance::where('user_id', $cashier->id)->where('type', 'check_in')->first();
        $this->assertNotNull($attendance);
        $this->assertTrue($attendance->isWithinRadius());
        $this->assertLessThanOrEqual(50.0, (float) $attendance->distance_meters);
    }

    /**
     * Absensi ditolak jika jarak melebihi batas 50 meter dari kantor.
     */
    public function test_attendance_is_rejected_when_exceeding_50_meters(): void
    {
        $warehouseUser = User::factory()->warehouse()->create();
        $fakeCameraBase64 = 'data:image/jpeg;base64,'.base64_encode('fake-camera-selfie-data');

        // Koordinat jauh dari kantor (misal berjarak beberapa kilometer)
        $response = $this->actingAs($warehouseUser)->post(route('attendance.store'), [
            'type' => 'check_in',
            'latitude' => -5.200000,
            'longitude' => 119.500000,
            'photo_camera' => $fakeCameraBase64,
        ]);

        $response->assertSessionHasErrors('attendance');
        $this->assertDatabaseMissing('attendances', [
            'user_id' => $warehouseUser->id,
            'type' => 'check_in',
        ]);
    }

    /**
     * Admin dapat melihat rekapitulasi presensi seluruh karyawan.
     */
    public function test_admin_can_view_all_attendances(): void
    {
        $admin = User::factory()->admin()->create();
        $cashier = User::factory()->create(['role' => 'cashier']);

        Attendance::factory()->create([
            'user_id' => $cashier->id,
            'type' => 'check_in',
            'distance_meters' => 10.0,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendances.index'));
        $response->assertOk();
        $response->assertSee('Rekapitulasi Presensi Karyawan');
        $response->assertSee($cashier->name);
    }

    /**
     * Pemilik (Owner) dapat melihat Laporan Laba/Rugi.
     */
    public function test_owner_can_view_profit_loss_report(): void
    {
        $owner = User::factory()->owner()->create();

        // 1. Penjualan lunas (Pendapatan: 500.000)
        Sale::factory()->create([
            'total_amount' => 500000,
            'subtotal' => 500000,
            'discount_amount' => 0,
            'payment_status' => 'paid',
            'status' => 'completed',
            'transaction_date' => now(),
        ]);

        // 2. Pengeluaran bahan baku (100.000)
        Expense::factory()->create([
            'expense_type' => 'raw_material',
            'amount' => 100000,
            'expense_date' => now()->toDateString(),
        ]);

        // 3. Pengeluaran operasional (50.000)
        Expense::factory()->create([
            'expense_type' => 'operational',
            'amount' => 50000,
            'expense_date' => now()->toDateString(),
        ]);

        // Laba bersih harus 500.000 - 100.000 - 50.000 = 350.000
        $response = $this->actingAs($owner)->get(route('owner.profit_loss.index', ['period' => 'this_month']));
        $response->assertOk();
        $response->assertSee('Laporan Laba / Rugi');
        $response->assertSee('350.000');
    }

    /**
     * Khusus Pemilik (Owner) yang dapat membatalkan pesanan.
     */
    public function test_only_owner_can_cancel_orders(): void
    {
        $owner = User::factory()->owner()->create();
        $cashier = User::factory()->create(['role' => 'cashier']);

        $sale = Sale::factory()->create([
            'status' => 'completed',
            'order_status' => 'processing',
            'payment_status' => 'paid',
        ]);

        // Kasir mencoba membatalkan pesanan -> 403 Forbidden
        $cashierResponse = $this->actingAs($cashier)->post(route('owner.transactions.cancel', $sale), [
            'cancellation_reason' => 'Kasir ingin membatalkan',
        ]);
        $cashierResponse->assertForbidden();

        // Pemilik membatalkan pesanan -> Berhasil
        $ownerResponse = $this->actingAs($owner)->post(route('owner.transactions.cancel', $sale), [
            'cancellation_reason' => 'Kasir salah input jumlah pesanan roti',
        ]);

        $ownerResponse->assertRedirect();
        $sale->refresh();

        $this->assertTrue($sale->isCancelled());
        $this->assertSame('cancelled', $sale->status);
        $this->assertSame('cancelled', $sale->order_status);
        $this->assertSame('cancelled', $sale->payment_status);
        $this->assertSame($owner->id, $sale->cancelled_by);
        $this->assertSame('Kasir salah input jumlah pesanan roti', $sale->cancellation_reason);
        $this->assertNotNull($sale->cancelled_at);
    }

    /**
     * Staf Warehouse dan Kasir dapat melihat slip gaji milik sendiri dan mengunduh format PDF.
     */
    public function test_cashier_and_warehouse_can_view_own_salaries_and_download_pdf(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'name' => 'Kasir Toko']);
        $warehouse = User::factory()->warehouse()->create(['name' => 'Staf Gudang']);
        $admin = User::factory()->admin()->create();

        $cashierSalary = Salary::factory()->create([
            'user_id' => $cashier->id,
            'created_by' => $admin->id,
            'period_month' => 'Oktober 2026',
            'basic_salary' => 2500000,
            'net_salary' => 2500000,
            'payment_status' => 'paid',
        ]);

        $warehouseSalary = Salary::factory()->create([
            'user_id' => $warehouse->id,
            'created_by' => $admin->id,
            'period_month' => 'November 2026',
            'basic_salary' => 2800000,
            'net_salary' => 2800000,
            'payment_status' => 'paid',
        ]);

        // 1. Kasir mengakses daftar slip gajinya sendiri
        $cashierIndex = $this->actingAs($cashier)->get(route('my_salaries.index'));
        $cashierIndex->assertOk();
        $cashierIndex->assertSee($cashierSalary->payroll_number);
        $cashierIndex->assertSee('Oktober 2026');
        $cashierIndex->assertSee('Unduh PDF');
        $cashierIndex->assertDontSee($warehouseSalary->payroll_number);

        // 2. Warehouse mengakses daftar slip gajinya sendiri
        $warehouseIndex = $this->actingAs($warehouse)->get(route('my_salaries.index'));
        $warehouseIndex->assertOk();
        $warehouseIndex->assertSee($warehouseSalary->payroll_number);
        $warehouseIndex->assertSee('November 2026');
        $warehouseIndex->assertSee('Unduh PDF');
        $warehouseIndex->assertDontSee($cashierSalary->payroll_number);

        // 3. Kasir mencetak / mengunduh slip miliknya sendiri
        $cashierPrint = $this->actingAs($cashier)->get(route('my_salaries.print', $cashierSalary));
        $cashierPrint->assertOk();
        $cashierPrint->assertSee('SLIP PENGGAJIAN RESMI');
        $cashierPrint->assertSee('Unduh Format PDF');

        // 4. Kasir mencoba mengakses slip milik staf gudang -> 403 Forbidden
        $unauthorizedPrint = $this->actingAs($cashier)->get(route('my_salaries.print', $warehouseSalary));
        $unauthorizedPrint->assertForbidden();

        // 5. Staf gudang mencoba mengakses slip milik kasir -> 403 Forbidden
        $unauthorizedPrint2 = $this->actingAs($warehouse)->get(route('my_salaries.print', $cashierSalary));
        $unauthorizedPrint2->assertForbidden();
    }
}

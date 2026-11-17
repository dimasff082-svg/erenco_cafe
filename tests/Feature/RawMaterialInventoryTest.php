<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\RawMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RawMaterialInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_warehouse_staff_can_view_raw_materials_index_and_kpi(): void
    {
        $warehouseUser = User::factory()->warehouse()->create();

        RawMaterial::create([
            'code' => 'BHN-001',
            'name' => 'Biji Kopi Arabika',
            'category' => 'Biji Kopi',
            'unit' => 'gram',
            'current_stock' => 5000,
            'min_stock' => 1000,
            'unit_price' => 250,
        ]);

        RawMaterial::create([
            'code' => 'BHN-002',
            'name' => 'Fresh Milk',
            'category' => 'Susu & Dairy',
            'unit' => 'liter',
            'current_stock' => 2,
            'min_stock' => 5, // low stock
            'unit_price' => 20000,
        ]);

        $response = $this->actingAs($warehouseUser)
            ->get(route('warehouse.raw_materials.index'));

        $response->assertOk();
        $response->assertSee('Biji Kopi Arabika');
        $response->assertSee('Fresh Milk');
        $response->assertSee('BHN-001');
    }

    public function test_can_create_new_raw_material_with_auto_code_and_initial_stock(): void
    {
        $warehouseUser = User::factory()->warehouse()->create();

        $response = $this->actingAs($warehouseUser)
            ->post(route('warehouse.raw_materials.store'), [
                'name' => 'Sirup Karamel Premium',
                'category' => 'Sirup & Pemanis',
                'unit' => 'botol',
                'initial_stock' => 10,
                'min_stock' => 2,
                'unit_price' => 125000,
                'notes' => 'Botol 750ml',
            ]);

        $response->assertRedirect(route('warehouse.raw_materials.index'));
        $this->assertDatabaseHas('raw_materials', [
            'name' => 'Sirup Karamel Premium',
            'current_stock' => 10,
        ]);

        $material = RawMaterial::where('name', 'Sirup Karamel Premium')->first();
        $this->assertNotNull($material->code);
        $this->assertDatabaseHas('raw_material_mutations', [
            'raw_material_id' => $material->id,
            'type' => 'in',
            'quantity' => 10,
            'reference_number' => 'SALDO-AWAL',
        ]);
    }

    public function test_quick_restock_increments_stock_and_records_mutation(): void
    {
        $warehouseUser = User::factory()->warehouse()->create();

        $material = RawMaterial::create([
            'code' => 'BHN-001',
            'name' => 'Biji Kopi Arabika',
            'category' => 'Biji Kopi',
            'unit' => 'gram',
            'current_stock' => 1000,
            'min_stock' => 500,
            'unit_price' => 200,
        ]);

        $response = $this->actingAs($warehouseUser)
            ->post(route('warehouse.raw_materials.restock', $material->id), [
                'quantity' => 2500,
                'unit_price' => 220,
                'reference_number' => 'NOTA-001',
                'notes' => 'Restock biji kopi baru',
            ]);

        $response->assertRedirect(route('warehouse.raw_materials.index'));
        $material->refresh();

        $this->assertEquals(3500, (float) $material->current_stock);
        $this->assertEquals(220, (float) $material->unit_price);
        $this->assertDatabaseHas('raw_material_mutations', [
            'raw_material_id' => $material->id,
            'type' => 'in',
            'quantity' => 2500,
            'stock_before' => 1000,
            'stock_after' => 3500,
            'reference_number' => 'NOTA-001',
        ]);
    }

    public function test_usage_deducts_stock_and_prevents_negative_stock(): void
    {
        $warehouseUser = User::factory()->warehouse()->create();

        $material = RawMaterial::create([
            'code' => 'BHN-003',
            'name' => 'Fresh Milk',
            'category' => 'Susu & Dairy',
            'unit' => 'liter',
            'current_stock' => 10,
            'min_stock' => 2,
            'unit_price' => 20000,
        ]);

        // 1. Pemakaian wajar
        $response = $this->actingAs($warehouseUser)
            ->post(route('warehouse.raw_materials.usage', $material->id), [
                'quantity' => 3,
                'usage_type' => 'bar_usage',
                'notes' => 'Pemakaian bar pagi',
            ]);

        $response->assertRedirect(route('warehouse.raw_materials.index'));
        $material->refresh();
        $this->assertEquals(7, (float) $material->current_stock);

        // 2. Pemakaian melebihi sisa stok (harus ditolak dengan validation error)
        $invalidResponse = $this->actingAs($warehouseUser)
            ->post(route('warehouse.raw_materials.usage', $material->id), [
                'quantity' => 20, // sisa 7, mau ambil 20
                'usage_type' => 'bar_usage',
                'notes' => 'Ambil terlalu banyak',
            ]);

        $invalidResponse->assertSessionHasErrors('quantity');
        $material->refresh();
        $this->assertEquals(7, (float) $material->current_stock); // tetap 7
    }

    public function test_stock_opname_adjusts_physical_stock_and_records_difference(): void
    {
        $warehouseUser = User::factory()->warehouse()->create();

        $material = RawMaterial::create([
            'code' => 'BHN-005',
            'name' => 'Cup Sablon 16oz',
            'category' => 'Packaging',
            'unit' => 'pcs',
            'current_stock' => 100,
            'min_stock' => 20,
            'unit_price' => 600,
        ]);

        // Hitungan fisik ternyata 95 (ada 5 cup rusak/hilang)
        $response = $this->actingAs($warehouseUser)
            ->post(route('warehouse.raw_materials.opname', $material->id), [
                'actual_stock' => 95,
                'notes' => 'Audit opname akhir shift',
            ]);

        $response->assertRedirect(route('warehouse.raw_materials.index'));
        $material->refresh();

        $this->assertEquals(95, (float) $material->current_stock);
        $this->assertDatabaseHas('raw_material_mutations', [
            'raw_material_id' => $material->id,
            'type' => 'adjustment',
            'quantity' => -5,
            'stock_before' => 100,
            'stock_after' => 95,
        ]);
    }

    public function test_expense_with_raw_material_automatically_adds_stock_and_reverts_on_delete(): void
    {
        $warehouseUser = User::factory()->warehouse()->create();

        $material = RawMaterial::create([
            'code' => 'BHN-001',
            'name' => 'Biji Kopi Arabika',
            'category' => 'Biji Kopi',
            'unit' => 'gram',
            'current_stock' => 1000,
            'min_stock' => 500,
            'unit_price' => 200,
        ]);

        // 1. Simpan pengeluaran belanja bahan baku
        $response = $this->actingAs($warehouseUser)
            ->post(route('warehouse.expenses.store'), [
                'expense_type' => 'raw_material',
                'raw_material_id' => $material->id,
                'raw_material_quantity' => 5000,
                'title' => 'Pembelian Biji Kopi Arabika 5kg',
                'amount' => 1250000,
                'expense_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'notes' => 'Restock dari roastery',
            ]);

        $response->assertRedirect(route('warehouse.expenses.index'));
        $material->refresh();

        $this->assertEquals(6000, (float) $material->current_stock);
        $this->assertEquals(250, (float) $material->unit_price); // 1250000 / 5000 = 250

        $expense = Expense::where('raw_material_id', $material->id)->first();
        $this->assertNotNull($expense);

        // 2. Hapus pengeluaran karena salah input -> stok otomatis berkurang kembali (revert)
        $deleteResponse = $this->actingAs($warehouseUser)
            ->delete(route('warehouse.expenses.destroy', $expense->id));

        $deleteResponse->assertRedirect(route('warehouse.expenses.index'));
        $material->refresh();

        $this->assertEquals(1000, (float) $material->current_stock); // kembali ke 1000
    }

    public function test_can_view_global_mutations_and_item_history(): void
    {
        $warehouseUser = User::factory()->warehouse()->create();

        $material = RawMaterial::create([
            'code' => 'BHN-001',
            'name' => 'Biji Kopi Arabika',
            'category' => 'Biji Kopi',
            'unit' => 'gram',
            'current_stock' => 1000,
            'min_stock' => 500,
            'unit_price' => 200,
        ]);

        $material->recordMutation('in', 500, 'REF-1', 'Restock 1', $warehouseUser->id);

        $responseMutations = $this->actingAs($warehouseUser)
            ->get(route('warehouse.raw_materials.mutations'));
        $responseMutations->assertOk();
        $responseMutations->assertSee('Biji Kopi Arabika');

        $responseHistory = $this->actingAs($warehouseUser)
            ->get(route('warehouse.raw_materials.history', $material->id));
        $responseHistory->assertOk();
        $responseHistory->assertSee('Kartu Stok Fisik');
        $responseHistory->assertSee('REF-1');
    }
}

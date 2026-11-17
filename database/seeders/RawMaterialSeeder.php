<?php

namespace Database\Seeders;

use App\Models\RawMaterial;
use App\Models\RawMaterialMutation;
use App\Models\User;
use Illuminate\Database\Seeder;

class RawMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('role', 'admin')->first()
            ?? User::where('role', 'warehouse')->first()
            ?? User::first();

        $userId = $adminUser?->id;

        $materials = [
            [
                'code' => 'BHN-001',
                'name' => 'Biji Kopi Arabika House Blend',
                'category' => 'Biji Kopi',
                'unit' => 'gram',
                'current_stock' => 15000.00,
                'min_stock' => 3000.00,
                'unit_price' => 250.00,
                'notes' => 'Blend Arabika Gayo & Mandheling roast medium dark',
            ],
            [
                'code' => 'BHN-002',
                'name' => 'Biji Kopi Robusta Premium',
                'category' => 'Biji Kopi',
                'unit' => 'gram',
                'current_stock' => 10000.00,
                'min_stock' => 2000.00,
                'unit_price' => 120.00,
                'notes' => 'Robusta Dampit / Temanggung body tebal',
            ],
            [
                'code' => 'BHN-003',
                'name' => 'Fresh Milk Diamond / Greenfields',
                'category' => 'Susu & Dairy',
                'unit' => 'liter',
                'current_stock' => 45.00,
                'min_stock' => 12.00,
                'unit_price' => 21000.00,
                'notes' => 'Susu segar pasteurisasi untuk cappuccino & latte',
            ],
            [
                'code' => 'BHN-004',
                'name' => 'Susu Evaporasi F&N',
                'category' => 'Susu & Dairy',
                'unit' => 'kaleng',
                'current_stock' => 24.00,
                'min_stock' => 6.00,
                'unit_price' => 15500.00,
                'notes' => 'Campuran kopi susu aren & thai tea gurih',
            ],
            [
                'code' => 'BHN-005',
                'name' => 'Gula Aren Cair Organik',
                'category' => 'Sirup & Pemanis',
                'unit' => 'liter',
                'current_stock' => 18.00,
                'min_stock' => 5.00,
                'unit_price' => 38000.00,
                'notes' => 'Gula aren nira murni tanpa pengawet',
            ],
            [
                'code' => 'BHN-006',
                'name' => 'Sirup Karamel Monin / Toffin',
                'category' => 'Sirup & Pemanis',
                'unit' => 'botol',
                'current_stock' => 6.00,
                'min_stock' => 2.00,
                'unit_price' => 145000.00,
                'notes' => 'Botol 750ml untuk Caramel Macchiato',
            ],
            [
                'code' => 'BHN-007',
                'name' => 'Sirup Vanilla Toffin',
                'category' => 'Sirup & Pemanis',
                'unit' => 'botol',
                'current_stock' => 5.00,
                'min_stock' => 2.00,
                'unit_price' => 140000.00,
                'notes' => 'Botol 750ml untuk Vanilla Latte',
            ],
            [
                'code' => 'BHN-008',
                'name' => 'Matcha Green Tea Powder',
                'category' => 'Bubuk Minuman',
                'unit' => 'gram',
                'current_stock' => 2500.00,
                'min_stock' => 500.00,
                'unit_price' => 180.00,
                'notes' => 'Pure Japanese Uji Matcha powder tanpa gula',
            ],
            [
                'code' => 'BHN-009',
                'name' => 'Cup Plastik Sablon BY Coffee 16oz',
                'category' => 'Packaging & Cup',
                'unit' => 'pcs',
                'current_stock' => 850.00,
                'min_stock' => 200.00,
                'unit_price' => 650.00,
                'notes' => 'Cup ice takeaway logo resmi BY Coffee',
            ],
            [
                'code' => 'BHN-010',
                'name' => 'Paper Cup Hot 8oz',
                'category' => 'Packaging & Cup',
                'unit' => 'pcs',
                'current_stock' => 300.00,
                'min_stock' => 100.00,
                'unit_price' => 550.00,
                'notes' => 'Cup panas espresso / piccolo / hot latte',
            ],
            [
                'code' => 'BHN-011',
                'name' => 'Sedotan Pipih Steril Hitam',
                'category' => 'Packaging & Cup',
                'unit' => 'pcs',
                'current_stock' => 1200.00,
                'min_stock' => 300.00,
                'unit_price' => 120.00,
                'notes' => 'Sedotan steril bungkus kertas higienis',
            ],
            [
                'code' => 'BHN-012',
                'name' => 'Kantong Plastik Takeaway 1 Cup',
                'category' => 'Packaging & Cup',
                'unit' => 'pcs',
                'current_stock' => 500.00,
                'min_stock' => 150.00,
                'unit_price' => 200.00,
                'notes' => 'Kresek take-away bening ramah lingkungan',
            ],
        ];

        foreach ($materials as $data) {
            $initialStock = $data['current_stock'];

            $rawMaterial = RawMaterial::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'category' => $data['category'],
                    'unit' => $data['unit'],
                    'current_stock' => $initialStock,
                    'min_stock' => $data['min_stock'],
                    'unit_price' => $data['unit_price'],
                    'notes' => $data['notes'],
                ]
            );

            // Buat kartu stok perdana jika belum pernah dicatat
            if ($rawMaterial->mutations()->count() === 0) {
                RawMaterialMutation::create([
                    'raw_material_id' => $rawMaterial->id,
                    'user_id' => $userId,
                    'type' => 'in',
                    'quantity' => $initialStock,
                    'stock_before' => 0,
                    'stock_after' => $initialStock,
                    'reference_number' => 'SALDO-AWAL',
                    'notes' => 'Saldo awal stok fisik pembukaan operasional BY Coffee',
                ]);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@pos.test'],
            [
                'name' => 'Administrator Toko',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir@pos.test'],
            [
                'name' => 'Kasir Utama',
                'password' => bcrypt('password'),
                'role' => 'cashier',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'pemilik@pos.test'],
            [
                'name' => 'Pemilik Toko',
                'password' => bcrypt('password'),
                'role' => 'owner',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'dapur@pos.test'],
            [
                'name' => 'Chef Dapur Utama',
                'password' => bcrypt('password'),
                'role' => 'kitchen',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'warehouse@pos.test'],
            [
                'name' => 'Staf Gudang Utama',
                'password' => bcrypt('password'),
                'role' => 'warehouse',
                'is_active' => true,
            ]
        );

        // Kategori Awal
        $catFood = Category::firstOrCreate(['name' => 'Makanan & Snack'], ['description' => 'Aneka makanan ringan dan porsi utama', 'is_active' => true]);
        $catDrink = Category::firstOrCreate(['name' => 'Minuman Segar'], ['description' => 'Minuman kemasan dan racikan dingin', 'is_active' => true]);
        $catAtk = Category::firstOrCreate(['name' => 'Alat Tulis Kantor'], ['description' => 'Peralatan kantor dan sekolah', 'is_active' => true]);

        // Produk Awal
        $initialProducts = [
            [
                'category_id' => $catDrink->id,
                'sku' => 'MNM-001',
                'name' => 'Air Mineral Aqua 600ml',
                'description' => 'Air mineral pegunungan alami kemasan botol 600ml, segar dan higienis.',
                'unit' => 'botol',
                'selling_price' => 3500,
            ],
            [
                'category_id' => $catDrink->id,
                'sku' => 'MNM-002',
                'name' => 'Teh Botol Sosro 450ml',
                'description' => 'Teh melati manis khas Indonesia dalam kemasan botol praktis, disajikan dingin.',
                'unit' => 'botol',
                'selling_price' => 6000,
            ],
            [
                'category_id' => $catFood->id,
                'sku' => 'MKN-001',
                'name' => 'Indomie Goreng Original',
                'description' => 'Mie goreng legendaris dengan bumbu rempah spesial, taburan bawang goreng renyah dan telur mata sapi.',
                'unit' => 'porsi',
                'selling_price' => 12000,
            ],
            [
                'category_id' => $catFood->id,
                'sku' => 'MKN-002',
                'name' => 'Chitato Sapi Panggang 68g',
                'description' => 'Keripik kentang bergelombang rasa sapi panggang gurih yang renyah dan lezat.',
                'unit' => 'bungkus',
                'selling_price' => 11000,
            ],
            [
                'category_id' => $catAtk->id,
                'sku' => 'ATK-001',
                'name' => 'Buku Tulis Sinar Dunia 38 Lembar',
                'description' => 'Buku tulis bergaris ukuran standar 38 lembar dengan kertas berkualitas.',
                'unit' => 'buku',
                'selling_price' => 5000,
            ],
            [
                'category_id' => $catAtk->id,
                'sku' => 'ATK-002',
                'name' => 'Pulpen Standard AE7 Hitam',
                'description' => 'Pulpen tinta hitam pekat dengan mata pena halus 0.5mm, nyaman untuk menulis.',
                'unit' => 'pcs',
                'selling_price' => 3000,
            ],
        ];

        foreach ($initialProducts as $p) {
            Product::firstOrCreate(
                ['sku' => $p['sku']],
                [
                    'category_id' => $p['category_id'],
                    'name' => $p['name'],
                    'description' => $p['description'] ?? null,
                    'unit' => $p['unit'],
                    'selling_price' => $p['selling_price'],
                    'is_active' => true,
                ]
            );
        }

        $this->call(RestaurantTableSeeder::class);
    }
}

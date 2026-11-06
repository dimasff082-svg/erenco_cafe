<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Setting::DEFAULTS as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => str_contains($key, 'description') || str_contains($key, 'address') ? 'textarea' : 'text',
                    'group' => str_starts_with($key, 'online_') ? 'online_order' : 'store',
                ]
            );
        }
    }
}

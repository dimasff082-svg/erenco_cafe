<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderVoiceNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected User $kitchen;

    protected User $admin;

    protected User $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->kitchen = User::factory()->create([
            'role' => 'kitchen',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->warehouse = User::factory()->create([
            'role' => 'warehouse',
            'is_active' => true,
        ]);
    }

    public function test_cashier_can_check_new_orders_via_polling_endpoint(): void
    {
        $existing = Sale::factory()->create([
            'transaction_date' => today(),
            'table_number' => 'M001',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'total_amount' => 50000,
        ]);

        // Initial check with last_id = 0
        $initResponse = $this->actingAs($this->cashier)
            ->getJson(route('cashier.orders.check_new', ['last_id' => 0]));

        $initResponse->assertStatus(200)
            ->assertJson([
                'has_new' => false,
                'latest_id' => $existing->id,
            ]);

        // A new order arrives
        $newSale = Sale::factory()->create([
            'transaction_number' => 'TRX-NEW-01',
            'transaction_date' => today(),
            'table_number' => 'M002',
            'customer_name' => 'Budi Customer',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'total_amount' => 75000,
        ]);

        // Polling with last_id = existing->id
        $pollResponse = $this->actingAs($this->cashier)
            ->getJson(route('cashier.orders.check_new', ['last_id' => $existing->id]));

        $pollResponse->assertStatus(200)
            ->assertJson([
                'has_new' => true,
                'count' => 1,
                'latest_id' => $newSale->id,
            ])
            ->assertJsonPath('orders.0.transaction_number', 'TRX-NEW-01')
            ->assertJsonPath('orders.0.table_number', 'M002');
    }

    public function test_kitchen_can_check_new_orders_via_polling_endpoint(): void
    {
        $existing = Sale::factory()->create([
            'transaction_date' => today(),
            'table_number' => 'M001',
            'status' => 'completed',
            'order_status' => 'pending',
            'payment_status' => 'paid',
        ]);

        // Initial check
        $initResponse = $this->actingAs($this->kitchen)
            ->getJson(route('kitchen.orders.check_new', ['last_id' => 0]));

        $initResponse->assertStatus(200)
            ->assertJson([
                'has_new' => false,
                'latest_id' => $existing->id,
            ]);

        // A new paid order enters kitchen queue
        $kitchenSale = Sale::factory()->create([
            'transaction_number' => 'KDS-002',
            'transaction_date' => today(),
            'table_number' => 'M005',
            'status' => 'completed',
            'order_status' => 'pending',
            'payment_status' => 'paid',
        ]);

        $pollResponse = $this->actingAs($this->kitchen)
            ->getJson(route('kitchen.orders.check_new', ['last_id' => $existing->id]));

        $pollResponse->assertStatus(200)
            ->assertJson([
                'has_new' => true,
                'count' => 1,
                'latest_id' => $kitchenSale->id,
            ])
            ->assertJsonPath('orders.0.transaction_number', 'KDS-002');
    }

    public function test_unpaid_online_orders_do_not_trigger_sound_for_cashier_or_kitchen(): void
    {
        $existing = Sale::factory()->create([
            'transaction_date' => today(),
            'table_number' => 'M001',
            'payment_status' => 'paid',
        ]);

        // Online order created but unpaid
        Sale::factory()->create([
            'transaction_number' => 'ONL-PENDING',
            'table_number' => 'ONLINE',
            'status' => 'pending',
            'order_status' => 'pending',
            'payment_status' => 'unpaid',
            'transaction_date' => today(),
        ]);

        // Cashier check should be false
        $this->actingAs($this->cashier)
            ->getJson(route('cashier.orders.check_new', ['last_id' => $existing->id]))
            ->assertStatus(200)
            ->assertJson(['has_new' => false]);

        // Kitchen check should also be false
        $this->actingAs($this->kitchen)
            ->getJson(route('kitchen.orders.check_new', ['last_id' => $existing->id]))
            ->assertStatus(200)
            ->assertJson(['has_new' => false]);
    }

    public function test_unauthorized_user_cannot_access_order_check_endpoints(): void
    {
        $this->actingAs($this->warehouse)
            ->getJson(route('cashier.orders.check_new'))
            ->assertStatus(403);

        $this->actingAs($this->warehouse)
            ->getJson(route('kitchen.orders.check_new'))
            ->assertStatus(403);
    }

    public function test_dashboards_contain_sound_test_buttons(): void
    {
        // Cashier dashboard has sound button and speech synthesis caller
        $cashierRes = $this->actingAs($this->cashier)->get(route('cashier.dashboard'));
        $cashierRes->assertStatus(200);
        $cashierRes->assertSee('playOrderAlertSound');
        $cashierRes->assertSee('Halo ada orderan masuk');
        $cashierRes->assertSee('Tes Suara Orderan');

        // Kitchen display has sound button and speech synthesis caller
        $kitchenRes = $this->actingAs($this->kitchen)->get(route('kitchen.index'));
        $kitchenRes->assertStatus(200);
        $kitchenRes->assertSee('playOrderAlertSound');
        $kitchenRes->assertSee('Halo ada orderan masuk');
        $kitchenRes->assertSee('Tes Suara Orderan');
    }

    public function test_incoming_order_screens_contain_sound_disable_toggle_feature(): void
    {
        // 1. Kasir / Pesanan Masuk
        $cashierRes = $this->actingAs($this->cashier)->get(route('cashier.dashboard'));
        $cashierRes->assertStatus(200);
        $cashierRes->assertSee('btn-toggle-order-sound');
        $cashierRes->assertSee('toggleOrderSound');
        $cashierRes->assertSee('isOrderSoundEnabled');
        $cashierRes->assertSee('updateOrderSoundToggleUI');
        $cashierRes->assertSee('order_sound_enabled');

        // 2. Kitchen Display
        $kitchenRes = $this->actingAs($this->kitchen)->get(route('kitchen.index'));
        $kitchenRes->assertStatus(200);
        $kitchenRes->assertSee('btn-toggle-order-sound');
        $kitchenRes->assertSee('toggleOrderSound');
        $kitchenRes->assertSee('isOrderSoundEnabled');
        $kitchenRes->assertSee('updateOrderSoundToggleUI');
        $kitchenRes->assertSee('order_sound_enabled');
    }
}

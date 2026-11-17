<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationAndRoleRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_admin_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_cashier_is_redirected_to_cashier_dashboard(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'is_active' => true]);

        $response = $this->actingAs($cashier)->get('/');

        $response->assertRedirect(route('cashier.dashboard'));
    }

    public function test_warehouse_is_redirected_to_warehouse_inventory(): void
    {
        $warehouse = User::factory()->create(['role' => 'warehouse', 'is_active' => true]);

        $response = $this->actingAs($warehouse)->get('/');

        $response->assertRedirect(route('warehouse.raw_materials.index'));
    }

    public function test_unknown_role_is_safely_logged_out_and_redirected_to_login(): void
    {
        $unknown = User::factory()->create(['role' => 'pelayanan', 'is_active' => true]);

        $response = $this->actingAs($unknown)->get('/');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_logout_can_be_accessed_via_get_request(): void
    {
        $user = User::factory()->create(['role' => 'cashier', 'is_active' => true]);

        $response = $this->actingAs($user)->get('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}

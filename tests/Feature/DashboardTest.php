<?php

namespace Tests\Feature;

use App\Models\DeliveryOption;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\PlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_users_without_a_dashboard_role_are_forbidden()
    {
        $this->seedRoles();
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_owner_can_visit_the_dashboard_and_every_module()
    {
        $this->actingAs($this->userWithRole('owner'));
        // Real records in every list, so computed columns are exercised.
        $this->seed(PlatformSeeder::class);
        DeliveryOption::query()->update(['is_active' => true]);

        $this->get('/admin')->assertOk();
        foreach (['orders', 'books', 'bookings', 'services', 'availability', 'enquiries', 'invitations', 'subscribers', 'articles', 'videos', 'albums', 'events', 'awards', 'testimonials', 'profile-sections', 'pages', 'settings', 'users', 'audit-logs', 'delivery-options'] as $module) {
            $this->get("/admin/{$module}")->assertOk();
        }
        $this->get('/admin/books/create')->assertOk();
    }

    public function test_content_manager_cannot_reach_sales_or_settings()
    {
        $this->actingAs($this->userWithRole('content-manager'));

        $this->get('/admin/articles')->assertOk();
        $this->get('/admin/orders')->assertForbidden();
        $this->get('/admin/settings')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
    }

    public function test_operations_manager_cannot_record_refunds_or_manage_users()
    {
        $this->actingAs($this->userWithRole('operations-manager'));

        $this->get('/admin/orders')->assertOk();
        $order = Order::create(['reference' => 'YA-TEST', 'access_token' => 'x', 'customer_name' => 'A', 'email' => 'a@example.com', 'currency' => 'NGN', 'subtotal' => 100, 'total' => 100]);
        $this->post("/admin/orders/{$order->reference}/refunds", ['amount' => 1, 'reason' => 'x'])->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
    }
}

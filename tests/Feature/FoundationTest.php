<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    /**
     * Test application health API route.
     */
    public function test_api_health_endpoint(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'system' => 'StockManager Enterprise ERP API',
                'version' => 'v1',
            ]);
    }

    /**
     * Test UserRole Enum label resolution.
     */
    public function test_user_role_enum_labels(): void
    {
        $this->assertEquals('Super Administrator', UserRole::SUPER_ADMIN->label());
        $this->assertEquals('Administrator', UserRole::ADMIN->label());
        $this->assertEquals('Manager', UserRole::MANAGER->label());
        $this->assertEquals('Standard User', UserRole::USER->label());
    }

    /**
     * Test guest redirection to login page.
     */
    public function test_root_redirects_to_transport(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/transport');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\User;
use App\Models\AuditLog;
use App\Domain\Transport\TransportMasterManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase1DriverMasterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected TransportMasterManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['email' => 'admin@stockmanager.com']);
        $this->actingAs($this->user);
        $this->manager = app(TransportMasterManager::class);
    }

    /** @test */
    public function test_driver_id_generation_is_drv_000001_format()
    {
        $d1 = $this->manager->registerDriver([
            'driver_name' => 'Vikram Singh',
            'phone_number' => '+91 98765 11111',
            'license_class' => 'Heavy Commercial (HMV)',
            'driving_license_number' => 'MH-01-2022-000001',
            'license_expiry_date' => now()->addYears(3)->format('Y-m-d'),
        ], $this->user->id);

        $this->assertEquals('DRV-000001', $d1->driver_code);

        $d2 = $this->manager->registerDriver([
            'driver_name' => 'Amit Patel',
            'phone_number' => '+91 98765 22222',
            'license_class' => 'Light Motor Vehicle (LMV)',
            'driving_license_number' => 'MH-01-2022-000002',
            'license_expiry_date' => now()->addYears(3)->format('Y-m-d'),
        ], $this->user->id);

        $this->assertEquals('DRV-000002', $d2->driver_code);
    }

    /** @test */
    public function test_mobile_number_validation_and_uniqueness()
    {
        $this->manager->registerDriver([
            'driver_name' => 'Sunil Kumar',
            'phone_number' => '9876543210',
            'license_class' => 'HMV',
            'driving_license_number' => 'DL-12345',
            'license_expiry_date' => now()->addYear()->format('Y-m-d'),
        ], $this->user->id);

        $this->expectException(\InvalidArgumentException::class);
        
        // Attempt duplicate mobile registration
        $this->manager->registerDriver([
            'driver_name' => 'Other Driver',
            'phone_number' => '+91 98765 43210',
            'license_class' => 'HMV',
            'driving_license_number' => 'DL-99999',
            'license_expiry_date' => now()->addYear()->format('Y-m-d'),
        ], $this->user->id);
    }

    /** @test */
    public function test_license_number_uniqueness()
    {
        $this->manager->registerDriver([
            'driver_name' => 'Rajesh Sharma',
            'phone_number' => '9800011111',
            'license_class' => 'HMV',
            'driving_license_number' => 'MH-14-2020-0001',
            'license_expiry_date' => now()->addYear()->format('Y-m-d'),
        ], $this->user->id);

        $this->expectException(\InvalidArgumentException::class);

        // Attempt duplicate license registration
        $this->manager->registerDriver([
            'driver_name' => 'Karan Johar',
            'phone_number' => '9800022222',
            'license_class' => 'HMV',
            'driving_license_number' => 'MH-14-2020-0001',
            'license_expiry_date' => now()->addYear()->format('Y-m-d'),
        ], $this->user->id);
    }

    /** @test */
    public function test_driver_editing_preserves_immutable_driver_id()
    {
        $driver = $this->manager->registerDriver([
            'driver_name' => 'Original Name',
            'phone_number' => '9111122222',
            'license_class' => 'HMV',
            'driving_license_number' => 'DL-ORIG-01',
            'license_expiry_date' => now()->addYears(2)->format('Y-m-d'),
        ], $this->user->id);

        $code = $driver->driver_code;

        $updated = $this->manager->updateDriver($driver, [
            'driver_name' => 'Updated Name',
            'driver_code' => 'ATTEMPT_MODIFICATION_DRV-999999',
            'phone_number' => '9111122222',
            'driving_license_number' => 'DL-ORIG-01',
        ], $this->user->id);

        $this->assertEquals('Updated Name', $updated->driver_name);
        $this->assertEquals($code, $updated->driver_code); // Driver ID remains unchanged
    }

    /** @test */
    public function test_driver_suspension_requires_reason_and_logs_audit()
    {
        $driver = $this->manager->registerDriver([
            'driver_name' => 'Suspension Test',
            'phone_number' => '9222233333',
            'license_class' => 'HMV',
            'driving_license_number' => 'DL-SUSP-01',
            'license_expiry_date' => now()->addYears(2)->format('Y-m-d'),
        ], $this->user->id);

        $suspended = $this->manager->suspendDriver($driver, 'License compliance audit pending', $this->user->id);

        $this->assertEquals('suspended', $suspended->status);
        $this->assertEquals('License compliance audit pending', $suspended->suspension_reason);
        $this->assertEquals($this->user->id, $suspended->suspended_by);
        $this->assertNotNull($suspended->suspended_at);

        $audit = AuditLog::where('table_name', 'drivers')
            ->where('action', 'Driver Suspended')
            ->first();

        $this->assertNotNull($audit);
    }

    /** @test */
    public function test_driver_deactivation_is_non_destructive()
    {
        $driver = $this->manager->registerDriver([
            'driver_name' => 'Deactivation Test',
            'phone_number' => '9333344444',
            'license_class' => 'HMV',
            'driving_license_number' => 'DL-DEACT-01',
            'license_expiry_date' => now()->addYears(2)->format('Y-m-d'),
        ], $this->user->id);

        $deactivated = $this->manager->deactivateDriver($driver, $this->user->id);

        $this->assertEquals('inactive', $deactivated->status);
        $this->assertNotNull($deactivated->deactivated_at);
        $this->assertDatabaseHas('drivers', ['id' => $driver->id, 'status' => 'inactive']);
    }

    /** @test */
    public function test_driver_master_search_and_filters_via_controller(): void
    {
        $this->actingAs($this->user);
        $d1 = $this->manager->registerDriver([
            'driver_name' => 'Special Search Name',
            'phone_number' => '9444455555',
            'license_class' => 'HMV',
            'driving_license_number' => 'DL-SEARCH-01',
            'license_expiry_date' => now()->addYears(2)->format('Y-m-d'),
        ], $this->user->id);

        $response = $this->get(route('transport.drivers.index', [
            'tab' => 'drivers',
            'driver_search' => 'Special Search',
            'driver_status' => 'all',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Special Search Name');
        $response->assertSee($d1->driver_code);
    }
}

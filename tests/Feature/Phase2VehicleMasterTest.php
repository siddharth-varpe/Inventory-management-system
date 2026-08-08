<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use App\Models\User;
use App\Models\AuditLog;
use App\Domain\Transport\TransportMasterManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase2VehicleMasterTest extends TestCase
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
    public function test_vehicle_id_generation_is_veh_000001_format()
    {
        $v1 = $this->manager->registerVehicle([
            'vehicle_number' => 'MH-12-AB-1001',
            'vehicle_type' => 'Medium Commercial Vehicle',
            'manufacturer' => 'Tata Motors',
            'model' => 'Ultra T.14',
            'load_capacity_kg' => 7500,
            'fuel_type' => 'Diesel',
        ], $this->user->id);

        $this->assertEquals('VEH-000001', $v1->vehicle_code);

        $v2 = $this->manager->registerVehicle([
            'vehicle_number' => 'MH-12-AB-1002',
            'vehicle_type' => 'Light Commercial Vehicle',
            'manufacturer' => 'Mahindra',
            'model' => 'Bolero Maxi Truck',
            'load_capacity_kg' => 2500,
            'fuel_type' => 'Diesel',
        ], $this->user->id);

        $this->assertEquals('VEH-000002', $v2->vehicle_code);
    }

    /** @test */
    public function test_registration_number_normalization_and_uniqueness()
    {
        $this->manager->registerVehicle([
            'vehicle_number' => 'MH-12-AB-1234',
            'vehicle_type' => 'Mini Truck',
            'manufacturer' => 'Tata',
            'model' => 'Ace Gold',
            'load_capacity_kg' => 1500,
            'fuel_type' => 'Diesel',
        ], $this->user->id);

        $this->expectException(\InvalidArgumentException::class);

        // Attempt registering duplicate registration number
        $this->manager->registerVehicle([
            'vehicle_number' => 'MH12AB1234', // Normalized to MH12AB1234
            'vehicle_type' => 'Mini Truck',
            'manufacturer' => 'Tata',
            'model' => 'Ace Zip',
            'load_capacity_kg' => 1200,
            'fuel_type' => 'Diesel',
        ], $this->user->id);
    }

    /** @test */
    public function test_vehicle_editing_preserves_immutable_vehicle_id()
    {
        $vehicle = $this->manager->registerVehicle([
            'vehicle_number' => 'DL-01-XY-9999',
            'vehicle_type' => 'Heavy Commercial Vehicle',
            'manufacturer' => 'Ashok Leyland',
            'model' => 'AVTR 2820',
            'load_capacity_kg' => 18000,
            'fuel_type' => 'Diesel',
        ], $this->user->id);

        $originalCode = $vehicle->vehicle_code;

        $updated = $this->manager->updateVehicle($vehicle, [
            'vehicle_number' => 'DL-01-XY-9999',
            'vehicle_code' => 'ATTEMPT_MODIFICATION_VEH-999999',
            'vehicle_type' => 'Heavy Commercial Vehicle',
            'manufacturer' => 'Ashok Leyland',
            'model' => 'AVTR 2820 Gold',
            'load_capacity_kg' => 18500,
            'fuel_type' => 'Diesel',
        ], $this->user->id);

        $this->assertEquals('AVTR 2820 Gold', $updated->model);
        $this->assertEquals($originalCode, $updated->vehicle_code); // Vehicle ID immutable
    }

    /** @test */
    public function test_vehicle_maintenance_workflow()
    {
        $vehicle = $this->manager->registerVehicle([
            'vehicle_number' => 'KA-04-MN-5555',
            'vehicle_type' => 'Medium Commercial Vehicle',
            'manufacturer' => 'Eicher',
            'model' => 'Pro 2059',
            'load_capacity_kg' => 5000,
            'fuel_type' => 'Diesel',
        ], $this->user->id);

        // 1. Mark Maintenance
        $maintVehicle = $this->manager->markVehicleMaintenance($vehicle, [
            'maintenance_reason' => 'Scheduled 40,000 km gearbox overhaul',
            'maintenance_start_date' => now()->format('Y-m-d'),
            'maintenance_expected_completion' => now()->addDays(5)->format('Y-m-d'),
        ], $this->user->id);

        $this->assertEquals('maintenance', $maintVehicle->status);
        $this->assertFalse($maintVehicle->isAvailable());

        // 2. Return from Maintenance
        $recovered = $this->manager->returnVehicleFromMaintenance($maintVehicle, $this->user->id);
        $this->assertEquals('available', $recovered->status);
        $this->assertTrue($recovered->isAvailable());
    }

    /** @test */
    public function test_vehicle_breakdown_workflow()
    {
        $vehicle = $this->manager->registerVehicle([
            'vehicle_number' => 'GJ-01-BK-8888',
            'vehicle_type' => 'Mini Truck',
            'manufacturer' => 'Mahindra',
            'model' => 'Jeeto',
            'load_capacity_kg' => 1000,
            'fuel_type' => 'CNG',
        ], $this->user->id);

        // 1. Mark Breakdown
        $broken = $this->manager->markVehicleBreakdown($vehicle, [
            'breakdown_reason' => 'Clutch wire snapped on highway',
        ], $this->user->id);

        $this->assertEquals('breakdown', $broken->status);
        $this->assertFalse($broken->isAvailable());

        // 2. Recover Breakdown
        $recovered = $this->manager->recoverVehicleFromBreakdown($broken, $this->user->id);
        $this->assertEquals('available', $recovered->status);
        $this->assertTrue($recovered->isAvailable());
    }

    /** @test */
    public function test_vehicle_deactivation_is_non_destructive()
    {
        $vehicle = $this->manager->registerVehicle([
            'vehicle_number' => 'TS-09-EX-7777',
            'vehicle_type' => 'Van',
            'manufacturer' => 'Force',
            'model' => 'Traveller',
            'load_capacity_kg' => 3000,
            'fuel_type' => 'Diesel',
        ], $this->user->id);

        $deactivated = $this->manager->deactivateVehicle($vehicle, $this->user->id);

        $this->assertEquals('inactive', $deactivated->status);
        $this->assertNotNull($deactivated->deactivated_at);
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'status' => 'inactive']);
    }

    /** @test */
    public function test_document_compliance_status_calculation()
    {
        $v1 = $this->manager->registerVehicle([
            'vehicle_number' => 'MH-15-DC-0001',
            'vehicle_type' => 'Pickup',
            'manufacturer' => 'Tata',
            'model' => 'Yodha',
            'load_capacity_kg' => 2000,
            'fuel_type' => 'Diesel',
            'insurance_expiry_date' => now()->addYear()->format('Y-m-d'), // Valid
            'fitness_expiry_date' => now()->addDays(15)->format('Y-m-d'), // Expiring Soon
            'puc_expiry_date' => now()->subDays(5)->format('Y-m-d'), // Expired
        ], $this->user->id);

        $this->assertEquals('Valid', $v1->insurance_status);
        $this->assertEquals('Expiring Soon', $v1->fitness_status);
        $this->assertEquals('Expired', $v1->puc_status);
        $this->assertTrue($v1->hasExpiringOrExpiredDocuments());
    }

    /** @test */
    public function test_vehicle_master_search_and_filters_via_controller()
    {
        $v1 = $this->manager->registerVehicle([
            'vehicle_number' => 'MH-43-SEARCH-9',
            'vehicle_type' => 'Heavy Commercial Vehicle',
            'manufacturer' => 'BharatBenz',
            'model' => '1920C',
            'load_capacity_kg' => 16000,
            'fuel_type' => 'Diesel',
        ], $this->user->id);

        $response = $this->get(route('transport.vehicles.index', [
            'tab' => 'vehicles',
            'vehicle_search' => 'SEARCH-9',
            'vehicle_status' => 'all',
        ]));

        $response->assertStatus(200);
        $response->assertSee($v1->vehicle_number);
        $response->assertSee($v1->vehicle_code);
    }
}

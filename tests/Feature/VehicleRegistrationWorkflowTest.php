<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleRegistrationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['email' => 'admin@stockmanager.com']);
        $this->actingAs($this->user);
    }

    /** @test */
    public function test_vehicle_can_be_registered_successfully_via_http_form_submission(): void
    {
        $response = $this->post(route('transport.vehicles.store'), [
            'vehicle_number' => 'MH-12-AU-9988',
            'vehicle_type' => 'Heavy Commercial Vehicle',
            'manufacturer' => 'Tata Motors',
            'model' => 'Prima 3530.K',
            'fuel_type' => 'Diesel',
            'load_capacity_kg' => 12000,
            'volume_capacity_m3' => 40.0,
            'insurance_expiry_date' => date('Y-m-d', strtotime('+1 year')),
            'fitness_expiry_date' => date('Y-m-d', strtotime('+1 year')),
            'notes' => 'New flagship fleet vehicle',
        ]);

        $response->assertRedirect(route('transport.vehicles.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', [
            'vehicle_number' => 'MH12AU9988',
            'vehicle_type' => 'Heavy Commercial Vehicle',
            'manufacturer' => 'Tata Motors',
            'model' => 'Prima 3530.K',
            'fuel_type' => 'Diesel',
            'load_capacity_kg' => 12000.00,
            'status' => 'available',
        ]);

        $vehicle = Vehicle::where('vehicle_number', 'MH12AU9988')->first();
        $this->assertNotNull($vehicle);
        $this->assertStringStartsWith('VEH-', $vehicle->vehicle_code);
    }

    /** @test */
    public function test_vehicle_registration_validates_required_fields(): void
    {
        $response = $this->post(route('transport.vehicles.store'), []);

        $response->assertSessionHasErrors(['vehicle_number', 'vehicle_type', 'manufacturer', 'model', 'load_capacity_kg']);
    }

    /** @test */
    public function test_duplicate_vehicle_registration_number_is_rejected(): void
    {
        Vehicle::create([
            'vehicle_code' => 'VEH-000001',
            'vehicle_number' => 'MH12AB1001',
            'vehicle_type' => 'Heavy Truck',
            'manufacturer' => 'Tata',
            'model' => 'Prima',
            'load_capacity_kg' => 10000,
            'fuel_type' => 'Diesel',
            'status' => 'available',
        ]);

        $response = $this->post(route('transport.vehicles.store'), [
            'vehicle_number' => 'MH-12-AB-1001',
            'vehicle_type' => 'Heavy Truck',
            'manufacturer' => 'Tata',
            'model' => 'Prima',
            'load_capacity_kg' => 10000,
        ]);

        $response->assertSessionHas('error');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Vehicles Table
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_number')->unique();
            $table->string('vehicle_type');
            $table->decimal('load_capacity_kg', 10, 2)->default(5000.00);
            $table->decimal('volume_capacity_m3', 10, 2)->default(15.00);
            $table->string('current_location')->default('Central Warehouse Freight Yard');
            $table->string('status', 50)->default('available');
            $table->string('maintenance_status')->default('Good');
            $table->timestamps();
        });

        // 2. Drivers Table
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('driver_name');
            $table->string('employee_id')->unique();
            $table->string('license_class')->default('Heavy Commercial (HMV)');
            $table->string('phone_number')->nullable();
            $table->enum('status', ['available', 'on_trip', 'off_duty', 'unavailable'])->default('available');
            $table->string('current_assignment')->nullable()->default('Standby at Dispatch Bay');
            $table->timestamps();
        });

        // 3. Transport Trips Table
        Schema::create('transport_trips', function (Blueprint $table) {
            $table->id();
            $table->string('trip_number')->unique();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('restrict');
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('restrict');
            $table->timestamp('planned_departure_at')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->integer('total_package_count')->default(0);
            $table->decimal('total_weight_kg', 10, 2)->default(0.00);
            $table->decimal('total_volume_m3', 10, 2)->default(0.00);
            $table->string('destination_city')->nullable();
            $table->enum('status', ['created', 'ready', 'dispatched', 'completed', 'cancelled'])->default('created');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 4. Extend Transport Requests Table
        Schema::table('transport_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_requests', 'transport_trip_id')) {
                $table->foreignId('transport_trip_id')->nullable()->after('assigned_driver_id')->constrained('transport_trips')->onDelete('set null');
            }
            if (!Schema::hasColumn('transport_requests', 'volume_m3')) {
                $table->decimal('volume_m3', 10, 2)->default(0.50)->after('weight_kg');
            }
            if (!Schema::hasColumn('transport_requests', 'vehicle_id')) {
                $table->foreignId('vehicle_id')->nullable()->after('transport_trip_id')->constrained('vehicles')->onDelete('set null');
            }
            if (!Schema::hasColumn('transport_requests', 'driver_id')) {
                $table->foreignId('driver_id')->nullable()->after('vehicle_id')->constrained('drivers')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transport_requests', function (Blueprint $table) {
            if (Schema::hasColumn('transport_requests', 'transport_trip_id')) {
                $table->dropForeign(['transport_trip_id']);
                $table->dropColumn('transport_trip_id');
            }
            if (Schema::hasColumn('transport_requests', 'vehicle_id')) {
                $table->dropForeign(['vehicle_id']);
                $table->dropColumn('vehicle_id');
            }
            if (Schema::hasColumn('transport_requests', 'driver_id')) {
                $table->dropForeign(['driver_id']);
                $table->dropColumn('driver_id');
            }
            if (Schema::hasColumn('transport_requests', 'volume_m3')) {
                $table->dropColumn('volume_m3');
            }
        });

        Schema::dropIfExists('transport_trips');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('vehicles');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Extend Drivers Table for Enterprise Master Data
        Schema::table('drivers', function (Blueprint $table) {
            if (!Schema::hasColumn('drivers', 'driver_code')) {
                $table->string('driver_code')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('drivers', 'photo_url')) {
                $table->string('photo_url')->nullable()->after('driver_name');
            }
            if (!Schema::hasColumn('drivers', 'emergency_contact')) {
                $table->string('emergency_contact')->nullable()->after('phone_number');
            }
            if (!Schema::hasColumn('drivers', 'address')) {
                $table->text('address')->nullable()->after('emergency_contact');
            }
            if (!Schema::hasColumn('drivers', 'joining_date')) {
                $table->date('joining_date')->nullable()->after('address');
            }
            if (!Schema::hasColumn('drivers', 'driving_license_number')) {
                $table->string('driving_license_number')->nullable()->after('license_class');
            }
            if (!Schema::hasColumn('drivers', 'license_expiry_date')) {
                $table->date('license_expiry_date')->nullable()->after('driving_license_number');
            }
            if (!Schema::hasColumn('drivers', 'medical_certificate_date')) {
                $table->date('medical_certificate_date')->nullable()->after('license_expiry_date');
            }
            if (!Schema::hasColumn('drivers', 'medical_certificate_expiry')) {
                $table->date('medical_certificate_expiry')->nullable()->after('medical_certificate_date');
            }
            if (!Schema::hasColumn('drivers', 'performance_rating')) {
                $table->decimal('performance_rating', 3, 2)->default(5.00)->after('status');
            }
        });

        // 2. Extend Vehicles Table for Enterprise Master Data
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'vehicle_code')) {
                $table->string('vehicle_code')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('vehicles', 'manufacturer')) {
                $table->string('manufacturer')->nullable()->after('vehicle_type');
            }
            if (!Schema::hasColumn('vehicles', 'model')) {
                $table->string('model')->nullable()->after('manufacturer');
            }
            if (!Schema::hasColumn('vehicles', 'fuel_type')) {
                $table->string('fuel_type')->default('Diesel')->after('model');
            }
            if (!Schema::hasColumn('vehicles', 'purchase_date')) {
                $table->date('purchase_date')->nullable()->after('fuel_type');
            }
            if (!Schema::hasColumn('vehicles', 'insurance_policy_number')) {
                $table->string('insurance_policy_number')->nullable()->after('purchase_date');
            }
            if (!Schema::hasColumn('vehicles', 'insurance_expiry_date')) {
                $table->date('insurance_expiry_date')->nullable()->after('insurance_policy_number');
            }
            if (!Schema::hasColumn('vehicles', 'fitness_certificate_number')) {
                $table->string('fitness_certificate_number')->nullable()->after('insurance_expiry_date');
            }
            if (!Schema::hasColumn('vehicles', 'fitness_expiry_date')) {
                $table->date('fitness_expiry_date')->nullable()->after('fitness_certificate_number');
            }
            if (!Schema::hasColumn('vehicles', 'permit_number')) {
                $table->string('permit_number')->nullable()->after('fitness_expiry_date');
            }
            if (!Schema::hasColumn('vehicles', 'permit_expiry_date')) {
                $table->date('permit_expiry_date')->nullable()->after('permit_number');
            }
            if (!Schema::hasColumn('vehicles', 'rc_number')) {
                $table->string('rc_number')->nullable()->after('permit_expiry_date');
            }
            if (!Schema::hasColumn('vehicles', 'puc_expiry_date')) {
                $table->date('puc_expiry_date')->nullable()->after('rc_number');
            }
            if (!Schema::hasColumn('vehicles', 'current_odometer_km')) {
                $table->integer('current_odometer_km')->default(12000)->after('volume_capacity_m3');
            }
            if (!Schema::hasColumn('vehicles', 'last_service_date')) {
                $table->date('last_service_date')->nullable()->after('current_odometer_km');
            }
            if (!Schema::hasColumn('vehicles', 'next_service_due_date')) {
                $table->date('next_service_due_date')->nullable()->after('last_service_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $cols = ['driver_code', 'photo_url', 'emergency_contact', 'address', 'joining_date', 'driving_license_number', 'license_expiry_date', 'medical_certificate_date', 'medical_certificate_expiry', 'performance_rating'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('drivers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $cols = ['vehicle_code', 'manufacturer', 'model', 'fuel_type', 'purchase_date', 'insurance_policy_number', 'insurance_expiry_date', 'fitness_certificate_number', 'fitness_expiry_date', 'permit_number', 'permit_expiry_date', 'rc_number', 'puc_expiry_date', 'current_odometer_km', 'last_service_date', 'next_service_due_date'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('vehicles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

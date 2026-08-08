<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('driver_vehicle_assignments')) {
            Schema::create('driver_vehicle_assignments', function (Blueprint $table) {
                $table->id();
                $table->string('assignment_number')->unique()->index();
                $table->foreignId('transport_request_id')->constrained('transport_requests')->onDelete('cascade');
                $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->onDelete('set null');
                $table->string('enterprise_order_id')->index();
                $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
                $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
                $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('assigned_at')->useCurrent();
                $table->string('status')->default('active')->index(); // active, reassigned, released, cancelled
                $table->text('reassignment_reason')->nullable();
                $table->text('instructions')->nullable();
                $table->timestamps();

                $table->index(['transport_request_id', 'status']);
                $table->index(['driver_id', 'status']);
                $table->index(['vehicle_id', 'status']);
            });
        }

        if (!Schema::hasColumn('transport_requests', 'driver_vehicle_assignment_id')) {
            Schema::table('transport_requests', function (Blueprint $table) {
                $table->foreignId('driver_vehicle_assignment_id')->nullable()->after('transport_trip_id')->constrained('driver_vehicle_assignments')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('driver_notifications')) {
            Schema::create('driver_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
                $table->foreignId('assignment_id')->nullable()->constrained('driver_vehicle_assignments')->onDelete('set null');
                $table->string('title');
                $table->string('enterprise_order_id');
                $table->string('customer_name')->nullable();
                $table->text('delivery_address')->nullable();
                $table->string('destination_city')->nullable();
                $table->integer('package_count')->default(1);
                $table->string('priority')->default('normal');
                $table->date('required_delivery_date')->nullable();
                $table->string('vehicle_registration_number')->nullable();
                $table->timestamp('assignment_time')->useCurrent();
                $table->text('delivery_instructions')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();

                $table->index(['driver_id', 'is_read']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_notifications');
        
        if (Schema::hasColumn('transport_requests', 'driver_vehicle_assignment_id')) {
            Schema::table('transport_requests', function (Blueprint $table) {
                $table->dropForeign(['driver_vehicle_assignment_id']);
                $table->dropColumn('driver_vehicle_assignment_id');
            });
        }

        Schema::dropIfExists('driver_vehicle_assignments');
    }
};

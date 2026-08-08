<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
            $table->string('order_reference');
            $table->string('customer_name');
            $table->text('delivery_address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('priority')->default('medium');
            $table->date('expected_delivery_date')->nullable();
            $table->integer('package_count')->default(1);
            $table->string('package_type')->nullable();
            $table->decimal('weight_kg', 10, 2)->nullable();
            $table->enum('status', [
                'pending_packaging',
                'ready_for_dispatch',
                'vehicle_assigned',
                'loaded',
                'out_for_delivery',
                'delivered',
                'completed',
                'cancelled'
            ])->default('pending_packaging');
            $table->string('carrier')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('route_name')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_requests');
    }
};

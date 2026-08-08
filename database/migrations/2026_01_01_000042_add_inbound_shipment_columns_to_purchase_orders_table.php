<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('shipment_status')->default('pending_dispatch')->after('status'); // pending_dispatch, in_transit, arrived, completed
            $table->timestamp('dispatch_date')->nullable()->after('shipment_status');
            $table->timestamp('expected_delivery_date')->nullable()->after('dispatch_date');
            $table->timestamp('actual_arrival_date')->nullable()->after('expected_delivery_date');
            $table->string('carrier_name')->nullable()->after('actual_arrival_date');
            $table->string('tracking_reference')->nullable()->after('carrier_name');
            $table->string('vehicle_number')->nullable()->after('tracking_reference');
            $table->timestamp('received_at')->nullable()->after('vehicle_number');
            $table->foreignId('received_by')->nullable()->after('received_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['received_by']);
            $table->dropColumn([
                'shipment_status',
                'dispatch_date',
                'expected_delivery_date',
                'actual_arrival_date',
                'carrier_name',
                'tracking_reference',
                'vehicle_number',
                'received_at',
                'received_by',
            ]);
        });
    }
};

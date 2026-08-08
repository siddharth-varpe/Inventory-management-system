<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Delivery Timelines Table
        Schema::create('delivery_timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_request_id')->constrained('transport_requests')->onDelete('cascade');
            $table->foreignId('transport_trip_id')->nullable()->constrained('transport_trips')->onDelete('cascade');
            $table->string('event_type');
            $table->string('status');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('driver_name')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
        });

        // 2. Extend Transport Requests Table for Driver Execution
        Schema::table('transport_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_requests', 'driver_status')) {
                $table->string('driver_status')->default('dispatched')->after('status');
            }
            if (!Schema::hasColumn('transport_requests', 'delivery_notes')) {
                $table->text('delivery_notes')->nullable()->after('driver_status');
            }
            if (!Schema::hasColumn('transport_requests', 'delivery_confirmed_at')) {
                $table->timestamp('delivery_confirmed_at')->nullable()->after('delivered_at');
            }
            if (!Schema::hasColumn('transport_requests', 'delivery_failure_reason')) {
                $table->string('delivery_failure_reason')->nullable()->after('delivery_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transport_requests', function (Blueprint $table) {
            if (Schema::hasColumn('transport_requests', 'driver_status')) {
                $table->dropColumn('driver_status');
            }
            if (Schema::hasColumn('transport_requests', 'delivery_notes')) {
                $table->dropColumn('delivery_notes');
            }
            if (Schema::hasColumn('transport_requests', 'delivery_confirmed_at')) {
                $table->dropColumn('delivery_confirmed_at');
            }
            if (Schema::hasColumn('transport_requests', 'delivery_failure_reason')) {
                $table->dropColumn('delivery_failure_reason');
            }
        });

        Schema::dropIfExists('delivery_timelines');
    }
};

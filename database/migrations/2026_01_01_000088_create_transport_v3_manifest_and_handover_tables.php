<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Dispatch Manifests Table
        Schema::create('dispatch_manifests', function (Blueprint $table) {
            $table->id();
            $table->string('manifest_number')->unique();
            $table->foreignId('transport_trip_id')->constrained('transport_trips')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('restrict');
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('restrict');
            $table->integer('package_count')->default(1);
            $table->decimal('total_weight_kg', 10, 2)->default(0.00);
            $table->decimal('total_volume_m3', 10, 2)->default(0.50);
            $table->string('destination_summary')->nullable();
            $table->timestamp('dispatch_timestamp')->nullable();
            $table->json('checklist_result')->nullable();
            $table->timestamp('warehouse_completed_at')->nullable();
            $table->timestamp('transport_accepted_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('warehouse_supervisor_name')->default('Warehouse Operations Supervisor');
            $table->enum('status', ['created', 'locked', 'dispatched', 'completed'])->default('created');
            $table->timestamps();
        });

        // 2. Dispatch Checklists Table
        Schema::create('dispatch_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_request_id')->constrained('transport_requests')->onDelete('cascade');
            $table->foreignId('transport_trip_id')->nullable()->constrained('transport_trips')->onDelete('cascade');
            $table->boolean('vehicle_inspected')->default(false);
            $table->boolean('packages_loaded')->default(false);
            $table->boolean('package_count_verified')->default(false);
            $table->boolean('labels_verified')->default(false);
            $table->boolean('delivery_documents_verified')->default(false);
            $table->boolean('vehicle_doors_sealed')->default(false);
            $table->boolean('driver_documents_verified')->default(false);
            $table->boolean('loading_completed')->default(false);
            $table->boolean('supervisor_approved')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        // 3. Extend Transport Requests Table
        Schema::table('transport_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_requests', 'dispatch_manifest_id')) {
                $table->foreignId('dispatch_manifest_id')->nullable()->after('transport_trip_id')->constrained('dispatch_manifests')->onDelete('set null');
            }
            if (!Schema::hasColumn('transport_requests', 'accepted_by')) {
                $table->foreignId('accepted_by')->nullable()->after('dispatch_manifest_id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('transport_requests', 'accepted_at')) {
                $table->timestamp('accepted_at')->nullable()->after('accepted_by');
            }
            if (!Schema::hasColumn('transport_requests', 'acceptance_department')) {
                $table->string('acceptance_department')->default('Transport Department')->after('accepted_at');
            }
        });

        // 4. Extend Transport Trips Table
        Schema::table('transport_trips', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_trips', 'dispatch_manifest_id')) {
                $table->foreignId('dispatch_manifest_id')->nullable()->after('driver_id')->constrained('dispatch_manifests')->onDelete('set null');
            }
            if (!Schema::hasColumn('transport_trips', 'dispatched_at')) {
                $table->timestamp('dispatched_at')->nullable()->after('planned_departure_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transport_trips', function (Blueprint $table) {
            if (Schema::hasColumn('transport_trips', 'dispatch_manifest_id')) {
                $table->dropForeign(['dispatch_manifest_id']);
                $table->dropColumn('dispatch_manifest_id');
            }
            if (Schema::hasColumn('transport_trips', 'dispatched_at')) {
                $table->dropColumn('dispatched_at');
            }
        });

        Schema::table('transport_requests', function (Blueprint $table) {
            if (Schema::hasColumn('transport_requests', 'dispatch_manifest_id')) {
                $table->dropForeign(['dispatch_manifest_id']);
                $table->dropColumn('dispatch_manifest_id');
            }
            if (Schema::hasColumn('transport_requests', 'accepted_by')) {
                $table->dropForeign(['accepted_by']);
                $table->dropColumn('accepted_by');
            }
            if (Schema::hasColumn('transport_requests', 'accepted_at')) {
                $table->dropColumn('accepted_at');
            }
            if (Schema::hasColumn('transport_requests', 'acceptance_department')) {
                $table->dropColumn('acceptance_department');
            }
        });

        Schema::dropIfExists('dispatch_checklists');
        Schema::dropIfExists('dispatch_manifests');
    }
};

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
        // 1. Picking Tasks
        Schema::create('picking_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_number')->unique(); // PICK-2026-0001
            $table->string('order_reference');      // SO-10023
            $table->string('customer_name')->nullable();
            $table->string('picking_type')->default('single'); // single, batch, wave
            $table->string('priority')->default('medium');    // low, medium, high, urgent
            $table->boolean('is_fragile')->default(false);
            $table->boolean('is_cold_chain')->default(false);
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending');      // pending, assigned, picking, completed, cancelled
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 2. Picking Items Checklist
        Schema::create('picking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('picking_task_id')->constrained('picking_tasks')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('source_bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
            $table->string('location_coordinate')->nullable(); // WH01-A01-R03-S02-B04
            $table->integer('requested_quantity');
            $table->integer('picked_quantity')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        // 3. Dispatch Tasks (To integrate with Transport Portal)
        Schema::create('dispatch_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('dispatch_number')->unique(); // DISP-2026-0001
            $table->foreignId('picking_task_id')->constrained('picking_tasks')->cascadeOnDelete();
            $table->string('order_reference');
            $table->string('customer_name')->nullable();
            $table->string('delivery_address')->nullable();
            $table->integer('total_items');
            $table->decimal('total_weight_kg', 8, 2)->default(0);
            $table->string('shipping_label_code')->nullable();
            $table->string('status')->default('queued'); // queued, in_transit, delivered, cancelled
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatch_tasks');
        Schema::dropIfExists('picking_items');
        Schema::dropIfExists('picking_tasks');
    }
};

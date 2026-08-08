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
        // 1. Warehouse Transfers
        Schema::create('warehouse_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique(); // TRF-2026-0001
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('from_bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
            $table->string('from_coordinate')->nullable();
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('to_bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
            $table->string('to_coordinate')->nullable();
            $table->string('reason')->nullable();
            $table->string('status')->default('completed'); // pending, in_transit, completed, cancelled
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Warehouse Exceptions
        Schema::create('warehouse_exceptions', function (Blueprint $table) {
            $table->id();
            $table->string('exception_number')->unique(); // EXC-2026-0001
            $table->string('exception_type'); // short_pick, missing_item, damaged_item, wrong_location, quality_failure
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
            $table->foreignId('picking_task_id')->nullable()->constrained('picking_tasks')->nullOnDelete();
            $table->integer('affected_quantity')->default(1);
            $table->text('description')->nullable();
            $table->string('action_taken')->nullable(); // report_exception, request_cycle_count, create_writeoff, notify_inventory
            $table->string('status')->default('open'); // open, investigating, resolved, closed
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_exceptions');
        Schema::dropIfExists('warehouse_transfers');
    }
};

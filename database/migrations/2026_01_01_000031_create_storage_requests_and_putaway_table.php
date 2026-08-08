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
        Schema::create('storage_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); // e.g. STR-2026-0001
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('stock_receipt_id')->nullable()->constrained('stock_receipts')->nullOnDelete();
            $table->integer('quantity');
            $table->string('batch_number')->nullable();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('preferred_zone_id')->nullable()->constrained('warehouse_zones')->nullOnDelete();
            $table->foreignId('assigned_bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
            $table->string('assigned_coordinate')->nullable(); // e.g. Main Depot / Rack A-01 / Shelf 2 / Bin 04
            $table->string('priority')->default('medium');     // low, medium, high, urgent
            $table->string('status')->default('pending');        // pending, assigned, completed, cancelled
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_requests');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Sales Order Engine & Reservation Engine.
     */
    public function up(): void
    {
        // 1. Sales Orders
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('salesperson_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();

            $table->date('order_date');
            $table->date('expected_dispatch_date')->nullable();
            $table->string('order_priority')->default('normal'); // normal, high, urgent
            $table->string('status')->default('draft'); 
            // draft, pending_approval, credit_hold, approved, reserved, ready_for_dispatch, partially_dispatched, fully_dispatched, completed, closed, cancelled, rejected

            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('order_discount_amount', 12, 2)->default(0.00);
            $table->decimal('taxable_amount', 12, 2)->default(0.00);
            $table->decimal('cgst_amount', 12, 2)->default(0.00);
            $table->decimal('sgst_amount', 12, 2)->default(0.00);
            $table->decimal('igst_amount', 12, 2)->default(0.00);
            $table->decimal('grand_total', 12, 2)->default(0.00);

            $table->string('delivery_address')->nullable();
            $table->string('payment_terms')->nullable();
            $table->text('internal_notes')->nullable();
            $table->text('customer_notes')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('reserved_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        // 2. Sales Order Items
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            $table->integer('ordered_qty')->default(1);
            $table->integer('reserved_qty')->default(0);
            $table->integer('dispatched_qty')->default(0);
            $table->integer('backorder_qty')->default(0);

            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('taxable_value', 12, 2)->default(0.00);
            $table->decimal('gst_rate', 5, 2)->default(18.00);
            $table->decimal('gst_amount', 12, 2)->default(0.00);
            $table->decimal('line_total', 12, 2)->default(0.00);

            $table->timestamps();
        });

        // 3. Centralized Inventory Reservations
        Schema::create('inventory_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('reserved_qty')->default(0);
            $table->string('status')->default('active'); // active, released, fulfilled
            $table->timestamp('reserved_at');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });

        // 4. Backorders
        Schema::create('backorders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('requested_qty')->default(0);
            $table->integer('backordered_qty')->default(0);
            $table->string('status')->default('pending'); // pending, fulfilled, cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backorders');
        Schema::dropIfExists('inventory_reservations');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
    }
};

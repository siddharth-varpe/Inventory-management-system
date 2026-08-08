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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('qr_code')->nullable();
            
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->nullOnDelete();

            $table->string('product_type')->default('single'); // single, variant, batch, combo
            $table->string('status')->default('active'); // active, inactive, archived
            $table->text('description')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('image')->nullable();

            // Pricing Tiers
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('mrp', 12, 2)->default(0);
            $table->decimal('dealer_price', 12, 2)->default(0);
            $table->decimal('wholesale_price', 12, 2)->default(0);
            $table->decimal('min_selling_price', 12, 2)->default(0);

            // Inventory Tracking Flags & Locations
            $table->boolean('track_inventory')->default(true);
            $table->boolean('batch_tracking')->default(false);
            $table->boolean('serial_tracking')->default(false);
            $table->boolean('expiry_tracking')->default(false);

            $table->integer('min_stock')->default(5);
            $table->integer('max_stock')->default(1000);
            $table->integer('reorder_level')->default(10);
            $table->string('warehouse_location')->nullable();
            $table->string('rack_location')->nullable();
            $table->string('storage_condition')->nullable();

            // Physical Balances
            $table->integer('physical_stock')->default(0);
            $table->integer('reserved_stock')->default(0);
            $table->integer('available_stock')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

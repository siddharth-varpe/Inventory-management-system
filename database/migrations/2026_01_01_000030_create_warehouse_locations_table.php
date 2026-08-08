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
        // 1. Warehouse Zones
        Schema::create('warehouse_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('name'); // Receiving, Storage, Picking, Packing, Dispatch, Returns, Damaged
            $table->string('code'); // e.g. ZONE-RCV, ZONE-STR
            $table->string('type')->default('storage'); // receiving, storage, picking, packing, dispatch, returns, damaged
            $table->text('description')->nullable();
            $table->decimal('capacity', 10, 2)->default(1000);
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();

            $table->unique(['warehouse_id', 'code']);
        });

        // 2. Warehouse Aisles
        Schema::create('warehouse_aisles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_zone_id')->constrained('warehouse_zones')->cascadeOnDelete();
            $table->string('name'); // e.g. Aisle 01
            $table->string('code'); // e.g. A01
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['warehouse_zone_id', 'code']);
        });

        // 3. Warehouse Racks
        Schema::create('warehouse_racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_aisle_id')->constrained('warehouse_aisles')->cascadeOnDelete();
            $table->string('name'); // e.g. Rack 03
            $table->string('code'); // e.g. R03
            $table->string('rack_type')->default('standard'); // standard, heavy_duty, pallet_rack, cold_shelf
            $table->integer('total_shelves')->default(4);
            $table->decimal('capacity', 10, 2)->default(500);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['warehouse_aisle_id', 'code']);
        });

        // 4. Warehouse Bins (5-Tier Coordinate Leaf: WH01-A01-R03-S02-B04)
        Schema::create('warehouse_bins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_rack_id')->constrained('warehouse_racks')->cascadeOnDelete();
            $table->string('shelf_number')->default('S01'); // S01, S02, S03...
            $table->string('bin_number')->default('B01');   // B01, B02, B03...
            $table->string('location_code')->unique();       // WH01-A01-R03-S02-B04
            $table->string('barcode')->nullable()->unique();
            $table->decimal('max_weight', 10, 2)->default(100);
            $table->decimal('max_volume', 10, 2)->default(50);
            $table->integer('current_occupied_qty')->default(0);
            $table->string('status')->default('active');     // active, blocked, full
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_bins');
        Schema::dropIfExists('warehouse_racks');
        Schema::dropIfExists('warehouse_aisles');
        Schema::dropIfExists('warehouse_zones');
    }
};

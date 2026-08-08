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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('type'); // damaged, expired, lost, audit_difference, transfer_correction
            $table->integer('quantity');
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('approved'); // pending, approved, rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};

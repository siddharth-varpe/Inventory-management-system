<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add conversion linkage fields to quotations.
     */
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('sales_order_id')->nullable()->after('status')->constrained('sales_orders')->nullOnDelete();
            $table->timestamp('converted_at')->nullable()->after('sales_order_id');
            $table->foreignId('converted_by')->nullable()->after('converted_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['sales_order_id']);
            $table->dropForeign(['converted_by']);
            $table->dropColumn(['sales_order_id', 'converted_at', 'converted_by']);
        });
    }
};

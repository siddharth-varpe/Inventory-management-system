<?php

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
        Schema::table('transport_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_requests', 'source_module')) {
                $table->string('source_module')->default('CRM Sales Order')->after('warehouse_completed_at');
            }
            if (!Schema::hasColumn('transport_requests', 'warehouse_status')) {
                $table->string('warehouse_status')->default('picking_in_progress')->after('source_module');
            }
        });

        // Add indices for search and filtering performance
        Schema::table('transport_requests', function (Blueprint $table) {
            try {
                $table->index(['status', 'created_at'], 'idx_trn_status_created');
            } catch (\Throwable $e) {}
            try {
                $table->index(['order_reference'], 'idx_trn_order_ref');
            } catch (\Throwable $e) {}
            try {
                $table->index(['priority'], 'idx_trn_priority');
            } catch (\Throwable $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_requests', function (Blueprint $table) {
            if (Schema::hasColumn('transport_requests', 'warehouse_status')) {
                $table->dropColumn('warehouse_status');
            }
        });
    }
};

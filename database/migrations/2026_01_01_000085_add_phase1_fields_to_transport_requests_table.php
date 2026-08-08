<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_requests', 'delivery_city')) {
                $table->string('delivery_city')->nullable()->after('delivery_address');
            }
            if (!Schema::hasColumn('transport_requests', 'dimensions')) {
                $table->string('dimensions')->nullable()->after('weight_kg');
            }
            if (!Schema::hasColumn('transport_requests', 'warehouse_completed_at')) {
                $table->timestamp('warehouse_completed_at')->nullable()->after('weight_kg');
            }
            if (!Schema::hasColumn('transport_requests', 'required_dispatch_date')) {
                $table->date('required_dispatch_date')->nullable()->after('expected_delivery_date');
            }
            if (!Schema::hasColumn('transport_requests', 'source_module')) {
                $table->string('source_module')->default('Pick & Pack Station')->after('created_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transport_requests', function (Blueprint $table) {
            if (Schema::hasColumn('transport_requests', 'delivery_city')) {
                $table->dropColumn('delivery_city');
            }
            if (Schema::hasColumn('transport_requests', 'dimensions')) {
                $table->dropColumn('dimensions');
            }
            if (Schema::hasColumn('transport_requests', 'warehouse_completed_at')) {
                $table->dropColumn('warehouse_completed_at');
            }
            if (Schema::hasColumn('transport_requests', 'required_dispatch_date')) {
                $table->dropColumn('required_dispatch_date');
            }
            if (Schema::hasColumn('transport_requests', 'source_module')) {
                $table->dropColumn('source_module');
            }
        });
    }
};

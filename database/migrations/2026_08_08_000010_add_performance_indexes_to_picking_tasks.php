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
        Schema::table('picking_tasks', function (Blueprint $table) {
            $table->index(['status', 'priority', 'created_at'], 'idx_picking_tasks_status_priority_created');
            $table->index(['order_reference'], 'idx_picking_tasks_order_ref');
            $table->index(['customer_name'], 'idx_picking_tasks_customer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('picking_tasks', function (Blueprint $table) {
            $table->dropIndex('idx_picking_tasks_status_priority_created');
            $table->dropIndex('idx_picking_tasks_order_ref');
            $table->dropIndex('idx_picking_tasks_customer');
        });
    }
};

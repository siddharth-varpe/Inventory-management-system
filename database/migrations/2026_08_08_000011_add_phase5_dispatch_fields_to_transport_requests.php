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
        Schema::table('transport_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_requests', 'dispatch_number')) {
                $table->string('dispatch_number')->nullable()->after('request_number');
            }
            if (!Schema::hasColumn('transport_requests', 'dispatched_by')) {
                $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete()->after('dispatched_at');
            }
            if (!Schema::hasColumn('transport_requests', 'dispatch_notes')) {
                $table->text('dispatch_notes')->nullable()->after('delivery_notes');
            }
        });

        Schema::table('transport_requests', function (Blueprint $table) {
            try {
                $table->index(['dispatch_number'], 'idx_trn_dispatch_number');
            } catch (\Throwable $e) {}
            try {
                $table->index(['dispatched_at'], 'idx_trn_dispatched_at');
            } catch (\Throwable $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_requests', function (Blueprint $table) {
            if (Schema::hasColumn('transport_requests', 'dispatch_number')) {
                $table->dropColumn('dispatch_number');
            }
            if (Schema::hasColumn('transport_requests', 'dispatched_by')) {
                $table->dropForeign(['dispatched_by']);
                $table->dropColumn('dispatched_by');
            }
            if (Schema::hasColumn('transport_requests', 'dispatch_notes')) {
                $table->dropColumn('dispatch_notes');
            }
        });
    }
};

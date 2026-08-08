<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'manufacturing_year')) {
                $table->integer('manufacturing_year')->nullable()->after('model');
            }
            if (!Schema::hasColumn('vehicles', 'color')) {
                $table->string('color')->nullable()->after('manufacturing_year');
            }
            if (!Schema::hasColumn('vehicles', 'notes')) {
                $table->text('notes')->nullable()->after('maintenance_status');
            }
            if (!Schema::hasColumn('vehicles', 'maintenance_reason')) {
                $table->text('maintenance_reason')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('vehicles', 'maintenance_start_date')) {
                $table->date('maintenance_start_date')->nullable()->after('maintenance_reason');
            }
            if (!Schema::hasColumn('vehicles', 'maintenance_expected_completion')) {
                $table->date('maintenance_expected_completion')->nullable()->after('maintenance_start_date');
            }
            if (!Schema::hasColumn('vehicles', 'maintenance_notes')) {
                $table->text('maintenance_notes')->nullable()->after('maintenance_expected_completion');
            }
            if (!Schema::hasColumn('vehicles', 'breakdown_reason')) {
                $table->text('breakdown_reason')->nullable()->after('maintenance_notes');
            }
            if (!Schema::hasColumn('vehicles', 'breakdown_at')) {
                $table->timestamp('breakdown_at')->nullable()->after('breakdown_reason');
            }
            if (!Schema::hasColumn('vehicles', 'breakdown_notes')) {
                $table->text('breakdown_notes')->nullable()->after('breakdown_at');
            }
            if (!Schema::hasColumn('vehicles', 'deactivated_at')) {
                $table->timestamp('deactivated_at')->nullable()->after('breakdown_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $cols = [
                'manufacturing_year', 'color', 'notes', 'maintenance_reason',
                'maintenance_start_date', 'maintenance_expected_completion',
                'maintenance_notes', 'breakdown_reason', 'breakdown_at',
                'breakdown_notes', 'deactivated_at'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('vehicles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

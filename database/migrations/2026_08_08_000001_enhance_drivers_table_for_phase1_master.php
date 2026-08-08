<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            if (!Schema::hasColumn('drivers', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('phone_number');
            }
            if (!Schema::hasColumn('drivers', 'email')) {
                $table->string('email')->nullable()->after('phone_number');
            }
            if (!Schema::hasColumn('drivers', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('emergency_contact');
            }
            if (!Schema::hasColumn('drivers', 'emergency_contact_number')) {
                $table->string('emergency_contact_number')->nullable()->after('emergency_contact_name');
            }
            if (!Schema::hasColumn('drivers', 'notes')) {
                $table->text('notes')->nullable()->after('performance_rating');
            }
            if (!Schema::hasColumn('drivers', 'suspended_by')) {
                $table->foreignId('suspended_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('drivers', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable()->after('suspended_by');
            }
            if (!Schema::hasColumn('drivers', 'suspension_reason')) {
                $table->text('suspension_reason')->nullable()->after('suspended_at');
            }
            if (!Schema::hasColumn('drivers', 'deactivated_at')) {
                $table->timestamp('deactivated_at')->nullable()->after('suspension_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $cols = [
                'date_of_birth', 'email', 'emergency_contact_name', 
                'emergency_contact_number', 'notes', 'suspended_by', 
                'suspended_at', 'suspension_reason', 'deactivated_at'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('drivers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

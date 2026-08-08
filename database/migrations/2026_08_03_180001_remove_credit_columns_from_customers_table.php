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
        Schema::table('customers', function (Blueprint $table) {
            $columnsToDrop = [];
            
            if (Schema::hasColumn('customers', 'credit_limit')) {
                $columnsToDrop[] = 'credit_limit';
            }
            if (Schema::hasColumn('customers', 'credit_days')) {
                $columnsToDrop[] = 'credit_days';
            }
            if (Schema::hasColumn('customers', 'available_credit')) {
                $columnsToDrop[] = 'available_credit';
            }
            if (Schema::hasColumn('customers', 'credit_hold')) {
                $columnsToDrop[] = 'credit_hold';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('credit_limit', 12, 2)->default(0.00);
            $table->integer('credit_days')->default(30);
            $table->decimal('available_credit', 12, 2)->default(0.00);
            $table->boolean('credit_hold')->default(false);
        });
    }
};

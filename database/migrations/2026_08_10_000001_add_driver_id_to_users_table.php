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
        if (!Schema::hasColumn('users', 'driver_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('driver_id')->nullable()->after('department_id');
                $table->foreign('driver_id')->references('id')->on('drivers')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'driver_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('driver_id');
            });
        }
    }
};

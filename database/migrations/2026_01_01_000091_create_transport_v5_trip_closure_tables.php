<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_trips', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_trips', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('dispatched_at');
            }
            if (!Schema::hasColumn('transport_trips', 'closed_by')) {
                $table->foreignId('closed_by')->nullable()->after('closed_at')->constrained('users')->onDelete('set null');
            }
        });

        Schema::table('dispatch_manifests', function (Blueprint $table) {
            if (!Schema::hasColumn('dispatch_manifests', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('dispatch_timestamp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transport_trips', function (Blueprint $table) {
            if (Schema::hasColumn('transport_trips', 'closed_at')) {
                $table->dropColumn('closed_at');
            }
            if (Schema::hasColumn('transport_trips', 'closed_by')) {
                $table->dropForeign(['closed_by']);
                $table->dropColumn('closed_by');
            }
        });

        Schema::table('dispatch_manifests', function (Blueprint $table) {
            if (Schema::hasColumn('dispatch_manifests', 'closed_at')) {
                $table->dropColumn('closed_at');
            }
        });
    }
};

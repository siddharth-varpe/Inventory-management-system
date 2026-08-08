<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communication_records', function (Blueprint $table) {
            $table->timestamp('last_sent_at')->nullable()->after('retry_counter');
            $table->timestamp('last_delivered_at')->nullable()->after('last_sent_at');
            $table->timestamp('last_viewed_at')->nullable()->after('last_delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('communication_records', function (Blueprint $table) {
            $table->dropColumn(['last_sent_at', 'last_delivered_at', 'last_viewed_at']);
        });
    }
};

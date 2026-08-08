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
        Schema::create('workspace_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. inventory_operator, warehouse_supervisor, ceo_executive, admin
            $table->string('name');
            $table->string('role_name')->default('admin');
            $table->string('layout_type')->default('manager'); // operator, manager, supervisor, executive, admin
            $table->json('sidebar_config')->nullable();
            $table->json('dashboard_config')->nullable();
            $table->json('quick_actions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_profiles');
    }
};

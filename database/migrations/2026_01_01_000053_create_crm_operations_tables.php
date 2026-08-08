<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for CRM Operations & Universal Workflow Timeline support.
     */
    public function up(): void
    {
        // 1. Leads Master
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_number')->unique();
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->string('source')->default('website'); // website, referral, exhibition, cold_call
            $table->string('industry')->nullable();
            $table->decimal('expected_revenue', 12, 2)->default(0.00);
            $table->integer('probability')->default(50); // percentage 0-100

            $table->foreignId('salesperson_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('territory_id')->nullable()->constrained('territories')->nullOnDelete();

            $table->string('priority')->default('medium'); // low, medium, high
            $table->string('status')->default('new'); // new, contacted, qualified, proposal, negotiation, won, lost

            $table->date('expected_closing_date')->nullable();
            $table->text('remarks')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        // 2. CRM Activities (Calls, Meetings, Notes, Demos, Emails)
        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('crm_leads')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            $table->string('activity_type'); // call, email, whatsapp, meeting, site_visit, demo, note
            $table->string('subject');
            $table->text('description')->nullable();
            $table->timestamp('activity_date');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // 3. CRM Follow-up Engine
        Schema::create('crm_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('crm_leads')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            $table->string('title');
            $table->timestamp('due_date');
            $table->string('priority')->default('medium'); // low, medium, high
            $table->foreignId('assigned_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. CRM Meetings & Schedule
        Schema::create('crm_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('crm_leads')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            $table->string('title');
            $table->timestamp('meeting_date');
            $table->string('location')->nullable();
            $table->string('meeting_type')->default('in_person'); // in_person, online, site_visit
            $table->text('agenda')->nullable();
            $table->text('outcome')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_meetings');
        Schema::dropIfExists('crm_followups');
        Schema::dropIfExists('crm_activities');
        Schema::dropIfExists('crm_leads');
    }
};

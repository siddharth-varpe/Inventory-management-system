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
        Schema::create('communication_records', function (Blueprint $table) {
            $table->id();
            $table->string('communication_number')->unique()->comment('Globally unique sequential ID e.g. COM-2026-000001');
            $table->string('related_document_type')->index()->comment('e.g. Quotation, SalesOrder, Invoice, PurchaseOrder, DeliveryChallan');
            $table->unsignedBigInteger('related_document_id')->index();
            $table->string('enterprise_order_id')->nullable()->index();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('recipient_email')->nullable();
            $table->string('recipient_mobile')->nullable();
            $table->string('preferred_channel')->default('email')->comment('email, whatsapp, sms, portal');
            $table->string('document_version')->default('1.0');
            $table->string('attachment_reference')->nullable();
            $table->string('subject');
            $table->text('message_preview')->nullable();
            $table->string('status')->default('draft')->index()->comment('draft, prepared, queued, ready, sending, sent, delivered, viewed, completed, failed, retry');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('created_department')->default('Sales');
            $table->text('failure_reason')->nullable();
            $table->unsignedInteger('retry_counter')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['related_document_type', 'related_document_id'], 'comm_doc_type_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_records');
    }
};

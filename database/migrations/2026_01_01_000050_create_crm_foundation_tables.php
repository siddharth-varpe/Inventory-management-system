<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for CRM Foundation & Customer Management.
     */
    public function up(): void
    {
        // 1. Customer Groups
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Customer Categories
        Schema::create('customer_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Territories & Sales Zones
        Schema::create('territories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('region')->default('North');
            $table->string('sales_zone')->default('Zone A');
            $table->string('country')->default('India');
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->text('pin_code_mapping')->nullable();
            $table->timestamps();
        });

        // 4. Customer Master
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('customer_type')->default('dealer'); // retail, dealer, distributor, corporate, government, oem, institution
            $table->string('company_name');
            $table->string('display_name')->nullable();
            $table->string('gst_number')->nullable()->unique();
            $table->string('pan_number')->nullable()->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();

            $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups')->nullOnDelete();
            $table->foreignId('customer_category_id')->nullable()->constrained('customer_categories')->nullOnDelete();
            $table->foreignId('territory_id')->nullable()->constrained('territories')->nullOnDelete();
            $table->foreignId('salesperson_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('payment_term')->default('Net 30'); // Advance, Cash, Net 7, Net 15, Net 30, Net 45, Net 60, Custom
            $table->decimal('credit_limit', 12, 2)->default(0.00);
            $table->integer('credit_days')->default(30);
            $table->decimal('outstanding_balance', 12, 2)->default(0.00);
            $table->decimal('available_credit', 12, 2)->default(0.00);
            $table->boolean('credit_hold')->default(false);
            $table->string('risk_level')->default('low'); // low, medium, high

            $table->string('status')->default('active'); // active, inactive, blocked, blacklisted
            $table->text('internal_notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        // 5. Customer Addresses
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('type')->default('billing'); // billing, shipping, branch, warehouse_delivery
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('country')->default('India');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // 6. Customer Contacts
        Schema::create('customer_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('type')->default('primary'); // primary, accounts, purchase, technical, management
            $table->string('preferred_contact_method')->default('email'); // email, phone, whatsapp, post
            $table->timestamps();
        });

        // 7. Customer Notes
        Schema::create('customer_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('type')->default('internal'); // internal, sales, support, management
            $table->text('note');
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 8. Customer Documents
        Schema::create('customer_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('document_type')->default('gst_cert'); // gst_cert, pan_card, trade_license, agreement, contract
            $table->string('title');
            $table->string('file_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_documents');
        Schema::dropIfExists('customer_notes');
        Schema::dropIfExists('customer_contacts');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('territories');
        Schema::dropIfExists('customer_categories');
        Schema::dropIfExists('customer_groups');
    }
};

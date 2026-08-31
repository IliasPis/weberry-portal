<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_name')->nullable();
            $table->string('vat_number')->nullable()->unique();
            $table->string('tax_office')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('technical_email')->nullable();
            $table->string('status')->default('lead');
            $table->string('priority')->default('normal');
            $table->date('start_date')->nullable();
            $table->text('internal_notes')->nullable();
            $table->text('client_visible_notes')->nullable();
            $table->timestamp('last_contact_at')->nullable();
            $table->string('next_action')->nullable();
            $table->decimal('mrr_cache', 10, 2)->default(0);
            $table->decimal('arr_cache', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('contact_title')->default('employee');
            $table->boolean('is_primary')->default(false);
            $table->boolean('can_view_billing')->default(false);
            $table->boolean('can_view_invoices')->default(false);
            $table->boolean('can_view_requests')->default(true);
            $table->boolean('can_view_documents')->default(true);
            $table->boolean('can_create_requests')->default(true);
            $table->boolean('can_manage_account')->default(false);
            $table->timestamps();

            $table->unique(['client_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_contacts');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('website_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_type_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('provider')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->date('renewal_date')->nullable();
            $table->string('billing_cycle')->default('yearly');
            $table->decimal('internal_cost', 10, 2)->nullable();
            $table->decimal('client_price', 10, 2)->nullable();
            $table->boolean('auto_renewal')->default(false);
            $table->string('status')->default('active');
            $table->text('notes_internal')->nullable();
            $table->text('notes_client')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('expiration_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};

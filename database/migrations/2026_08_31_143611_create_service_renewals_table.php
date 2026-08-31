<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->date('previous_expiration')->nullable();
            $table->date('new_expiration')->nullable();
            $table->timestamp('renewed_at')->nullable();
            $table->foreignId('renewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount_charged', 10, 2)->nullable();
            $table->string('status')->default('upcoming');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_renewals');
    }
};

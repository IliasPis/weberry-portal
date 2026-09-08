<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('owner_type')->default('admin'); // admin|client
            $table->foreignId('client_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('website_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('remind_at');
            $table->time('remind_time')->nullable();
            $table->string('priority')->default('normal');
            $table->string('repeat_rule')->default('none'); // none|daily|weekly|monthly|quarterly|yearly
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('upcoming'); // upcoming|completed|dismissed
            $table->timestamps();

            $table->index('status');
            $table->index('remind_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};

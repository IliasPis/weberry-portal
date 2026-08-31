<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->string('message_type')->default('client_message');
            $table->boolean('is_read_by_client')->default(false);
            $table->boolean('is_read_by_admin')->default(false);
            $table->timestamps();

            $table->index('message_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};

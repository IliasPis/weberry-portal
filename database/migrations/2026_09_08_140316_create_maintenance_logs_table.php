<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('performed_at');
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->text('notes_internal')->nullable();
            $table->text('notes_client')->nullable();
            // Defaults true, unlike most internal-by-default flags - the
            // whole point of a maintenance log is proving work to the
            // client, so hiding it needs a deliberate choice.
            $table->boolean('client_visible')->default(true);
            $table->timestamps();

            $table->index('performed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
    }
};

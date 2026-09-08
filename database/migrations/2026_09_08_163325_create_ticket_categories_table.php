<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();
            // Null service_type_id = a global category available regardless
            // of which service (if any) the client picks on the request
            // form. Non-null = only offered once a service of that TYPE is
            // chosen - scoped to the type (Hosting, Domain, ...) rather than
            // one client's individual service row, since many clients share
            // the same service type.
            $table->foreignId('service_type_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('creates_task')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('service_type_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_categories');
    }
};

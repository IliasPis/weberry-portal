<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('ticket_category_id')->nullable()->after('category')->constrained()->nullOnDelete();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('category')->nullable()->after('service_id');
            $table->dropConstrainedForeignId('ticket_category_id');
        });
    }
};

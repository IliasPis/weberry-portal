<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('domain')->unique();
            $table->string('production_url')->nullable();
            $table->string('staging_url')->nullable();
            $table->string('cms')->default('wordpress');
            $table->string('wp_version')->nullable();
            $table->string('woo_version')->nullable();
            $table->string('theme')->nullable();
            $table->string('hosting_provider')->nullable();
            $table->string('hosting_package')->nullable();
            $table->string('server')->nullable();
            $table->string('server_ip')->nullable();
            $table->string('php_version')->nullable();
            $table->string('database_name')->nullable();
            $table->string('cdn_provider')->nullable();
            $table->boolean('cloudflare_enabled')->default(false);
            $table->string('ssl_provider')->nullable();
            $table->date('ssl_expires_at')->nullable();
            $table->string('analytics_id')->nullable();
            $table->string('search_console')->nullable();
            $table->string('smtp_provider')->nullable();
            $table->string('backup_provider')->nullable();
            $table->string('status')->default('active');
            $table->text('internal_notes')->nullable();
            $table->text('client_visible_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};

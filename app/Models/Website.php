<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'client_id', 'name', 'domain', 'production_url', 'staging_url', 'cms', 'wp_version', 'woo_version',
    'theme', 'hosting_provider', 'hosting_package', 'server', 'server_ip', 'php_version', 'database_name',
    'cdn_provider', 'cloudflare_enabled', 'ssl_provider', 'ssl_expires_at', 'analytics_id',
    'search_console', 'smtp_provider', 'backup_provider', 'status', 'internal_notes', 'client_visible_notes',
])]
class Website extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'cloudflare_enabled' => 'boolean',
            'ssl_expires_at' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}

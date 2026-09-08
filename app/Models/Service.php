<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'client_id', 'website_id', 'service_type_id', 'name', 'provider', 'start_date', 'expiration_date',
    'renewal_date', 'billing_cycle', 'internal_cost', 'client_price', 'auto_renewal', 'status',
    'notes_internal', 'notes_client',
])]
class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expiration_date' => 'date',
            'renewal_date' => 'date',
            'internal_cost' => 'decimal:2',
            'client_price' => 'decimal:2',
            'auto_renewal' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(ServiceRenewal::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'service_tags');
    }

    public function hostingDetail(): HasOne
    {
        return $this->hasOne(ServiceHostingDetail::class);
    }

    public function domainDetail(): HasOne
    {
        return $this->hasOne(ServiceDomainDetail::class);
    }

    public function maintenanceDetail(): HasOne
    {
        return $this->hasOne(ServiceMaintenanceDetail::class);
    }

    public function licenseDetail(): HasOne
    {
        return $this->hasOne(ServiceLicenseDetail::class);
    }
}

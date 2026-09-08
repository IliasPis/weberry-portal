<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['client_id', 'website_id', 'actor_user_id', 'action', 'description', 'client_visible', 'entity_type', 'entity_id'])]
class ActivityLog extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['client_visible' => 'boolean'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function scopeVisibleToClient(Builder $query): Builder
    {
        return $query->where('client_visible', true);
    }
}

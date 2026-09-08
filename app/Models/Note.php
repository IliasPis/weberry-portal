<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['client_id', 'website_id', 'service_id', 'ticket_id', 'author_user_id', 'visibility', 'body'])]
class Note extends Model
{
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /**
     * The only notes a client is ever allowed to see - internal notes
     * are excluded at the query level, not filtered client-side.
     */
    public function scopeVisibleToClient(Builder $query): Builder
    {
        return $query->whereIn('visibility', ['shared', 'system']);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'client_id', 'website_id', 'service_id', 'ticket_category_id', 'priority', 'status', 'title', 'description',
    'url', 'created_by_user_id', 'assigned_to_user_id', 'first_response_at', 'resolved_at', 'closed_at',
])]
class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'first_response_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
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

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at');
    }

    /**
     * Messages a client is allowed to see - internal notes never leave
     * the admin side, by construction rather than by UI filtering alone.
     */
    public function visibleMessages(): HasMany
    {
        return $this->messages()->where('message_type', '!=', 'internal_note');
    }
}

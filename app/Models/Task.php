<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'client_id', 'website_id', 'related_ticket_id', 'title', 'description', 'assigned_to_user_id',
    'priority', 'status', 'start_date', 'due_date', 'estimated_minutes', 'actual_minutes', 'client_visible',
])]
class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'client_visible' => 'boolean',
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

    public function relatedTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'related_ticket_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(TaskChecklistItem::class)->orderBy('position');
    }
}

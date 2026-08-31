<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['storage_disk', 'storage_key', 'original_filename', 'mime_type', 'size_bytes', 'uploaded_by_user_id'])]
class File extends Model
{
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function ticketAttachment(): HasOne
    {
        return $this->hasOne(TicketAttachment::class);
    }
}

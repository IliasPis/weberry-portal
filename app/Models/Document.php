<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['client_id', 'website_id', 'type', 'file_id', 'description', 'visible_to_client', 'uploaded_by_user_id'])]
class Document extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['visible_to_client' => 'boolean'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'color', 'scope'])]
class Tag extends Model
{
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_tags');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_tags');
    }
}

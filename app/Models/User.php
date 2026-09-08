<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'locale', 'client_id', 'is_active', 'notification_preferences'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'notification_preferences' => 'array',
        ];
    }

    /**
     * Whether this user wants email notifications for the given category
     * (ticket/note/task/invoice/document/maintenance). Defaults to true -
     * unset means "not turned off yet", not "opted out". Admin-side users
     * always get email regardless of this (the preference UI is client-only).
     */
    public function wantsEmailFor(string $category): bool
    {
        return (bool) ($this->notification_preferences[$category] ?? true);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function clientContacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
    }

    /**
     * A client-side user's membership row for their own client, carrying
     * the per-contact permission flags (billing/requests/documents/...).
     * Every client-side user gets one of these, including the "owner" role.
     */
    public function clientContact(): HasOne
    {
        return $this->hasOne(ClientContact::class);
    }
}

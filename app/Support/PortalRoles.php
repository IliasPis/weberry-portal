<?php

namespace App\Support;

/**
 * The role names that belong to each side of the application. Kept in one
 * place because portal-gating middleware, the post-login redirect, and any
 * future "which portal does this user belong to" check must agree.
 */
final class PortalRoles
{
    /** @var list<string> */
    public const ADMIN_SIDE = ['super_admin', 'admin', 'staff'];

    /** @var list<string> */
    public const CLIENT_SIDE = ['client', 'client_contact'];
}

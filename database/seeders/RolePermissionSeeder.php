<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * All permissions the admin side of the application understands.
     *
     * @var list<string>
     */
    private const PERMISSIONS = [
        'clients.viewAny',
        'clients.manage',
        'websites.manage',
        'services.manage',
        'services.viewFinancials',
        'tickets.viewAny',
        'tickets.assign',
        'tickets.internalNotes.view',
        'tasks.manage',
        'notes.viewInternal',
        'reminders.manage',
        'invoices.manage',
        'invoices.viewFinancials',
        'documents.manage',
        'maintenance.manage',
        'credentials.view',
        'credentials.reveal',
        'credentials.manage',
        'audit.view',
        'reports.view',
        'settings.manage',
        'timeentries.manage',
        'users.manage',
    ];

    /**
     * Permissions withheld from the "admin" role, on top of everything
     * "staff" is also missing. Only super_admin manages other accounts.
     *
     * @var list<string>
     */
    private const ADMIN_ONLY_RESTRICTIONS = ['users.manage'];

    /**
     * Permissions withheld from "staff", beyond the admin-only ones -
     * the day-to-day team can run the business but not touch secrets
     * or system configuration.
     *
     * @var list<string>
     */
    private const STAFF_RESTRICTIONS = ['credentials.reveal', 'settings.manage'];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdmin = Role::findOrCreate('super_admin', 'web');
        $superAdmin->syncPermissions(self::PERMISSIONS);

        $admin = Role::findOrCreate('admin', 'web');
        $admin->syncPermissions(array_diff(self::PERMISSIONS, self::ADMIN_ONLY_RESTRICTIONS));

        $staff = Role::findOrCreate('staff', 'web');
        $staff->syncPermissions(array_diff(
            self::PERMISSIONS,
            self::ADMIN_ONLY_RESTRICTIONS,
            self::STAFF_RESTRICTIONS,
        ));

        // Client-facing roles carry no admin-side permissions at all - their
        // access is governed entirely by row-level Policy checks against
        // their own client_id, never by these gates.
        Role::findOrCreate('client', 'web');
        Role::findOrCreate('client_contact', 'web');
    }
}
